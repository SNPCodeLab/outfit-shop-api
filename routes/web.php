<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\HelpCentreGuideController;
use App\Http\Response\ApiResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — OutfitShop API Gateway Root Entrypoint
|--------------------------------------------------------------------------
|
| This file serves the JSON root index and the help centre guide routes.
| All API routes are defined in routes/api.php under the /api/v1 prefix.
|
*/

Route::get('/', function () {
    $base = rtrim(config('app.url', 'https://api.kesararamwithdigital.tech'), '/');

    return ApiResponse::success([
        'system' => 'OutfitShop-Backend-API',
        'version' => config('api.version', 'Version: 1.2.0'),
        'status' => 'online',
        'frontend_url' => 'https://app.kesararamwithdigital.tech',
        'endpoints' => [
            'guide' => "{$base}/guide",
            'health' => "{$base}/api/v1/health",
            'status' => "{$base}/api/v1/status",
            'postman' => "{$base}/api/v1/postman.json",
        ],
        'public_catalog' => [
            'products' => "{$base}/api/v1/products",
            'categories' => "{$base}/api/v1/categories",
            'variants' => "{$base}/api/v1/variants",
            'clothing_sizes' => "{$base}/api/v1/clothing-sizes",
            'colors' => "{$base}/api/v1/colors",
            'brands' => "{$base}/api/v1/brands",
            'cart' => "{$base}/api/v1/cart",
            'wishlist' => "{$base}/api/v1/wishlist",
        ],
        'authentication' => [
            'login' => "{$base}/api/v1/auth/login",
            'me' => "{$base}/api/v1/auth/me",
            'logout' => "{$base}/api/v1/auth/logout",
            'refresh' => "{$base}/api/v1/auth/refresh",
            'register' => "{$base}/api/v1/auth/register",
        ],
        'authenticated' => [
            'customers' => "{$base}/api/v1/customers",
            'orders' => "{$base}/api/v1/orders",
            'orders_checkout' => "{$base}/api/v1/orders/checkout",
            'sales' => "{$base}/api/v1/orders",          // Legacy alias
            'pos_checkout' => "{$base}/api/v1/orders/checkout", // Legacy alias
            'shifts' => "{$base}/api/v1/shifts/current",
        ],
        'manager_admin' => [
            'suppliers' => "{$base}/api/v1/suppliers",
            'purchases' => "{$base}/api/v1/purchases",
            'stock_movements' => "{$base}/api/v1/stock-movements",
            'stock_transfers' => "{$base}/api/v1/stock-transfers",
            'media_gallery' => "{$base}/api/v1/uploads/gallery",
            'dashboard_stats' => "{$base}/api/v1/dashboard/stats",
            'audit_logs' => "{$base}/api/v1/audit-logs",
            'reports_sales' => "{$base}/api/v1/reports/sales",
        ],
        'admin_only' => [
            'employees' => "{$base}/api/v1/employees",
            'admin_pulse' => "{$base}/api/v1/admin/master-pulse",
            'api_analytics' => "{$base}/api/v1/admin/api-analytics",
        ],
    ], 'OutfitShop-Backend-API gateway is operational');
});

// Help Centre and Interactive Knowledge Base
Route::get('/guide', [HelpCentreGuideController::class, 'index']);
Route::get('/kb', [HelpCentreGuideController::class, 'index']);
