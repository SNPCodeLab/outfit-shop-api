<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | Cloudinary provides image and video upload, storage, and transformation.
    | Parses either full CLOUDINARY_URL or individual credentials.
    |
    */

    'cloud_url' => env('CLOUDINARY_URL'),

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME', function () {
        if ($url = env('CLOUDINARY_URL')) {
            $parsed = parse_url($url);
            return $parsed['host'] ?? null;
        }
        return 'od8t271n';
    }),

    'api_key' => env('CLOUDINARY_API_KEY', function () {
        if ($url = env('CLOUDINARY_URL')) {
            $parsed = parse_url($url);
            return $parsed['user'] ?? null;
        }
        return '292517627621863';
    }),

    'api_secret' => env('CLOUDINARY_API_SECRET', function () {
        if ($url = env('CLOUDINARY_URL')) {
            $parsed = parse_url($url);
            return $parsed['pass'] ?? null;
        }
        return 'CZhMlOoVVxAQBS_Vc_OrnPtqr4g';
    }),

    'folder' => env('CLOUDINARY_FOLDER', 'khmeriel/products'),

    'secure' => env('CLOUDINARY_SECURE', true),
];
