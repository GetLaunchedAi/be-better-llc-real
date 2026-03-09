<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Collection extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'image',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_collection');
    }

    public function getUrlAttribute(): string
    {
        return '/collections/' . $this->slug . '/';
    }
}

