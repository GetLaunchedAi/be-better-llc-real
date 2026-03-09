<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');                         // created, updated, deleted, duplicated, bulk_update, image_uploaded, etc.
            $table->string('subject_type')->nullable();       // App\Models\Product, App\Models\ProductVariant, etc.
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label')->nullable();      // Human-readable label, e.g. product title
            $table->json('changes')->nullable();              // {"field": {"old": "x", "new": "y"}}
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

