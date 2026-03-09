<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;

return [
    'default' => env('LOG_CHANNEL', 'stack'),

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    'channels' => [

        /*
        |----------------------------------------------------------------------
        | Production Stack — daily rotation + stderr for Cloudways
        |----------------------------------------------------------------------
        */
        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', env('LOG_STACK', 'daily')),
            'ignore_exceptions' => false,
        ],

        /*
        |----------------------------------------------------------------------
        | Daily rotating log — 14-day retention
        |----------------------------------------------------------------------
        */
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'info'),
            'days' => (int) env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        /*
        |----------------------------------------------------------------------
        | Single file (debug/development)
        |----------------------------------------------------------------------
        */
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        /*
        |----------------------------------------------------------------------
        | Stderr — useful for Docker / Cloudways process monitoring
        |----------------------------------------------------------------------
        */
        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'warning'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        /*
        |----------------------------------------------------------------------
        | Query profiler — dedicated log channel
        |----------------------------------------------------------------------
        */
        'query' => [
            'driver' => 'daily',
            'path' => storage_path('logs/queries.log'),
            'level' => 'debug',
            'days' => 7,
        ],

        /*
        |----------------------------------------------------------------------
        | Admin audit trail — separate from general logs
        |----------------------------------------------------------------------
        */
        'audit' => [
            'driver' => 'daily',
            'path' => storage_path('logs/audit.log'),
            'level' => 'info',
            'days' => 90, // Keep admin activity longer for compliance
        ],

        /*
        |----------------------------------------------------------------------
        | Security events — login attempts, auth failures
        |----------------------------------------------------------------------
        */
        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'info',
            'days' => 30,
        ],

        /*
        |----------------------------------------------------------------------
        | Performance / slow request log
        |----------------------------------------------------------------------
        */
        'performance' => [
            'driver' => 'daily',
            'path' => storage_path('logs/performance.log'),
            'level' => 'info',
            'days' => 14,
        ],

        /*
        |----------------------------------------------------------------------
        | Null handler — discard
        |----------------------------------------------------------------------
        */
        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],
    ],
];
