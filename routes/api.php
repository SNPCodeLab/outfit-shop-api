<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ClothingSizeController;
use App\Http\Controllers\Api\V1\ColorController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\ImageUploadController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductVariantController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\StatusController;
use App\Http\Controllers\Api\V1\StockMovementController;
use App\Http\Controllers\Api\V1\SupplierController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SS-MIS RESTful API — v1
|--------------------------------------------------------------------------
|
| Access Tiers:
|   PUBLIC      — No token required. Read-only catalog endpoints.
|   AUTHENTICATED— Requires valid Bearer token (any role).
|   MANAGER     — Requires token + role MANAGER or ADMIN.
|   ADMIN       — Requires token + role ADMIN only.
|
| Base URL: https://api.kesararamwithdigital.tech/api/v1
|
*/

Route::prefix('v1')->group(function () {

    // =========================================================================
    // TIER 1 — PUBLIC (No authentication required)
    // =========================================================================

    // Health & Status — always open
    Route::get('/health', [StatusController::class, 'index']);
    Route::get('/status', [StatusController::class, 'index']);

    // Authentication — rate-limited to prevent brute-force
    Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
    });

    // Public Product Catalog — read-only, no token needed
    Route::get('/categories',                          [CategoryController::class,      'index']);
    Route::get('/categories/{id}',                     [CategoryController::class,      'show']);

    Route::get('/clothing-sizes',                      [ClothingSizeController::class,  'index']);
    Route::get('/clothing-sizes/{id}',                 [ClothingSizeController::class,  'show']);

    Route::get('/colors',                              [ColorController::class,         'index']);
    Route::get('/colors/{id}',                         [ColorController::class,         'show']);

    Route::get('/products',                            [ProductController::class,       'index']);
    Route::get('/products/{id}',                       [ProductController::class,       'show']);

    Route::get('/variants',                            [ProductVariantController::class,'index']);
    Route::get('/variants/low-stock',                  [ProductVariantController::class,'lowStock']);
    Route::get('/variants/barcode/{barcode}',          [ProductVariantController::class,'lookupBarcode']);
    Route::get('/variants/{id}',                       [ProductVariantController::class,'show']);


    // =========================================================================
    // TIER 2 — AUTHENTICATED (Any valid Bearer token)
    // =========================================================================
    Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {

        // -- Session Management --
        Route::prefix('auth')->group(function () {
            Route::get('/me',       [AuthController::class, 'me']);
            Route::post('/logout',  [AuthController::class, 'logout']);
        });

        // -- Customer Management (Cashier, Manager, Admin) --
        Route::get('/customers',          [CustomerController::class, 'index']);
        Route::get('/customers/{id}',     [CustomerController::class, 'show']);
        Route::post('/customers',         [CustomerController::class, 'store']);
        Route::put('/customers/{id}',     [CustomerController::class, 'update']);

        // -- POS Sales (Cashier, Manager, Admin) --
        Route::post('/sales/checkout',    [SaleController::class, 'checkout']);
        Route::get('/sales',              [SaleController::class, 'index']);
        Route::get('/sales/{id}',         [SaleController::class, 'show']);


        // =====================================================================
        // TIER 3 — MANAGER (role: MANAGER or ADMIN)
        // =====================================================================
        Route::middleware('role:MANAGER,ADMIN')->group(function () {

            // Analytics & Dashboard
            Route::get('/dashboard/stats',             [DashboardController::class,     'stats']);

            // Catalog Write Access
            Route::post('/categories',                 [CategoryController::class,      'store']);
            Route::put('/categories/{id}',             [CategoryController::class,      'update']);
            Route::delete('/categories/{id}',          [CategoryController::class,      'destroy']);

            Route::post('/clothing-sizes',             [ClothingSizeController::class,  'store']);
            Route::put('/clothing-sizes/{id}',         [ClothingSizeController::class,  'update']);
            Route::delete('/clothing-sizes/{id}',      [ClothingSizeController::class,  'destroy']);

            Route::post('/colors',                     [ColorController::class,         'store']);
            Route::put('/colors/{id}',                 [ColorController::class,         'update']);
            Route::delete('/colors/{id}',              [ColorController::class,         'destroy']);

            Route::post('/products',                   [ProductController::class,       'store']);
            Route::put('/products/{id}',               [ProductController::class,       'update']);
            Route::delete('/products/{id}',            [ProductController::class,       'destroy']);

            Route::post('/variants',                   [ProductVariantController::class,'store']);
            Route::put('/variants/{id}',               [ProductVariantController::class,'update']);
            Route::delete('/variants/{id}',            [ProductVariantController::class,'destroy']);

            // Suppliers & Purchasing
            Route::get('/suppliers',                   [SupplierController::class,      'index']);
            Route::get('/suppliers/{id}',              [SupplierController::class,      'show']);
            Route::post('/suppliers',                  [SupplierController::class,      'store']);
            Route::put('/suppliers/{id}',              [SupplierController::class,      'update']);
            Route::delete('/suppliers/{id}',           [SupplierController::class,      'destroy']);

            Route::get('/purchases',                   [PurchaseController::class,      'index']);
            Route::get('/purchases/{id}',              [PurchaseController::class,      'show']);
            Route::post('/purchases',                  [PurchaseController::class,      'store']);

            // Inventory
            Route::get('/stock-movements',             [StockMovementController::class, 'index']);
            Route::post('/stock-movements/adjust',     [StockMovementController::class, 'adjust']);

            // Cloudinary Image Media Management
            Route::get('/uploads/gallery',                   [ImageUploadController::class,   'gallery']);
            Route::post('/uploads/image',                    [ImageUploadController::class,   'upload']);
            Route::post('/uploads/batch',                    [ImageUploadController::class,   'uploadBatch']);
            Route::delete('/uploads/image',                  [ImageUploadController::class,   'destroy']);
            Route::post('/products/{id}/image',              [ImageUploadController::class,   'uploadForProduct']);
            Route::post('/variants/{id}/image',              [ImageUploadController::class,   'uploadForVariant']);

            // Sales Management
            Route::post('/sales/{id}/void',            [SaleController::class,          'void']);

            // Audit Logs
            Route::get('/audit-logs',                  [AuditLogController::class,      'index']);
            Route::get('/audit-logs/{id}',             [AuditLogController::class,      'show']);
        });


        // =====================================================================
        // TIER 4 — ADMIN (role: ADMIN only)
        // =====================================================================
        Route::middleware('role:ADMIN')->group(function () {

            // Employee Management — full CRUD
            Route::get('/employees',                   [EmployeeController::class,      'index']);
            Route::get('/employees/{id}',              [EmployeeController::class,      'show']);
            Route::post('/employees',                  [EmployeeController::class,      'store']);
            Route::put('/employees/{id}',              [EmployeeController::class,      'update']);
            Route::delete('/employees/{id}',           [EmployeeController::class,      'destroy']);

            // User Account Management (create accounts for frontend team)
            Route::prefix('auth')->group(function () {
                Route::post('/register', [AuthController::class, 'register']);
            });
        });
    });
});
