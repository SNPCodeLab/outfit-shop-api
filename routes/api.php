<?php

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
| SS-MIS RESTful Web API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public Status & Auth Endpoints
    Route::get('/status', [StatusController::class, 'index']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected Employee Endpoints
    Route::middleware('auth:sanctum')->group(function () {

        // Profile & Session
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Catalog & Inventory Lookups (Read-only for all employees)
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

        // Customers Management (All Employees)
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);
        Route::put('/customers/{id}', [CustomerController::class, 'update']);

        // POS Sales & Receipts (All Employees / Cashiers)
        Route::post('/sales/checkout', [SaleController::class, 'checkout']);
        Route::get('/sales', [SaleController::class, 'index']);
        Route::get('/sales/{id}', [SaleController::class, 'show']);

        // ------------------------------------------------------------------
        // Manager & Admin Restricted Routes
        // ------------------------------------------------------------------
        Route::middleware('role:ADMIN,MANAGER')->group(function () {

            // Catalog Management (Create/Update/Delete)
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

            // Supplier Management
            Route::get('/suppliers', [SupplierController::class, 'index']);
            Route::post('/suppliers', [SupplierController::class, 'store']);
            Route::get('/suppliers/{id}', [SupplierController::class, 'show']);
            Route::put('/suppliers/{id}', [SupplierController::class, 'update']);
            Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy']);

            // Purchase Orders & Receiving
            Route::get('/purchases', [PurchaseController::class, 'index']);
            Route::post('/purchases', [PurchaseController::class, 'store']);
            Route::get('/purchases/{id}', [PurchaseController::class, 'show']);

            // Inventory Stock Adjustments & Movement Audit
            Route::get('/stock-movements', [StockMovementController::class, 'index']);
            Route::post('/stock-movements/adjust', [StockMovementController::class, 'adjust']);

            // Void Sales
            Route::post('/sales/{id}/void', [SaleController::class, 'void']);

            // Audit Logs
            Route::get('/audit-logs', [AuditLogController::class, 'index']);
            Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);
        });

        // ------------------------------------------------------------------
        // Admin Only Routes
        // ------------------------------------------------------------------
        Route::middleware('role:ADMIN')->group(function () {
            Route::get('/employees', [EmployeeController::class, 'index']);
            Route::post('/employees', [EmployeeController::class, 'store']);
            Route::get('/employees/{id}', [EmployeeController::class, 'show']);
            Route::put('/employees/{id}', [EmployeeController::class, 'update']);
            Route::delete('/employees/{id}', [EmployeeController::class, 'destroy']);
        });
    });
});
