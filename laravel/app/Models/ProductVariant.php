<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'color',
        'sku',
        'price_override',
        'is_active',
    ];

    protected $casts = [
        'price_override' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'variant_id')->orderBy('sort_order');
    }

    /**
     * Effective price: variant override or parent product price.
     */
    public function getEffectivePriceAttribute(): string
    {
        return $this->price_override ?? $this->product->price;
    }
}

