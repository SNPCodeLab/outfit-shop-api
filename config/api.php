<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | System Name & Branding
    |--------------------------------------------------------------------------
    | Official API branding and system identification.
    */

    'system_name' => env('API_SYSTEM_NAME', 'OutfitShop Ecommerce Clothing API'),

    /*
    |--------------------------------------------------------------------------
    | API Version
    |--------------------------------------------------------------------------
    | Injected into every response envelope under meta.api_version.
    */

    'version' => env('API_VERSION', 'Version: 1.2.0'),

    /*
    |--------------------------------------------------------------------------
    | Support Contact
    |--------------------------------------------------------------------------
    | Included in every error response under meta.support_contact.
    */

    'support_email' => env('API_SUPPORT_EMAIL', ''),

    /*
    |--------------------------------------------------------------------------
    | Documentation Base Path
    |--------------------------------------------------------------------------
    | Used to build meta.documentation links in error responses.
    */

    'docs_base' => env('API_DOCS_BASE', '/api/v1/guide'),

    /*
    |--------------------------------------------------------------------------
    | Brand Assets
    |--------------------------------------------------------------------------
    | Cloudinary-hosted official brand logos, icons, vectors, and video assets.
    */

    'brand' => [
        'name' => 'OutfitShop',
        'title' => 'OutfitShop Ecommerce Clothing API',
        'primary_logo' => 'https://res.cloudinary.com/od8t271n/image/upload/v1787064621/bleu-SNPCodeLab.png',
        'primary_gif' => 'https://res.cloudinary.com/od8t271n/image/upload/v1787062663/default-cycle-SNPCodeLab.gif',
        'secondary_logo' => 'https://res.cloudinary.com/od8t271n/image/upload/v1787062664/bleu-SNPCodeLab.gif',
        'vector_logo' => 'https://res.cloudinary.com/od8t271n/image/upload/v1787062662/anime-SNPCodeLab.svg',
        'video_logo' => 'https://res.cloudinary.com/od8t271n/video/upload/v1787062665/default-cycle-SNPCodeLab.mp4',
        'favicon' => 'https://res.cloudinary.com/od8t271n/image/upload/v1787062662/anime-SNPCodeLab.svg',
    ],

];
