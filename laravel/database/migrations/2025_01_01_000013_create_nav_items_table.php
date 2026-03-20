<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nav_items', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('url');
            $table->string('type')->default('primary');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_visible', 'sort_order']);
        });

        $now = now();

        DB::table('nav_items')->insert([
            ['label' => 'HOLIDAYDEALS', 'url' => '/collections/sale/', 'type' => 'primary', 'sort_order' => 1, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'MENS', 'url' => '/collections/men/', 'type' => 'primary', 'sort_order' => 2, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'WOMENS', 'url' => '/collections/women/', 'type' => 'primary', 'sort_order' => 3, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'BAGS', 'url' => '/collections/bags/', 'type' => 'primary', 'sort_order' => 4, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'GEAR', 'url' => '/collections/gear/', 'type' => 'primary', 'sort_order' => 5, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'YOUTH', 'url' => '/collections/youth/', 'type' => 'primary', 'sort_order' => 6, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Shipping', 'url' => '/shipping/', 'type' => 'meta', 'sort_order' => 1, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Returns', 'url' => '/returns/', 'type' => 'meta', 'sort_order' => 2, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Privacy', 'url' => '/privacy/', 'type' => 'meta', 'sort_order' => 3, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Terms', 'url' => '/terms/', 'type' => 'meta', 'sort_order' => 4, 'is_visible' => true, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('nav_items');
    }
};
