<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

/**
 * Centralized cache invalidation for storefront pages.
 *
 * Called from model observers or admin controllers when
 * product data changes. Uses targeted key deletion rather
 * than full cache flush to avoid thundering herd.
 */
class CacheInvalidator
{
    /**
     * Invalidate all caches related to a specific product.
     */
    public static function forProduct(Product $product): void
    {
        // PDP cache
        Cache::forget("pdp:{$product->slug}");

        // Collection caches for all collections this product belongs to
        $product->loadMissing('collections');
        foreach ($product->collections as $collection) {
            static::forCollection($collection->slug);
        }

        // Search caches are short-lived (2 min) so we don't proactively bust them.
        // For aggressive invalidation, uncomment:
        // Cache::flush(); // Nuclear option — only if needed
    }

    /**
     * Invalidate collection page caches.
     * Since collections can have many sort/filter combos, we use a tag-based
     * approach with prefix pattern clearing (file cache driver limitation:
     * we store known keys in a meta key).
     */
    public static function forCollection(string $collectionSlug): void
    {
        // Clear all known sort/filter combos for this collection
        $combos = [
            ['featured', '', ''],
            ['price-asc', '', ''],
            ['price-desc', '', ''],
            ['title-asc', '', ''],
            ['title-desc', '', ''],
        ];

        $filters = ['', 'new', 'sale', 'best'];
        $prices = ['', '0-25', '25-75', '75+'];

        foreach ($combos as [$sort]) {
            foreach ($filters as $filter) {
                foreach ($prices as $price) {
                    $key = "collection:{$collectionSlug}:s={$sort}:f={$filter}:p={$price}";
                    Cache::forget($key);
                }
            }
        }
    }

    /**
     * Flush all storefront caches.
     * Used during cutover or major bulk operations.
     */
    public static function flushAll(): void
    {
        // Flush entire cache store
        Cache::flush();
    }
}

