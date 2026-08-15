<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ClothingSizeController;
use App\Http\Controllers\Api\V1\ColorController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\EmployeeController;
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
| SS-MIS RESTful Web API Gateway Routes
|--------------------------------------------------------------------------
| Base URL: https://api.kesararamwithdigital.tech/api/v1
| All endpoints are versioned under /v1/
*/

Route::prefix('v1')->group(function () {

    // --------------------------------------------------------------------------
    // 1. PUBLIC AUTHENTICATION & CATALOG (Unprotected + Rate Limiting)
    // --------------------------------------------------------------------------

    // Authentication (Rate limited to 10 attempts/min to prevent brute-force)
    Route::prefix('auth')->middleware('throttle:10,1')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    // Health Check & Status
    Route::get('/status', [StatusController::class, 'index']);

    // Public Product Catalog Read Endpoints
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    Route::get('/clothing-sizes', [ClothingSizeController::class, 'index']);
    Route::get('/clothing-sizes/{id}', [ClothingSizeController::class, 'show']);

    Route::get('/colors', [ColorController::class, 'index']);
    Route::get('/colors/{id}', [ColorController::class, 'show']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    Route::get('/variants', [ProductVariantController::class, 'index']);
    Route::get('/variants/low-stock', [ProductVariantController::class, 'lowStock']);
    Route::get('/variants/barcode/{barcode}', [ProductVariantController::class, 'lookupBarcode']);
    Route::get('/variants/{id}', [ProductVariantController::class, 'show']);


    // --------------------------------------------------------------------------
    // 2. PROTECTED API ROUTES (Sanctum Auth Required + Rate Limiting 60 req/min)
    // --------------------------------------------------------------------------
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

        // Authenticated Session Management
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });

        // Analytics & Dashboard Stats (Admin / Manager)
        Route::middleware('role:ADMIN,MANAGER')->group(function () {
            Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        });

        // Customer Management (Cashier, Manager, Admin)
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::put('/customers/{id}', [CustomerController::class, 'update']);

        // POS Sales
        Route::post('/sales/checkout', [SaleController::class, 'checkout']);
        Route::get('/sales', [SaleController::class, 'index']);
        Route::get('/sales/{id}', [SaleController::class, 'show']);

        // Manager & Admin Restricted Routes
        Route::middleware('role:ADMIN,MANAGER')->group(function () {
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::put('/categories/{id}', [CategoryController::class, 'update']);
            Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

            Route::post('/clothing-sizes', [ClothingSizeController::class, 'store']);
            Route::put('/clothing-sizes/{id}', [ClothingSizeController::class, 'update']);
            Route::delete('/clothing-sizes/{id}', [ClothingSizeController::class, 'destroy']);

            Route::post('/colors', [ColorController::class, 'store']);
            Route::put('/colors/{id}', [ColorController::class, 'update']);
            Route::delete('/colors/{id}', [ColorController::class, 'destroy']);

            Route::post('/products', [ProductController::class, 'store']);
            Route::put('/products/{id}', [ProductController::class, 'update']);
            Route::delete('/products/{id}', [ProductController::class, 'destroy']);

            Route::post('/variants', [ProductVariantController::class, 'store']);
            Route::put('/variants/{id}', [ProductVariantController::class, 'update']);
            Route::delete('/variants/{id}', [ProductVariantController::class, 'destroy']);

            Route::get('/suppliers', [SupplierController::class, 'index']);
            Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
            Route::post('/suppliers', [SupplierController::class, 'store']);
            Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
            Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']);

            Route::get('/purchases', [PurchaseController::class, 'index']);
            Route::get('/purchases/{id}', [PurchaseController::class, 'show']);
            Route::post('/purchases', [PurchaseController::class, 'store']);

            Route::get('/stock-movements', [StockMovementController::class, 'index']);
            Route::post('/stock-movements/adjust', [StockMovementController::class, 'adjust']);

            Route::post('/sales/{id}/void', [SaleController::class, 'void']);

            Route::get('/audit-logs', [AuditLogController::class, 'index']);
            Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);
        });

        // Employee Management (Admin Only)
        Route::middleware('role:ADMIN')->group(function () {
            Route::get('/employees', [EmployeeController::class, 'index']);
            Route::get('/employees/{id}', [EmployeeController::class, 'show']);
            Route::post('/employees', [EmployeeController::class, 'store']);
            Route::put('/employees/{id}', [EmployeeController::class, 'update']);
            Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);
        });
    });
});
