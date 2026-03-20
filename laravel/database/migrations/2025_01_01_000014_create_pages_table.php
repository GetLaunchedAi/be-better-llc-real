<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('url');
            $table->string('type')->default('static');

            $table->boolean('in_nav')->default(false);
            $table->string('nav_label')->nullable();
            $table->string('nav_section')->default('primary');
            $table->unsignedInteger('nav_sort_order')->default(0);

            $table->string('hero_image')->nullable();
            $table->string('hero_alt')->nullable();
            $table->string('content_image')->nullable();
            $table->string('content_image_alt')->nullable();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->index(['in_nav', 'nav_section', 'nav_sort_order']);
        });

        $now = now();
        $pages = [
            ['title' => 'Holiday Deals', 'slug' => 'sale', 'url' => '/collections/sale/', 'type' => 'collection', 'in_nav' => true, 'nav_label' => 'HOLIDAYDEALS', 'nav_section' => 'primary', 'nav_sort_order' => 1],
            ['title' => 'Mens', 'slug' => 'men', 'url' => '/collections/men/', 'type' => 'collection', 'in_nav' => true, 'nav_label' => 'MENS', 'nav_section' => 'primary', 'nav_sort_order' => 2],
            ['title' => 'Womens', 'slug' => 'women', 'url' => '/collections/women/', 'type' => 'collection', 'in_nav' => true, 'nav_label' => 'WOMENS', 'nav_section' => 'primary', 'nav_sort_order' => 3],
            ['title' => 'Bags', 'slug' => 'bags', 'url' => '/collections/bags/', 'type' => 'collection', 'in_nav' => true, 'nav_label' => 'BAGS', 'nav_section' => 'primary', 'nav_sort_order' => 4],
            ['title' => 'Gear', 'slug' => 'gear', 'url' => '/collections/gear/', 'type' => 'collection', 'in_nav' => true, 'nav_label' => 'GEAR', 'nav_section' => 'primary', 'nav_sort_order' => 5],
            ['title' => 'Youth', 'slug' => 'youth', 'url' => '/collections/youth/', 'type' => 'collection', 'in_nav' => true, 'nav_label' => 'YOUTH', 'nav_section' => 'primary', 'nav_sort_order' => 6],
            ['title' => 'Headwear', 'slug' => 'headwear', 'url' => '/collections/headwear/', 'type' => 'collection', 'in_nav' => false, 'nav_label' => 'HEADWEAR', 'nav_section' => 'primary', 'nav_sort_order' => 7],

            ['title' => 'About', 'slug' => 'about', 'url' => '/about/', 'type' => 'static', 'in_nav' => false, 'nav_label' => 'About', 'nav_section' => 'meta', 'nav_sort_order' => 0],
            ['title' => 'Contact', 'slug' => 'contact', 'url' => '/contact/', 'type' => 'static', 'in_nav' => false, 'nav_label' => 'Contact', 'nav_section' => 'meta', 'nav_sort_order' => 0],
            ['title' => 'Shipping', 'slug' => 'shipping', 'url' => '/shipping/', 'type' => 'static', 'in_nav' => true, 'nav_label' => 'Shipping', 'nav_section' => 'meta', 'nav_sort_order' => 1],
            ['title' => 'Returns', 'slug' => 'returns', 'url' => '/returns/', 'type' => 'static', 'in_nav' => true, 'nav_label' => 'Returns', 'nav_section' => 'meta', 'nav_sort_order' => 2],
            ['title' => 'Privacy Policy', 'slug' => 'privacy', 'url' => '/privacy/', 'type' => 'static', 'in_nav' => true, 'nav_label' => 'Privacy', 'nav_section' => 'meta', 'nav_sort_order' => 3],
            ['title' => 'Terms of Service', 'slug' => 'terms', 'url' => '/terms/', 'type' => 'static', 'in_nav' => true, 'nav_label' => 'Terms', 'nav_section' => 'meta', 'nav_sort_order' => 4],
            ['title' => 'Help Center', 'slug' => 'help', 'url' => '/help/', 'type' => 'static', 'in_nav' => false, 'nav_label' => 'Help', 'nav_section' => 'meta', 'nav_sort_order' => 0],
            ['title' => 'Careers', 'slug' => 'careers', 'url' => '/careers/', 'type' => 'static', 'in_nav' => false, 'nav_label' => 'Careers', 'nav_section' => 'meta', 'nav_sort_order' => 0],
            ['title' => 'Accessibility', 'slug' => 'accessibility', 'url' => '/accessibility/', 'type' => 'static', 'in_nav' => false, 'nav_label' => 'Accessibility', 'nav_section' => 'meta', 'nav_sort_order' => 0],
        ];

        foreach ($pages as $page) {
            DB::table('pages')->insert(array_merge($page, [
                'hero_image' => null,
                'hero_alt' => null,
                'content_image' => null,
                'content_image_alt' => null,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
