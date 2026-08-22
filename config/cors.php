<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // Production origins ship by default. Set CORS_ALLOWED_ORIGINS (comma
    // separated) to override; local dev origins are merged in automatically
    // when APP_ENV is local/testing so production never ships localhost.
    'allowed_origins' => array_values(array_unique(array_merge(
        [
            'https://api.kesararamwithdigital.tech',
            'https://kesararamwithdigital.tech',
            'https://app.kesararamwithdigital.tech',
            'https://ss-mis.vercel.app',
            'https://ss-mis.pages.dev',
        ],
        array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', '')))),
        in_array(env('APP_ENV', 'production'), ['local', 'testing', 'development'], true) ? [
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:5173',
            'http://127.0.0.1:5173',
            'http://localhost:8080',
            'http://127.0.0.1:8080',
            'http://localhost:4200',
            'http://127.0.0.1:4200',
        ] : []
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['X-Request-Id', 'Retry-After', 'X-RateLimit-Reset'],

    // Preflight results are cacheable for 24h - avoids one OPTIONS round
    // trip per browser session per endpoint.
    'max_age' => 86400,

    'supports_credentials' => true,

];
