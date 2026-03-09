<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Final delta sync — imports any products.json changes that occurred
 * between the last seed and the cutover freeze.
 *
 * This is designed to be idempotent: running it multiple times
 * produces the same result (no duplicate inserts).
 */
class FinalSyncCommand extends Command
{
    protected $signature = 'cutover:sync
        {--json= : Path to products.json (defaults to ../src/_data/products.json)}
        {--dry-run : Show what would change without modifying the database}';

    protected $description = 'Run final delta sync from legacy products.json into MariaDB';

    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;

    public function handle(): int
    {
        $jsonPath = $this->option('json')
            ?: base_path('../src/_data/products.json');

        if (! File::exists($jsonPath)) {
            $this->error("products.json not found at: {$jsonPath}");
            return self::FAILURE;
        }

        $data = json_decode(File::get($jsonPath), true);
        $catalog = $data['catalog'] ?? $data;

        if (! is_array($catalog) || empty($catalog)) {
            $this->warn('No products found in JSON.');
            return self::SUCCESS;
        }

        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN — no database changes will be made.');
            $this->newLine();
        }

        $this->info("Found " . count($catalog) . " products in JSON.");
        $this->newLine();

        $bar = $this->output->createProgressBar(count($catalog));
        $bar->start();

        if (! $isDryRun) {
            DB::transaction(function () use ($catalog, $bar) {
                foreach ($catalog as $item) {
                    $this->syncProduct($item);
                    $bar->advance();
                }
            });
        } else {
            foreach ($catalog as $item) {
                $this->previewProduct($item);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Action', 'Count'],
            [
                ['Created', $this->created],
                ['Updated', $this->updated],
                ['Skipped (no changes)', $this->skipped],
            ]
        );

        $this->newLine();
        $this->info('✓ Delta sync complete.');

        return self::SUCCESS;
    }

    private function syncProduct(array $item): void
    {
        $slug = $item['slug'] ?? Str::slug($item['title'] ?? '');

        if (empty($slug)) {
            $this->skipped++;
            return;
        }

        $existing = Product::where('slug', $slug)->first();
        $attributes = $this->mapAttributes($item);

        if ($existing) {
            // Check for actual changes
            $changed = false;
            foreach ($attributes as $key => $value) {
                if ((string) ($existing->getAttribute($key) ?? '') !== (string) ($value ?? '')) {
                    $changed = true;
                    break;
                }
            }

            if ($changed) {
                $existing->update($attributes);
                $this->updated++;
            } else {
                $this->skipped++;
            }

            $product = $existing;
        } else {
            $product = Product::create(array_merge(['slug' => $slug], $attributes));
            $this->created++;
        }

        // Sync collections
        $this->syncCollections($product, $item);

        // Sync tags
        $this->syncTags($product, $item);

        // Sync variants
        $this->syncVariants($product, $item);

        // Sync images
        $this->syncImages($product, $item);
    }

    private function previewProduct(array $item): void
    {
        $slug = $item['slug'] ?? Str::slug($item['title'] ?? '');
        $existing = Product::where('slug', $slug)->first();

        if ($existing) {
            $attributes = $this->mapAttributes($item);
            $changed = false;
            foreach ($attributes as $key => $value) {
                if ((string) ($existing->getAttribute($key) ?? '') !== (string) ($value ?? '')) {
                    $changed = true;
                    break;
                }
            }

            if ($changed) {
                $this->updated++;
            } else {
                $this->skipped++;
            }
        } else {
            $this->created++;
        }
    }

    private function mapAttributes(array $item): array
    {
        return [
            'legacy_id' => $item['id'] ?? null,
            'title' => $item['title'] ?? 'Untitled',
            'subtitle' => $item['subtitle'] ?? null,
            'details' => $item['details'] ?? null,
            'price' => (float) ($item['price'] ?? 0),
            'compare_at' => ! empty($item['compareAt']) ? (float) $item['compareAt'] : null,
            'badge' => $item['badge'] ?? null,
            'rating' => ! empty($item['rating']) ? (float) $item['rating'] : null,
            'review_count' => ! empty($item['reviewCount']) ? (int) $item['reviewCount'] : null,
            'giveaway_entries' => ! empty($item['giveawayEntries']) ? (int) $item['giveawayEntries'] : null,
            'image' => $item['image'] ?? '/assets/img/placeholder.jpg',
            'status' => 'active',
        ];
    }

    private function syncCollections(Product $product, array $item): void
    {
        $collSlugs = $item['collections'] ?? [];
        if (empty($collSlugs)) {
            return;
        }

        $ids = [];
        foreach ($collSlugs as $slug) {
            $collection = Collection::firstOrCreate(
                ['slug' => $slug],
                ['title' => Str::title($slug)]
            );
            $ids[] = $collection->id;
        }

        $product->collections()->syncWithoutDetaching($ids);
    }

    private function syncTags(Product $product, array $item): void
    {
        $tagNames = $item['tags'] ?? [];
        if (empty($tagNames)) {
            return;
        }

        $ids = [];
        foreach ($tagNames as $name) {
            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            );
            $ids[] = $tag->id;
        }

        $product->tags()->syncWithoutDetaching($ids);
    }

    private function syncVariants(Product $product, array $item): void
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
                    ['product_id' => $product->id, 'size' => $size, 'color' => $color],
                    ['sku' => $sku, 'is_active' => true]
                );
            }
        }
    }

    private function syncImages(Product $product, array $item): void
    {
        $imageList = $item['images'] ?? [];

        if (empty($imageList) && ! empty($item['image'])) {
            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'sort_order' => 0],
                [
                    'path' => $item['image'],
                    'thumb_path' => $item['image'],
                    'alt_text' => $product->title,
                ]
            );
            return;
        }

        foreach ($imageList as $index => $img) {
            $src = is_array($img) ? ($img['src'] ?? '') : $img;
            $thumb = is_array($img) ? ($img['thumb'] ?? $src) : $src;

            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'sort_order' => $index],
                [
                    'path' => $src,
                    'thumb_path' => $thumb,
                    'alt_text' => $product->title . ' image ' . ($index + 1),
                ]
            );
        }
    }
}

