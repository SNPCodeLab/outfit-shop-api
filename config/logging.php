<?php

use Monolog\Handler\NullHandler;

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
        'trace'   => env('LOG_DEPRECATIONS_TRACE', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    */

    'channels' => [

        'stack' => [
            'driver'            => 'stack',
            'channels'          => ['stderr'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver'               => 'single',
            'path'                 => storage_path('logs/laravel.log'),
            'level'                => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver'               => 'daily',
            'path'                 => storage_path('logs/laravel.log'),
            'level'                => env('LOG_LEVEL', 'debug'),
            'days'                 => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'stderr' => [
            'driver'               => 'monolog',
            'level'                => env('LOG_LEVEL', 'debug'),
            'handler'              => Monolog\Handler\StreamHandler::class,
            'handler_with'         => ['stream' => 'php://stderr'],
            'formatter'            => Monolog\Formatter\LineFormatter::class,
            'formatter_with'       => ['format' => null, 'dateFormat' => null, 'allowInlineLineBreaks' => true],
            'replace_placeholders' => true,
        ],

        'syslog' => [
            'driver'               => 'syslog',
            'level'                => env('LOG_LEVEL', 'debug'),
            'facility'             => env('LOG_SYSLOG_FACILITY', LOG_USER),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver'               => 'errorlog',
            'level'                => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],

    ],

];
