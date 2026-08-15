<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - GitHub-Style JSON Root API Gateway Entrypoint
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $baseUrl = config('app.url', 'https://api.kesararamwithdigital.tech');

    return response()->json([
        'system' => 'Store Stock & Point-of-Sale MIS API',
        'version' => '1.0.0',
        'status' => 'online',
        'documentation_url' => 'https://github.com/SNPbuilds/csms-api',
        'health_url' => "{$baseUrl}/api/v1/health",
        'auth_login_url' => "{$baseUrl}/api/v1/auth/login",
        'auth_me_url' => "{$baseUrl}/api/v1/auth/me",
        'products_url' => "{$baseUrl}/api/v1/products",
        'product_barcode_url' => "{$baseUrl}/api/v1/products/barcode/{barcode}",
        'categories_url' => "{$baseUrl}/api/v1/categories",
        'variants_url' => "{$baseUrl}/api/v1/variants",
        'pos_checkout_url' => "{$baseUrl}/api/v1/pos/checkout",
        'sales_url' => "{$baseUrl}/api/v1/sales",
        'purchases_url' => "{$baseUrl}/api/v1/purchases",
        'employees_url' => "{$baseUrl}/api/v1/employees",
        'customers_url' => "{$baseUrl}/api/v1/customers",
        'suppliers_url' => "{$baseUrl}/api/v1/suppliers",
    ], 200, ['Content-Type' => 'application/json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});
