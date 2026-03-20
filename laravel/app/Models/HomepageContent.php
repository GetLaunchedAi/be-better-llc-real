<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomepageContent extends Model
{
    protected $fillable = [
        'singleton_key',
        'hero_image',
        'hero_alt',
        'new_arrivals_banner_image',
        'new_arrivals_banner_alt',
        'grip_main_image',
        'grip_main_alt',
        'grip_tile_1_image',
        'grip_tile_1_alt',
        'grip_tile_2_image',
        'grip_tile_2_alt',
        'grip_tile_3_image',
        'grip_tile_3_alt',
        'holiday_tile_1_image',
        'holiday_tile_1_alt',
        'holiday_tile_2_image',
        'holiday_tile_2_alt',
        'featured_tile_1_image',
        'featured_tile_1_alt',
        'featured_tile_2_image',
        'featured_tile_2_alt',
        'featured_tile_3_image',
        'featured_tile_3_alt',
    ];

    public static function singleton(): self
    {
        return static::firstOrCreate(
            ['singleton_key' => 'default'],
            static::defaultValues()
        );
    }

    public static function defaultValues(): array
    {
        return [
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
        ];
    }

    public function toPublicPayload(): array
    {
        $defaults = static::defaultValues();

        return [
            'hero' => [
                'image' => $this->hero_image ?: $defaults['hero_image'],
                'alt' => $this->hero_alt ?: '',
            ],
            'newArrivals' => [
                'bannerImage' => $this->new_arrivals_banner_image ?: $defaults['new_arrivals_banner_image'],
                'bannerAlt' => $this->new_arrivals_banner_alt ?: '',
            ],
            'grip' => [
                'mainImage' => $this->grip_main_image ?: $defaults['grip_main_image'],
                'mainAlt' => $this->grip_main_alt ?: '',
                'tiles' => [
                    [
                        'image' => $this->grip_tile_1_image ?: $defaults['grip_tile_1_image'],
                        'alt' => $this->grip_tile_1_alt ?: '',
                    ],
                    [
                        'image' => $this->grip_tile_2_image ?: $defaults['grip_tile_2_image'],
                        'alt' => $this->grip_tile_2_alt ?: '',
                    ],
                    [
                        'image' => $this->grip_tile_3_image ?: $defaults['grip_tile_3_image'],
                        'alt' => $this->grip_tile_3_alt ?: '',
                    ],
                ],
            ],
            'holidayDeals' => [
                'tiles' => [
                    [
                        'image' => $this->holiday_tile_1_image ?: $defaults['holiday_tile_1_image'],
                        'alt' => $this->holiday_tile_1_alt ?: '',
                    ],
                    [
                        'image' => $this->holiday_tile_2_image ?: $defaults['holiday_tile_2_image'],
                        'alt' => $this->holiday_tile_2_alt ?: '',
                    ],
                ],
            ],
            'featuredPromos' => [
                'tiles' => [
                    [
                        'image' => $this->featured_tile_1_image ?: $defaults['featured_tile_1_image'],
                        'alt' => $this->featured_tile_1_alt ?: '',
                    ],
                    [
                        'image' => $this->featured_tile_2_image ?: $defaults['featured_tile_2_image'],
                        'alt' => $this->featured_tile_2_alt ?: '',
                    ],
                    [
                        'image' => $this->featured_tile_3_image ?: $defaults['featured_tile_3_image'],
                        'alt' => $this->featured_tile_3_alt ?: '',
                    ],
                ],
            ],
        ];
    }
}
