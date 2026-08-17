<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — GitHub-Style JSON Root API Gateway Entrypoint
|--------------------------------------------------------------------------
|
| Access Tiers:
|   PUBLIC   — No token. Read-only catalog (products, categories, variants).
|   AUTH     — Bearer token required (any role).
|   MANAGER  — Bearer token + role MANAGER or ADMIN.
|   ADMIN    — Bearer token + role ADMIN only.
|
| Login: POST /api/v1/auth/login  → returns access_token + permissions[]
|
*/

Route::get('/', function () {
    $base = rtrim(config('app.url', 'https://api.kesararamwithdigital.tech'), '/');

    return response()->json([
        'brand_name'         => 'KhmeRiel',
        'brand_tagline'      => 'KhmeRiel • Clothing & POS MIS',
        'brand_logo_url'     => 'https://res.cloudinary.com/od8t271n/image/upload/v1786898754/KhmerRiel.png',
        'system'             => 'KhmeRiel Store Stock & Point-of-Sale MIS API',
        'version'            => '1.0.0',
        'status'             => 'online',
        'frontend_url'       => 'https://app.kesararamwithdigital.tech',
        'documentation_url'  => "{$base}/guide",
        'guide_api_url'      => "{$base}/api/v1/guide",

        // ── Public (no token) ─────────────────────────────────────────────
        'health_url'             => "{$base}/api/v1/health",
        'products_url'           => "{$base}/api/v1/products",
        'categories_url'         => "{$base}/api/v1/categories",
        'variants_url'           => "{$base}/api/v1/variants",
        'clothing_sizes_url'     => "{$base}/api/v1/clothing-sizes",
        'colors_url'             => "{$base}/api/v1/colors",

        // ── Authentication ────────────────────────────────────────────────
        'auth_login_url'         => "{$base}/api/v1/auth/login",
        'auth_me_url'            => "{$base}/api/v1/auth/me",
        'auth_logout_url'        => "{$base}/api/v1/auth/logout",
        'auth_register_url'      => "{$base}/api/v1/auth/register",   // ADMIN only

        // ── Authenticated (any role) ──────────────────────────────────────
        'customers_url'          => "{$base}/api/v1/customers",
        'sales_url'              => "{$base}/api/v1/sales",
        'pos_checkout_url'       => "{$base}/api/v1/sales/checkout",

        // ── Manager & Admin ───────────────────────────────────────────────
        'suppliers_url'          => "{$base}/api/v1/suppliers",
        'purchases_url'          => "{$base}/api/v1/purchases",
        'stock_movements_url'    => "{$base}/api/v1/stock-movements",
        'media_gallery_url'      => "{$base}/api/v1/uploads/gallery",
        'media_upload_url'       => "{$base}/api/v1/uploads/image",
        'media_batch_upload_url' => "{$base}/api/v1/uploads/batch",
        'dashboard_stats_url'    => "{$base}/api/v1/dashboard/stats",
        'audit_logs_url'         => "{$base}/api/v1/audit-logs",

        // ── Admin only ────────────────────────────────────────────────────
        'employees_url'          => "{$base}/api/v1/employees",
    ], 200, ['Content-Type' => 'application/json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
});

// SalesBinder-style Help Centre & Interactive Guide
Route::get('/guide', [\App\Http\Controllers\Api\V1\HelpCentreGuideController::class, 'index']);
Route::get('/kb', [\App\Http\Controllers\Api\V1\HelpCentreGuideController::class, 'index']);

