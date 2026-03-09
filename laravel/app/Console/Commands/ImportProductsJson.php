<?php

namespace App\Console\Commands;

use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportProductsJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-products-json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import products from src/_data/products.json into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jsonPath = base_path('../src/_data/products.json');

        if (!File::exists($jsonPath)) {
            $this->error("File not found: {$jsonPath}");
            return 1;
        }

        $data = json_decode(File::get($jsonPath), true);
        if (!$data || !isset($data['catalog'])) {
            $this->error("Invalid JSON or missing 'catalog' key.");
            return 1;
        }

        $this->info('Importing products...');

        $importedSlugs = [];

        DB::transaction(function () use ($data, &$importedSlugs) {
            foreach ($data['catalog'] as $item) {
                $importedSlugs[] = $this->importProduct($item);
            }

            // Delete products not in the import list
            Product::whereNotIn('slug', $importedSlugs)->delete();
        });

        $this->info('Import complete! Removed obsolete products.');
        return 0;
    }

    private function importProduct(array $item)
    {
        $slug = $item['slug'] ?? Str::slug($item['title']);
        
        $product = Product::updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $item['title'],
                'subtitle' => $item['subtitle'] ?? null,
                'price' => (float) ($item['price'] ?? 0),
                'compare_at' => isset($item['compareAt']) && $item['compareAt'] ? (float) $item['compareAt'] : null,
                'badge' => $item['badge'] ?? null,
                'rating' => isset($item['rating']) ? (float) $item['rating'] : null,
                'review_count' => isset($item['reviewCount']) ? (int) $item['reviewCount'] : 0,
                'giveaway_entries' => isset($item['giveawayEntries']) ? (int) $item['giveawayEntries'] : 0,
                'image' => $item['image'] ?? null,
                'details' => $item['details'] ?? null,
                'status' => 'active',
            ]
        );

        // Sync Collections
        if (isset($item['collections'])) {
            $collectionIds = [];
            foreach ($item['collections'] as $colName) {
                $colSlug = Str::slug($colName);
                $collection = Collection::firstOrCreate(
                    ['slug' => $colSlug],
                    ['title' => Str::title(str_replace('-', ' ', $colName))]
                );
                $collectionIds[] = $collection->id;
            }
            $product->collections()->sync($collectionIds);
        }

        // Sync Tags
        if (isset($item['tags'])) {
            $tagIds = [];
            foreach ($item['tags'] as $tagName) {
                $tagSlug = Str::slug($tagName);
                $tag = Tag::firstOrCreate(
                    ['slug' => $tagSlug],
                    ['name' => $tagName]
                );
                $tagIds[] = $tag->id;
            }
            $product->tags()->sync($tagIds);
        }

        // Generate Variants
        // Strategy: Delete existing and recreate based on sizes/colors combinations
        // This is destructive but ensures the DB matches the JSON source of truth.
        $product->variants()->delete();

        $sizes = $item['sizes'] ?? ['OS'];
        $colors = $item['colors'] ?? ['Default'];

        foreach ($sizes as $size) {
            foreach ($colors as $color) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'size' => $size,
                    'color' => $color,
                    'sku' => strtoupper("{$slug}-{$size}-{$color}"),
                    'is_active' => true,
                ]);
            }
        }

        $this->line("Imported: {$product->title}");
        
        return $slug;
    }
}

