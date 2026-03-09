<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    /**
     * PDP — Product Detail Page.
     * Serves active products with full SEO support.
     * Draft/archived products return 404 to public visitors.
     *
     * Results are cached per-slug for fast repeat loads.
     * Cache is invalidated when products are updated via admin.
     */
    public function show(string $slug)
    {
        $ttl = config('cache.ttl.product_show', 300);
        $cacheKey = "pdp:{$slug}";

        $data = Cache::remember($cacheKey, $ttl, function () use ($slug) {
            $product = Product::active()
                ->where('slug', $slug)
                ->with(['variants' => fn ($q) => $q->where('is_active', true), 'images', 'collections', 'tags'])
                ->firstOrFail();

            // Related products: same primary collection, excluding current
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

            // If we didn't get enough, backfill from any collection
            if ($related->count() < 4) {
                $excludeIds = $related->pluck('id')->push($product->id)->toArray();
                $backfill = Product::active()
                    ->whereNotIn('id', $excludeIds)
                    ->with(['images', 'collections'])
                    ->limit(4 - $related->count())
                    ->get();
                $related = $related->merge($backfill);
            }

            // SEO data
            $canonicalUrl = $product->canonical_url ?: url('/products/' . $product->slug);
            $metaTitle = $product->meta_title ?: $product->title;
            $metaDescription = $product->meta_description ?: ($product->subtitle ?? $product->title . ' — Be Better BSBL');

            return [
                'product'         => $product,
                'related'         => $related,
                'canonicalUrl'    => $canonicalUrl,
                'metaTitle'       => $metaTitle,
                'metaDescription' => $metaDescription,
                'noindex'         => false,
            ];
        });

        return view('products.show', $data);
    }

    /**
     * JSON Feed — /products.json
     * Returns all active products in the format expected by the frontend.
     */
    public function json()
    {
        $products = \App\Models\Product::active()
            ->with(['collections', 'tags'])
            ->get()
            ->map(function ($p) {
                return [
                    'id'          => (string) $p->id,
                    'slug'        => $p->slug,
                    'url'         => '/products/' . $p->slug . '/',
                    'title'       => $p->title,
                    'subtitle'    => $p->subtitle,
                    'price'       => (string) $p->price,
                    'compareAt'   => $p->compare_at ? (string) $p->compare_at : null,
                    'badge'       => $p->badge,
                    'badges'      => $p->badge ? [$p->badge] : [],
                    'collections' => $p->collections->pluck('title')->toArray(),
                    'tags'        => $p->tags->pluck('name')->toArray(),
                    'image'       => $p->image ?: '/assets/img/placeholder.jpg',
                ];
            });

        return response()->json($products);
    }
}
