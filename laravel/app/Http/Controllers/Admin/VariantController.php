<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VariantController extends Controller
{
    /**
     * Generate variant matrix from sizes × colors.
     * Creates new variants for combinations that don't yet exist.
     */
    public function generate(Request $request, Product $product)
    {
        $request->validate([
            'sizes' => 'required|array|min:1',
            'sizes.*' => 'required|string|max:30',
            'colors' => 'required|array|min:1',
            'colors.*' => 'required|string|max:50',
        ]);

        $sizes = $request->input('sizes');
        $colors = $request->input('colors');
        $created = 0;

        foreach ($sizes as $size) {
            foreach ($colors as $color) {
                $sku = Str::upper(Str::slug($product->slug . '-' . $size . '-' . $color));

                $existed = ProductVariant::where('product_id', $product->id)
                    ->where('size', $size)
                    ->where('color', $color)
                    ->exists();

                if (! $existed) {
                    // Ensure SKU uniqueness
                    $finalSku = $sku;
                    $counter = 1;
                    while (ProductVariant::where('sku', $finalSku)->exists()) {
                        $finalSku = $sku . '-' . (++$counter);
                    }

                    ProductVariant::create([
                        'product_id' => $product->id,
                        'size' => $size,
                        'color' => $color,
                        'sku' => $finalSku,
                        'is_active' => true,
                    ]);
                    $created++;
                }
            }
        }

        ActivityLog::log('variants_generated', $product, [
            'sizes' => $sizes,
            'colors' => $colors,
            'created' => $created,
        ], $product->title);

        return back()->with('success', "{$created} new variant(s) created.");
    }

    /**
     * Update a single variant (inline edit).
     */
    public function update(Request $request, Product $product, ProductVariant $variant)
    {
        abort_if($variant->product_id !== $product->id, 404);

        $validated = $request->validate([
            'sku' => [
                'required',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('product_variants', 'sku')->ignore($variant->id),
            ],
            'price_override' => 'nullable|numeric|min:0|max:99999.99',
            'is_active' => 'sometimes|boolean',
        ]);

        $changes = [];
        foreach ($validated as $field => $val) {
            $old = $variant->getAttribute($field);
            if ((string) $old !== (string) $val) {
                $changes[$field] = ['old' => $old, 'new' => $val];
            }
        }

        $variant->update($validated);

        if (! empty($changes)) {
            ActivityLog::log('variant_updated', $product, $changes, "{$product->title} — {$variant->size}/{$variant->color}");
        }

        return back()->with('success', "Variant {$variant->sku} updated.");
    }

    /**
     * Delete a single variant.
     */
    public function destroy(Product $product, ProductVariant $variant)
    {
        abort_if($variant->product_id !== $product->id, 404);

        ActivityLog::log('variant_deleted', $product, [
            'sku' => $variant->sku,
            'size' => $variant->size,
            'color' => $variant->color,
        ], "{$product->title} — {$variant->sku}");

        $variant->delete();

        return back()->with('success', "Variant {$variant->sku} deleted.");
    }

    /**
     * Bulk toggle active/inactive for multiple variants.
     */
    public function bulkToggle(Request $request, Product $product)
    {
        $request->validate([
            'variant_ids' => 'required|array|min:1',
            'variant_ids.*' => 'integer|exists:product_variants,id',
            'is_active' => 'required|boolean',
        ]);

        $ids = $request->input('variant_ids');
        $active = $request->boolean('is_active');

        $count = ProductVariant::where('product_id', $product->id)
            ->whereIn('id', $ids)
            ->update(['is_active' => $active]);

        $action = $active ? 'activated' : 'deactivated';

        ActivityLog::log("variants_bulk_{$action}", $product, [
            'count' => $count,
            'variant_ids' => $ids,
        ], $product->title);

        return back()->with('success', "{$count} variant(s) {$action}.");
    }

    /**
     * Bulk price override for selected variants.
     */
    public function bulkPrice(Request $request, Product $product)
    {
        $request->validate([
            'variant_ids' => 'required|array|min:1',
            'variant_ids.*' => 'integer|exists:product_variants,id',
            'price_override' => 'nullable|numeric|min:0|max:99999.99',
        ]);

        $ids = $request->input('variant_ids');
        $price = $request->input('price_override');

        $count = ProductVariant::where('product_id', $product->id)
            ->whereIn('id', $ids)
            ->update(['price_override' => $price ?: null]);

        $label = $price ? "$" . number_format((float) $price, 2) : 'base price';

        ActivityLog::log('variants_bulk_price', $product, [
            'count' => $count,
            'price_override' => $price,
            'variant_ids' => $ids,
        ], $product->title);

        return back()->with('success', "{$count} variant(s) price set to {$label}.");
    }
}

