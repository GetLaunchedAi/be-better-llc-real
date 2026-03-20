<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class NavItem extends Model
{
    protected $fillable = [
        'label',
        'url',
        'type',
        'sort_order',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function scopePrimaryNav(Builder $query): Builder
    {
        return $query->where('type', 'primary');
    }

    public function scopeMetaNav(Builder $query): Builder
    {
        return $query->where('type', 'meta');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order');
    }

    public static function allGrouped(): array
    {
        $items = static::ordered()->get();

        return [
            'primary' => $items->where('type', 'primary')->values(),
            'meta' => $items->where('type', 'meta')->values(),
        ];
    }

    public static function visibleGrouped(): array
    {
        $items = static::visible()->ordered()->get();

        return [
            'primary' => $items->where('type', 'primary')->values(),
            'meta' => $items->where('type', 'meta')->values(),
        ];
    }

    public static function toPublicPayload(): array
    {
        $groups = static::visibleGrouped();

        return [
            'primary' => $groups['primary']->map(fn ($item) => [
                'label' => $item->label,
                'url' => $item->url,
            ])->all(),
            'meta' => $groups['meta']->map(fn ($item) => [
                'label' => $item->label,
                'url' => $item->url,
            ])->all(),
        ];
    }
}
