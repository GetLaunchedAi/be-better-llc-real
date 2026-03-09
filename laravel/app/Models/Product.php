<?php

namespace App\Models;

use App\Services\CacheInvalidator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected static function booted(): void
    {
        // Bust storefront caches whenever a product is saved or deleted
        static::saved(fn (Product $product) => CacheInvalidator::forProduct($product));
        static::deleted(fn (Product $product) => CacheInvalidator::forProduct($product));
    }

    protected $fillable = [
        'legacy_id',
        'title',
        'subtitle',
        'slug',
        'details',
        'price',
        'compare_at',
        'badge',
        'rating',
        'review_count',
        'giveaway_entries',
        'image',
        'status',
        'meta_title',
        'meta_description',
        'canonical_url',
        'lock_version',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at' => 'decimal:2',
        'rating' => 'decimal:2',
        'review_count' => 'integer',
        'giveaway_entries' => 'integer',
        'lock_version' => 'integer',
    ];

    /* ---- Relationships ---- */

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'product_collection');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'product_tag');
    }

    /* ---- Helpers ---- */

    public function getUrlAttribute(): string
    {
        return '/products/' . $this->slug . '/';
    }

    public function getPrimaryCollectionAttribute(): ?Collection
    {
        return $this->collections->first(fn ($c) => $c->slug !== 'sale')
            ?? $this->collections->first();
    }

    public function getDiscountPercentAttribute(): ?string
    {
        if (! $this->compare_at || $this->compare_at <= $this->price) {
            return null;
        }

        $pct = round((1 - $this->price / $this->compare_at) * 100);

        return "-{$pct}%";
    }

    public function getDistinctSizesAttribute(): array
    {
        return $this->variants
            ->pluck('size')
            ->unique()
            ->values()
            ->toArray();
    }

    public function getDistinctColorsAttribute(): array
    {
        return $this->variants
            ->pluck('color')
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Optimistic lock update: only update if lock_version matches.
     * Bumps the version on success, throws on conflict.
     *
     * @throws \App\Exceptions\StaleModelException
     */
    public function optimisticUpdate(array $attributes, int $expectedVersion): bool
    {
        $affected = static::where('id', $this->id)
            ->where('lock_version', $expectedVersion)
            ->update(array_merge($attributes, [
                'lock_version' => $expectedVersion + 1,
            ]));

        if ($affected === 0) {
            throw new \App\Exceptions\StaleModelException(
                'This product was modified by another user. Please reload and try again.'
            );
        }

        // Refresh in-memory state
        $this->fill($attributes);
        $this->lock_version = $expectedVersion + 1;

        return true;
    }

    /**
     * Scope: only active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: products matching a search query.
     */
    public function scopeSearch($query, string $term)
    {
        $like = '%' . $term . '%';

        return $query->where(function ($q) use ($like) {
            $q->where('title', 'like', $like)
              ->orWhere('subtitle', 'like', $like)
              ->orWhere('details', 'like', $like)
              ->orWhereHas('collections', fn ($c) => $c->where('title', 'like', $like))
              ->orWhereHas('tags', fn ($t) => $t->where('name', 'like', $like));
        });
    }
}

