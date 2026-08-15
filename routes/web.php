<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Pure JSON API Gateway Entrypoint
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'success'       => true,
        'name'          => 'CSMS-API (Store Stock & Point-of-Sale System)',
        'version'       => '1.0.0',
        'status'        => 'online',
        'documentation' => 'https://api.kesararamwithdigital.tech/API_DOCS.md',
        'api_base_url'  => 'https://api.kesararamwithdigital.tech/api/v1',
        'database'      => 'Neon Cloud PostgreSQL',
        'endpoints'     => [
            'auth' => [
                'login'    => 'POST /api/v1/auth/login',
                'register' => 'POST /api/v1/auth/register',
                'me'       => 'GET  /api/v1/auth/me',
                'logout'   => 'POST /api/v1/auth/logout',
            ],
            'catalog' => [
                'categories' => 'GET /api/v1/categories',
                'products'   => 'GET /api/v1/products',
                'variants'   => 'GET /api/v1/variants',
            ],
            'sales' => [
                'checkout' => 'POST /api/v1/sales/checkout',
                'void'     => 'POST /api/v1/sales/{id}/void',
            ]
        ]
    ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});
