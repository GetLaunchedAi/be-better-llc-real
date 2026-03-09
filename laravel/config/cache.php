<?php

return [
    'default' => env('CACHE_STORE', 'file'),

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('framework/cache/data'),
            'lock_path' => storage_path('framework/cache/data'),
        ],
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
        'database' => [
            'driver' => 'database',
            'table' => 'cache',
            'connection' => null,
            'lock_connection' => null,
        ],
    ],

    'prefix' => env('CACHE_PREFIX', 'bebetter_cache'),

    /*
    |--------------------------------------------------------------------------
    | Storefront Cache TTLs (seconds)
    |--------------------------------------------------------------------------
    | Tunable TTLs for page-level and query-level caching.
    | Adjust via .env for production tuning.
    */
    'ttl' => [
        'product_show'   => (int) env('CACHE_TTL_PRODUCT', 300),     // 5 min
        'collection_show' => (int) env('CACHE_TTL_COLLECTION', 300), // 5 min
        'search_results' => (int) env('CACHE_TTL_SEARCH', 120),     // 2 min
        'home_page'      => (int) env('CACHE_TTL_HOME', 600),       // 10 min
    ],
];
