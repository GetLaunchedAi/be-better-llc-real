<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Collection;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Rules\NotReservedSlug;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    /**
     * Product list with search & filters.
     */
    public function index(Request $request)
    {
        $query = Product::with(['collections', 'tags', 'variants', 'images']);

        // Search
        if ($search = $request->input('q')) {
            $query->search($search);
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Collection filter
        if ($collection = $request->input('collection')) {
            $query->whereHas('collections', fn ($q) => $q->where('slug', $collection));
        }

        // Sorting
        $sort = $request->input('sort', 'updated_at');
        $dir = $request->input('dir', 'desc');
        $allowedSorts = ['title', 'price', 'status', 'created_at', 'updated_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $dir === 'asc' ? 'asc' : 'desc');
        }

        $products = $query->paginate(25)->withQueryString();
        $collections = Collection::orderBy('title')->get();

        return view('admin.products.index', compact('products', 'collections'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $collections = Collection::orderBy('title')->get();
        $tags = Tag::orderBy('name')->get();

        return view('admin.products.create', compact('collections', 'tags'));
    }

    /**
     * Store a new product.
     */
    public function store(Request $request)
    {
        $validated = $this->validateProduct($request);

        // Auto-generate slug from title if blank
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Ensure slug uniqueness
        $validated['slug'] = $this->uniqueSlug($validated['slug']);

        $product = Product::create($validated);

        // Sync collections & tags
        $this->syncRelations($product, $request);

        ActivityLog::log('created', $product, $validated, $product->title);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', "Product \"{$product->title}\" created. Add variants and images below.");
    }

    /**
     * Show edit form.
     */
    public function edit(Product $product)
    {
        $product->load(['variants', 'images', 'collections', 'tags']);
        $collections = Collection::orderBy('title')->get();
        $tags = Tag::orderBy('name')->get();
        $recentLogs = ActivityLog::where('subject_type', Product::class)
            ->where('subject_id', $product->id)
            ->with('user')
            ->latest()
            ->limit(20)
            ->get();

        return view('admin.products.edit', compact('product', 'collections', 'tags', 'recentLogs'));
    }

    /**
     * Update an existing product.
     * Uses optimistic locking to prevent overwriting concurrent edits.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $this->validateProduct($request, $product->id);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        // Track changes for audit log
        $changes = $this->detectChanges($product, $validated);

        // Optimistic lock: check version from hidden form field
        $expectedVersion = (int) $request->input('lock_version', $product->lock_version);

        try {
            $product->optimisticUpdate($validated, $expectedVersion);
        } catch (\App\Exceptions\StaleModelException $e) {
            return back()
                ->withInput()
                ->with('error', 'This product was modified by another user while you were editing. Your changes were NOT saved. Please review the latest version and try again.');
        }

        $this->syncRelations($product, $request);

        if (! empty($changes)) {
            ActivityLog::log('updated', $product, $changes, $product->title);
        }

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Product updated.');
    }

    /**
     * Delete a product.
     */
    public function destroy(Product $product)
    {
        $title = $product->title;
        ActivityLog::log('deleted', $product, null, $title);
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Product \"{$title}\" deleted.");
    }

    /**
     * Storefront PDP preview (rendered within admin chrome).
     */
    public function preview(Product $product)
    {
        $product->load(['variants', 'images', 'collections', 'tags']);

        // Build the same data the storefront PDP uses
        $primaryCollection = $product->primary_collection;
        $related = collect();

        if ($primaryCollection) {
            $related = Product::active()
                ->where('id', '!=', $product->id)
                ->whereHas('collections', fn ($q) => $q->where('collections.id', $primaryCollection->id))
                ->with(['images', 'collections'])
                ->limit(4)
                ->get();
        }

        if ($related->count() < 4) {
            $excludeIds = $related->pluck('id')->push($product->id)->toArray();
            $backfill = Product::active()
                ->whereNotIn('id', $excludeIds)
                ->with(['images', 'collections'])
                ->limit(4 - $related->count())
                ->get();
            $related = $related->merge($backfill);
        }

        return view('admin.products.preview', [
            'product' => $product,
            'related' => $related,
        ]);
    }

    /**
     * Duplicate a product.
     */
    public function duplicate(Product $product)
    {
        $product->load(['variants', 'images', 'collections', 'tags']);

        $newProduct = $product->replicate(['legacy_id']);
        $newProduct->title = $product->title . ' (Copy)';
        $newProduct->slug = $this->uniqueSlug(Str::slug($newProduct->title));
        $newProduct->status = 'draft';
        $newProduct->save();

        // Copy collections & tags
        $newProduct->collections()->sync($product->collections->pluck('id'));
        $newProduct->tags()->sync($product->tags->pluck('id'));

        // Copy variants
        foreach ($product->variants as $variant) {
            $newVariant = $variant->replicate();
            $newVariant->product_id = $newProduct->id;
            $newVariant->sku = $this->uniqueSku($variant->sku . '-COPY');
            $newVariant->save();
        }

        // Copy images
        foreach ($product->images as $image) {
            $newImage = $image->replicate();
            $newImage->product_id = $newProduct->id;
            $newImage->variant_id = null; // Reset variant assignment for copies
            $newImage->save();
        }

        ActivityLog::log('duplicated', $newProduct, [
            'source_id' => $product->id,
            'source_title' => $product->title,
        ], $newProduct->title);

        return redirect()
            ->route('admin.products.edit', $newProduct)
            ->with('success', "Product duplicated as \"{$newProduct->title}\".");
    }

    /* ---- Private helpers ---- */

    private function validateProduct(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9\-]*$/',
                Rule::unique('products', 'slug')->ignore($ignoreId),
                new NotReservedSlug(),
            ],
            'details' => 'nullable|string|max:10000',
            'price' => 'required|numeric|min:0|max:99999.99',
            'compare_at' => 'nullable|numeric|min:0|max:99999.99',
            'badge' => 'nullable|string|max:50',
            'rating' => 'nullable|numeric|min:0|max:5',
            'review_count' => 'nullable|integer|min:0',
            'giveaway_entries' => 'nullable|integer|min:0',
            'image' => 'nullable|string|max:500',
            'status' => 'required|in:active,draft,archived',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'canonical_url' => 'nullable|string|max:500',
        ]);
    }

    private function syncRelations(Product $product, Request $request): void
    {
        // Collections
        if ($request->has('collections')) {
            $product->collections()->sync($request->input('collections', []));
        }

        // Tags — handle both existing IDs and new tag names
        $tagIds = $request->input('tags', []);
        $newTagNames = array_filter($request->input('new_tags', []), fn ($v) => trim($v) !== '');

        foreach ($newTagNames as $name) {
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => trim($name)]
            );
            $tagIds[] = $tag->id;
        }

        $product->tags()->sync(array_unique($tagIds));
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $original = $slug;
        $counter = 1;

        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $original . '-' . (++$counter);
        }

        return $slug;
    }

    private function uniqueSku(string $sku): string
    {
        $original = $sku;
        $counter = 1;

        while (\App\Models\ProductVariant::where('sku', $sku)->exists()) {
            $sku = $original . '-' . (++$counter);
        }

        return $sku;
    }

    private function detectChanges(Product $product, array $newValues): array
    {
        $changes = [];

        foreach ($newValues as $field => $newVal) {
            $oldVal = $product->getAttribute($field);
            if ((string) $oldVal !== (string) $newVal) {
                $changes[$field] = ['old' => $oldVal, 'new' => $newVal];
            }
        }

        return $changes;
    }
}

