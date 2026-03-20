<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_contents', function (Blueprint $table) {
            $table->id();
            $table->string('singleton_key')->default('default')->unique();

            $table->string('hero_image')->nullable();
            $table->string('hero_alt')->nullable();

            $table->string('new_arrivals_banner_image')->nullable();
            $table->string('new_arrivals_banner_alt')->nullable();

            $table->string('grip_main_image')->nullable();
            $table->string('grip_main_alt')->nullable();
            $table->string('grip_tile_1_image')->nullable();
            $table->string('grip_tile_1_alt')->nullable();
            $table->string('grip_tile_2_image')->nullable();
            $table->string('grip_tile_2_alt')->nullable();
            $table->string('grip_tile_3_image')->nullable();
            $table->string('grip_tile_3_alt')->nullable();

            $table->string('holiday_tile_1_image')->nullable();
            $table->string('holiday_tile_1_alt')->nullable();
            $table->string('holiday_tile_2_image')->nullable();
            $table->string('holiday_tile_2_alt')->nullable();

            $table->string('featured_tile_1_image')->nullable();
            $table->string('featured_tile_1_alt')->nullable();
            $table->string('featured_tile_2_image')->nullable();
            $table->string('featured_tile_2_alt')->nullable();
            $table->string('featured_tile_3_image')->nullable();
            $table->string('featured_tile_3_alt')->nullable();

            $table->timestamps();
        });

        DB::table('homepage_contents')->insert([
            'singleton_key' => 'default',
            'hero_image' => '/assets/img/placeholder.jpg',
            'hero_alt' => '',
            'new_arrivals_banner_image' => '/assets/img/placeholder.jpg',
            'new_arrivals_banner_alt' => '',
            'grip_main_image' => '/assets/img/placeholder.jpg',
            'grip_main_alt' => '',
            'grip_tile_1_image' => '/assets/img/placeholder.jpg',
            'grip_tile_1_alt' => '',
            'grip_tile_2_image' => '/assets/img/placeholder.jpg',
            'grip_tile_2_alt' => '',
            'grip_tile_3_image' => '/assets/img/placeholder.jpg',
            'grip_tile_3_alt' => '',
            'holiday_tile_1_image' => '/assets/img/OG Better Hoodie Blk and white 2.png',
            'holiday_tile_1_alt' => '',
            'holiday_tile_2_image' => '/assets/img/OG Tan Be Better Crew.png',
            'holiday_tile_2_alt' => '',
            'featured_tile_1_image' => '/assets/img/OG BLK Be Better Crew.png',
            'featured_tile_1_alt' => '',
            'featured_tile_2_image' => '/assets/img/OG Better Hoodie (Katy).png',
            'featured_tile_2_alt' => '',
            'featured_tile_3_image' => '/assets/img/Women_s Relax crew Bone.png',
            'featured_tile_3_alt' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_contents');
    }
};
