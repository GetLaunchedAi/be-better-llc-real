<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Upload one or more images for a product.
     */
    public function upload(Request $request, Product $product)
    {
        $request->validate([
            'images' => 'required|array|min:1|max:20',
            'images.*' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120', // 5 MB each
            'variant_id' => 'nullable|integer|exists:product_variants,id',
        ]);

        $variantId = $request->input('variant_id');
        $maxSort = $product->images()->max('sort_order') ?? -1;
        $uploaded = 0;

        foreach ($request->file('images') as $file) {
            $maxSort++;

            // Store in public disk: uploads/products/{product_id}/
            $dir = "uploads/products/{$product->id}";
            $filename = time() . '-' . $maxSort . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($dir, $filename, 'public');

            ProductImage::create([
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'path' => '/storage/' . $path,
                'thumb_path' => '/storage/' . $path, // Thumbnail generation can be added later
                'alt_text' => $product->title . ' image ' . ($maxSort + 1),
                'sort_order' => $maxSort,
            ]);

            $uploaded++;
        }

        ActivityLog::log('images_uploaded', $product, [
            'count' => $uploaded,
            'variant_id' => $variantId,
        ], $product->title);

        return back()->with('success', "{$uploaded} image(s) uploaded.");
    }

    /**
     * Update image metadata (alt text, variant assignment).
     */
    public function update(Request $request, Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 404);

        $validated = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
        ]);

        $image->update($validated);

        return back()->with('success', 'Image updated.');
    }

    /**
     * Reorder images via AJAX.
     */
    public function reorder(Request $request, Product $product)
    {
        $request->validate([
            'order' => 'required|array|min:1',
            'order.*' => 'integer|exists:product_images,id',
        ]);

        foreach ($request->input('order') as $index => $imageId) {
            ProductImage::where('id', $imageId)
                ->where('product_id', $product->id)
                ->update(['sort_order' => $index]);
        }

        ActivityLog::log('images_reordered', $product, null, $product->title);

        return response()->json(['ok' => true]);
    }

    /**
     * Delete an image.
     */
    public function destroy(Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 404);

        // Delete file from storage if it's a local upload
        if (str_starts_with($image->path, '/storage/')) {
            $storagePath = str_replace('/storage/', '', $image->path);
            Storage::disk('public')->delete($storagePath);
        }

        ActivityLog::log('image_deleted', $product, [
            'path' => $image->path,
        ], $product->title);

        $image->delete();

        return back()->with('success', 'Image deleted.');
    }

    /**
     * Set image as the primary product image.
     */
    public function setPrimary(Product $product, ProductImage $image)
    {
        abort_if($image->product_id !== $product->id, 404);

        $product->update(['image' => $image->path]);

        return back()->with('success', 'Primary image updated.');
    }
}

