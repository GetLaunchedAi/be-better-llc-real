<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 5 — Performance index tuning.
 *
 * Adds composite indexes optimized for the most common storefront
 * and admin queries identified during profiling.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Products: composite for active product lookups by slug (PDP)
        Schema::table('products', function (Blueprint $table) {
            $table->index(['status', 'slug'], 'idx_products_status_slug');
            $table->index(['status', 'price'], 'idx_products_status_price');
            $table->index(['status', 'title'], 'idx_products_status_title');
            $table->index(['status', 'updated_at'], 'idx_products_status_updated');
        });

        // Product images: faster PDP gallery loading
        Schema::table('product_images', function (Blueprint $table) {
            $table->index(['product_id', 'sort_order'], 'idx_images_product_sort');
        });

        // Product variants: faster active variant loading per product
        Schema::table('product_variants', function (Blueprint $table) {
            $table->index(['product_id', 'is_active'], 'idx_variants_product_active');
        });

        // Activity logs: faster admin audit queries
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['subject_type', 'subject_id'], 'idx_logs_subject');
            $table->index('created_at', 'idx_logs_created');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_status_slug');
            $table->dropIndex('idx_products_status_price');
            $table->dropIndex('idx_products_status_title');
            $table->dropIndex('idx_products_status_updated');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('idx_images_product_sort');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropIndex('idx_variants_product_active');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('idx_logs_subject');
            $table->dropIndex('idx_logs_created');
        });
    }
};

