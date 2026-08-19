<?php

declare(strict_types=1);

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    | On Vercel serverless, LOG_CHANNEL is forced to 'null' in api/index.php
    | before bootstrap. Locally it defaults to 'stderr'.
    */

    'default' => env('LOG_CHANNEL', 'stderr'),

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    */

    'channels' => [

        'stack' => [
            'driver' => 'stack',
            'channels' => ['stderr'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'handler_with' => ['stream' => 'php://stderr'],
            'formatter' => LineFormatter::class,
            'formatter_with' => ['format' => null, 'dateFormat' => null, 'allowInlineLineBreaks' => true],
            'replace_placeholders' => true,
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],

        // ── Enterprise Domain Dedicated Logging Channels ─────────────────────

        'pos' => [
            'driver' => 'daily',
            'path' => storage_path('logs/pos.log'),
            'level' => 'info',
            'days' => 30,
            'replace_placeholders' => true,
        ],

        'inventory' => [
            'driver' => 'daily',
            'path' => storage_path('logs/inventory.log'),
            'level' => 'info',
            'days' => 30,
            'replace_placeholders' => true,
        ],

        'purchasing' => [
            'driver' => 'daily',
            'path' => storage_path('logs/purchasing.log'),
            'level' => 'info',
            'days' => 30,
            'replace_placeholders' => true,
        ],

        'admin' => [
            'driver' => 'daily',
            'path' => storage_path('logs/admin.log'),
            'level' => 'info',
            'days' => 60,
            'replace_placeholders' => true,
        ],

        'security' => [
            'driver' => 'daily',
            'path' => storage_path('logs/security.log'),
            'level' => 'info',
            'days' => 90,
            'replace_placeholders' => true,
        ],

    ],

];
