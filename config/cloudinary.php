<?php

declare(strict_types=1);

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

    'cloud_url' => env('CLOUDINARY_URL', 'cloudinary://292517627621863:CZhMlOoVVxAQBS_Vc_OrnPtqr4g@od8t271n'),

    'cloud_name' => env('CLOUDINARY_CLOUD_NAME', 'od8t271n'),

    'api_key' => env('CLOUDINARY_API_KEY', '292517627621863'),

    'api_secret' => env('CLOUDINARY_API_SECRET', 'CZhMlOoVVxAQBS_Vc_OrnPtqr4g'),

    'folder' => env('CLOUDINARY_FOLDER', 'khmeriel/products'),

    'secure' => env('CLOUDINARY_SECURE', true),
];
