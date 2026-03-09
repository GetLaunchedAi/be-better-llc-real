<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductJsonSeeder extends Seeder
{
    /**
     * Collection metadata — matches the Nunjucks collection pages.
     */
    private array $collectionMeta = [
        'men' => [
            'title' => 'Men',
            'description' => 'Performance polos, layers, and essentials built for the course and beyond.',
        ],
        'women' => [
            'title' => 'Women',
            'description' => 'Skorts, vests, hoodies, and tees designed for women who play.',
        ],
        'youth' => [
            'title' => 'Youth',
            'description' => 'Kid-friendly cuts of our best-selling styles.',
        ],
        'headwear' => [
            'title' => 'Headwear',
            'description' => 'Caps, beanies, visors, and truckers for every round.',
        ],
        'sale' => [
            'title' => 'Sale',
            'description' => 'Grab deals on past-season favourites and limited-time markdowns.',
        ],
        'bags' => [
            'title' => 'Bags',
            'description' => 'Duffels, totes, and range bags built to carry your gear.',
        ],
        'gear' => [
            'title' => 'Gear',
            'description' => 'Accessories and essentials for on and off the course.',
        ],
    ];

    public function run(): void
    {
        // Path to the legacy JSON (relative to laravel root — sibling folder)
        $jsonPath = base_path('../src/_data/products.json');

        if (! file_exists($jsonPath)) {
            $this->command->error("products.json not found at: {$jsonPath}");
            return;
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        $catalog = $data['catalog'] ?? [];

        if (empty($catalog)) {
            $this->command->warn('Catalog array is empty — nothing to import.');
            return;
        }

        DB::transaction(function () use ($catalog) {
            // 1. Seed collections
            $collectionMap = $this->seedCollections($catalog);

            // 2. Seed tags
            $tagMap = $this->seedTags($catalog);

            // 3. Seed products
            foreach ($catalog as $item) {
                $product = $this->seedProduct($item);

                // Attach collections
                $collectionIds = [];
                foreach (($item['collections'] ?? []) as $collSlug) {
                    if (isset($collectionMap[$collSlug])) {
                        $collectionIds[] = $collectionMap[$collSlug];
                    }
                }
                $product->collections()->sync($collectionIds);

                // Attach tags
                $tagIds = [];
                foreach (($item['tags'] ?? []) as $tagName) {
                    $slug = Str::slug($tagName);
                    if (isset($tagMap[$slug])) {
                        $tagIds[] = $tagMap[$slug];
                    }
                }
                $product->tags()->sync($tagIds);

                // Create variants (size × color matrix)
                $this->seedVariants($product, $item);

                // Create images
                $this->seedImages($product, $item);
            }
        });

        $this->command->info('Imported ' . count($catalog) . ' products from products.json.');
    }

    /**
     * Create collection records from the catalog + known metadata.
     *
     * @return array<string, int>  slug => id
     */
    private function seedCollections(array $catalog): array
    {
        // Gather unique collection slugs from catalog
        $slugs = [];
        foreach ($catalog as $item) {
            foreach (($item['collections'] ?? []) as $slug) {
                $slugs[$slug] = true;
            }
        }

        // Also ensure all known navigation collections exist
        foreach (array_keys($this->collectionMeta) as $slug) {
            $slugs[$slug] = true;
        }

        $map = [];
        foreach (array_keys($slugs) as $slug) {
            $meta = $this->collectionMeta[$slug] ?? [
                'title' => Str::title($slug),
                'description' => null,
            ];

            $collection = Collection::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $meta['title'],
                    'description' => $meta['description'] ?? null,
                ]
            );

            $map[$slug] = $collection->id;
        }

        return $map;
    }

    /**
     * Create tag records from the catalog.
     *
     * @return array<string, int>  slug => id
     */
    private function seedTags(array $catalog): array
    {
        $allTags = [];
        foreach ($catalog as $item) {
            foreach (($item['tags'] ?? []) as $tagName) {
                $slug = Str::slug($tagName);
                $allTags[$slug] = $tagName;
            }
        }

        $map = [];
        foreach ($allTags as $slug => $name) {
            $tag = Tag::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
            $map[$slug] = $tag->id;
        }

        return $map;
    }

    /**
     * Create a product record from a single catalog item.
     */
    private function seedProduct(array $item): Product
    {
        return Product::updateOrCreate(
            ['slug' => $item['slug']],
            [
                'legacy_id'       => $item['id'] ?? null,
                'title'           => $item['title'],
                'subtitle'        => $item['subtitle'] ?? null,
                'details'         => $item['details'] ?? null,
                'price'           => (float) ($item['price'] ?? 0),
                'compare_at'      => ! empty($item['compareAt']) ? (float) $item['compareAt'] : null,
                'badge'           => $item['badge'] ?? null,
                'rating'          => ! empty($item['rating']) ? (float) $item['rating'] : null,
                'review_count'    => ! empty($item['reviewCount']) ? (int) $item['reviewCount'] : null,
                'giveaway_entries' => ! empty($item['giveawayEntries']) ? (int) $item['giveawayEntries'] : null,
                'image'           => $item['image'] ?? '/assets/img/placeholder.jpg',
                'status'          => 'active',
            ]
        );
    }

    /**
     * Generate variant rows for size × color matrix.
     */
    private function seedVariants(Product $product, array $item): void
    {
        $sizes = $item['sizes'] ?? [];
        $colors = $item['colors'] ?? [];

        if (empty($sizes) || empty($colors)) {
            return;
        }

        foreach ($sizes as $size) {
            foreach ($colors as $color) {
                $sku = Str::upper(Str::slug($product->slug . '-' . $size . '-' . $color));

                ProductVariant::firstOrCreate(
                    ['sku' => $sku],
                    [
                        'product_id' => $product->id,
                        'size'       => $size,
                        'color'      => $color,
                        'is_active'  => true,
                    ]
                );
            }
        }
    }

    /**
     * Seed product images from the JSON images array.
     */
    private function seedImages(Product $product, array $item): void
    {
        $imageList = $item['images'] ?? [];

        if (empty($imageList)) {
            // Fallback: create a single image from the main image field
            if (! empty($item['image'])) {
                ProductImage::firstOrCreate(
                    ['product_id' => $product->id, 'sort_order' => 0],
                    [
                        'path'       => $item['image'],
                        'thumb_path' => $item['image'],
                        'alt_text'   => $product->title,
                    ]
                );
            }
            return;
        }

        foreach ($imageList as $index => $img) {
            $src = is_array($img) ? ($img['src'] ?? '') : $img;
            $thumb = is_array($img) ? ($img['thumb'] ?? $src) : $src;

            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'sort_order' => $index],
                [
                    'path'       => $src,
                    'thumb_path' => $thumb,
                    'alt_text'   => $product->title . ' image ' . ($index + 1),
                ]
            );
        }
    }
}

