<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_id')->nullable()->unique()->comment('Original JSON id, e.g. pf-polo');
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('slug')->unique();
            $table->text('details')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('compare_at', 10, 2)->nullable();
            $table->string('badge')->nullable()->comment('e.g. New, Sale, Top Rated, Best Seller');
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('review_count')->nullable();
            $table->unsignedInteger('giveaway_entries')->nullable();
            $table->string('image')->nullable()->comment('Primary product image path');
            $table->enum('status', ['active', 'draft', 'archived'])->default('active');

            // SEO fields
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('canonical_url')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

