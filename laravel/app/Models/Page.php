<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'url',
        'type',
        'in_nav',
        'nav_label',
        'nav_section',
        'nav_sort_order',
        'hero_image',
        'hero_alt',
        'content_image',
        'content_image_alt',
        'description',
    ];

    protected $casts = [
        'in_nav' => 'boolean',
        'nav_sort_order' => 'integer',
    ];

    public function scopeInNav(Builder $query): Builder
    {
        return $query->where('in_nav', true);
    }

    public function scopePrimaryNav(Builder $query): Builder
    {
        return $query->where('nav_section', 'primary');
    }

    public function scopeMetaNav(Builder $query): Builder
    {
        return $query->where('nav_section', 'meta');
    }

    public function scopeNavOrdered(Builder $query): Builder
    {
        return $query->orderBy('nav_sort_order');
    }

    public function scopeCollections(Builder $query): Builder
    {
        return $query->where('type', 'collection');
    }

    public function scopeStaticPages(Builder $query): Builder
    {
        return $query->where('type', 'static');
    }

    public static function navGrouped(): array
    {
        $items = static::inNav()->navOrdered()->get();

        return [
            'primary' => $items->where('nav_section', 'primary')->values(),
            'meta' => $items->where('nav_section', 'meta')->values(),
        ];
    }

    public static function toNavPayload(): array
    {
        $groups = static::navGrouped();

        return [
            'primary' => $groups['primary']->map(fn ($p) => [
                'label' => $p->nav_label ?: $p->title,
                'url' => $p->url,
            ])->all(),
            'meta' => $groups['meta']->map(fn ($p) => [
                'label' => $p->nav_label ?: $p->title,
                'url' => $p->url,
            ])->all(),
        ];
    }
}
