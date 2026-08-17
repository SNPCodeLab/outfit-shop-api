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
|   TIER 1 (PUBLIC)        — No token required. Read-only storefront & catalog.
|   TIER 2 (AUTHENTICATED) — Requires valid Bearer token (CASHIER, STAFF, MANAGER, ADMIN).
|   TIER 3 (MANAGER)       — Requires token + role MANAGER or ADMIN.
|   TIER 4 (ADMIN)         — Requires token + role ADMIN only.
|
| Base URL: https://api.kesararamwithdigital.tech/api/v1
|
*/

Route::prefix('v1')->group(function () {

    // =========================================================================
    // TIER 1 — PUBLIC (No authentication required)
    // =========================================================================

    // Health, Status & Developer Knowledge Base — always open
    Route::get('/health', [StatusController::class, 'index']);
    Route::get('/status', [StatusController::class, 'index']);
    Route::get('/guide',  [\App\Http\Controllers\Api\V1\HelpCentreGuideController::class, 'index']);
    Route::get('/docs',   [\App\Http\Controllers\Api\V1\HelpCentreGuideController::class, 'index']);
    Route::get('/openapi.json', function () {
        $path = base_path('API-Delivery-Package/openapi_spec.json');
        return response()->file($path, ['Content-Type' => 'application/json']);
    });
    Route::get('/postman.json', function () {
        $path = base_path('API-Delivery-Package/postman_collection.json');
        return response()->file($path, ['Content-Type' => 'application/json']);
    });

    // Authentication — rate-limited to prevent brute-force (10 attempts / min)
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
    Route::get('/products/{id}/images',                [\App\Http\Controllers\Api\V1\ProductImageController::class, 'index']);
    Route::get('/products/{id}/matrix',                [\App\Http\Controllers\Api\V1\ProductMatrixController::class, 'matrix']);
    Route::get('/products/{id}/colorways',             [\App\Http\Controllers\Api\V1\ProductMatrixController::class, 'colorways']);
    Route::get('/products/{id}/download',              [\App\Http\Controllers\Api\V1\DigitalAssetController::class, 'download']);
    Route::get('/products/{id}/reviews',               [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'index']);
    Route::post('/products/{id}/reviews',              [\App\Http\Controllers\Api\V1\ProductReviewController::class, 'store']);

    Route::get('/brands',                              [\App\Http\Controllers\Api\V1\BrandController::class, 'index']);
    Route::get('/brands/{id}',                         [\App\Http\Controllers\Api\V1\BrandController::class, 'show']);

    Route::get('/bundles',                             [\App\Http\Controllers\Api\V1\ProductBundleController::class, 'index']);
    Route::get('/bundles/{id}',                        [\App\Http\Controllers\Api\V1\ProductBundleController::class, 'show']);

    Route::get('/promotions/active',                   [\App\Http\Controllers\Api\V1\PromotionController::class, 'active']);
    Route::post('/promotions/verify-coupon',           [\App\Http\Controllers\Api\V1\PromotionController::class, 'verifyCoupon']);

    Route::get('/branches',                            [\App\Http\Controllers\Api\V1\StoreBranchController::class, 'index']);
    Route::get('/wishlist',                            [\App\Http\Controllers\Api\V1\CustomerWishlistController::class, 'index']);
    Route::post('/wishlist/toggle',                    [\App\Http\Controllers\Api\V1\CustomerWishlistController::class, 'toggle']);

    Route::get('/variants',                            [ProductVariantController::class,'index']);
    Route::get('/variants/low-stock',                  [ProductVariantController::class,'lowStock']);
    Route::get('/variants/barcode/{barcode}',          [ProductVariantController::class,'lookupBarcode']);
    Route::get('/variants/{id}',                       [ProductVariantController::class,'show'])->whereNumber('id');
    Route::get('/variants/{id}/tiers',                 [\App\Http\Controllers\Api\V1\VariantPricingTierController::class, 'index']);
    Route::get('/variants/{id}/barcode-label',         [\App\Http\Controllers\Api\V1\BarcodePrintController::class, 'barcodeLabel']);

    // SalesBinder Inventory Statistics & Valuation (High-speed cached)
    Route::get('/inventory/statistics',                [\App\Http\Controllers\Api\V1\InventoryValuationController::class, 'statistics']);

    // Payments, KHQR & Hardware Print Services
    Route::get('/payments/khqr',                       [\App\Http\Controllers\Api\V1\KhqrPaymentController::class, 'generateCustom']);
    Route::get('/sales/{id}/khqr',                     [\App\Http\Controllers\Api\V1\KhqrPaymentController::class, 'generateForSale']);
    Route::get('/sales/{id}/receipt-thermal',          [\App\Http\Controllers\Api\V1\BarcodePrintController::class, 'receiptThermal']);
    Route::get('/sales/{id}/invoice-pdf',              [\App\Http\Controllers\Api\V1\InvoiceEstimateController::class, 'renderInvoiceHtml']);
    Route::post('/gift-cards/check',                   [\App\Http\Controllers\Api\V1\GiftCardController::class, 'check']);

    // Storefront CMS Banners & System Audio Settings
    Route::get('/marketing/banners',                   [\App\Http\Controllers\Api\V1\MarketingBannerController::class, 'index']);
    Route::get('/settings/audio-cues',                 [\App\Http\Controllers\Api\V1\SystemSettingController::class, 'audioCues']);


    // =========================================================================
    // TIER 2 — AUTHENTICATED (CASHIER, STAFF, MANAGER, ADMIN)
    // =========================================================================
    Route::middleware(['auth:sanctum', 'throttle:role-based'])->group(function () {

        // -- Session Management & Token Rotation --
        Route::prefix('auth')->group(function () {
            Route::get('/me',          [AuthController::class, 'me']);
            Route::post('/logout',     [AuthController::class, 'logout']);
            Route::post('/refresh',    [AuthController::class, 'refresh']);
            Route::post('/revoke-all', [AuthController::class, 'revokeAll']);
        });

        // -- Customer Management & Loyalty Points --
        Route::get('/customers',                       [CustomerController::class, 'index']);
        Route::get('/customers/{id}',                  [CustomerController::class, 'show']);
        Route::post('/customers',                      [CustomerController::class, 'store']);
        Route::put('/customers/{id}',                  [CustomerController::class, 'update']);
        Route::get('/customers/{id}/loyalty',          [\App\Http\Controllers\Api\V1\CustomerLoyaltyController::class, 'show']);
        Route::post('/customers/{id}/redeem-points',   [\App\Http\Controllers\Api\V1\CustomerLoyaltyController::class, 'redeem']);

        // -- POS Cash Register Shifts (Z-Report) --
        Route::get('/shifts/current',                  [\App\Http\Controllers\Api\V1\PosShiftController::class, 'current']);
        Route::post('/shifts/open',                    [\App\Http\Controllers\Api\V1\PosShiftController::class, 'open']);
        Route::post('/shifts/drop-cash',               [\App\Http\Controllers\Api\V1\PosShiftController::class, 'dropCash']);
        Route::post('/shifts/close',                   [\App\Http\Controllers\Api\V1\PosShiftController::class, 'close']);

        // -- POS Sales, Invoices & Estimates (SalesBinder Engine) --
        Route::post('/sales/checkout',                 [SaleController::class, 'checkout']);
        Route::get('/sales',                           [SaleController::class, 'index']);
        Route::get('/sales/{id}',                      [SaleController::class, 'show']);

        Route::get('/invoices',                        [\App\Http\Controllers\Api\V1\InvoiceEstimateController::class, 'index']);
        Route::post('/estimates',                      [\App\Http\Controllers\Api\V1\InvoiceEstimateController::class, 'createEstimate']);
        Route::post('/estimates/{id}/convert',         [\App\Http\Controllers\Api\V1\InvoiceEstimateController::class, 'convertEstimateToInvoice']);
        Route::post('/gift-cards/issue',               [\App\Http\Controllers\Api\V1\GiftCardController::class, 'issue']);

        // -- Omnichannel Shipping & Click-and-Collect --
        Route::get('/shipping/orders',                 [\App\Http\Controllers\Api\V1\ShippingOrderController::class, 'index']);
        Route::post('/shipping/create',                [\App\Http\Controllers\Api\V1\ShippingOrderController::class, 'create']);
        Route::post('/shipping/{id}/status',           [\App\Http\Controllers\Api\V1\ShippingOrderController::class, 'updateStatus']);

        // -- Live Role-Pulse Analytics (Pie & Agile Graph tracking per role) --
        Route::get('/dashboard/role-pulse',            [\App\Http\Controllers\Api\DashboardController::class, 'rolePulse']);

        // Active Broadcast Alerts feed for all logged-in staff
        Route::get('/alerts/active', function () {
            $alerts = \Illuminate\Support\Facades\DB::table('system_broadcast_alerts')
                ->where('is_active', true)
                ->orderBy('alert_id', 'DESC')
                ->get();
            return response()->json(['success' => true, 'data' => $alerts]);
        });


        // =====================================================================
        // TIER 3 — MANAGER (role: MANAGER or ADMIN)
        // =====================================================================
        Route::middleware('role:MANAGER,ADMIN')->group(function () {

            // Analytics, Dashboard & Restock Forecasting
            Route::get('/dashboard/stats',                      [DashboardController::class,     'stats']);
            Route::get('/inventory/restock-recommendations',    [\App\Http\Controllers\Api\V1\InventoryForecastingController::class, 'restockRecommendations']);
            Route::post('/purchases/auto-generate',             [\App\Http\Controllers\Api\V1\InventoryForecastingController::class, 'autoGeneratePurchaseOrder']);

            // Catalog Write Access
            Route::post('/categories',                 [CategoryController::class,      'store']);
            Route::put('/categories/{id}',             [CategoryController::class,      'update']);
            Route::delete('/categories/{id}',          [CategoryController::class,      'destroy']);

            Route::post('/brands',                     [\App\Http\Controllers\Api\V1\BrandController::class, 'store']);
            Route::put('/brands/{id}',                 [\App\Http\Controllers\Api\V1\BrandController::class, 'update']);
            Route::delete('/brands/{id}',              [\App\Http\Controllers\Api\V1\BrandController::class, 'destroy']);

            Route::post('/clothing-sizes',             [ClothingSizeController::class,  'store']);
            Route::put('/clothing-sizes/{id}',         [ClothingSizeController::class,  'update']);
            Route::delete('/clothing-sizes/{id}',      [ClothingSizeController::class,  'destroy']);

            Route::post('/colors',                     [ColorController::class,         'store']);
            Route::put('/colors/{id}',                 [ColorController::class,         'update']);
            Route::delete('/colors/{id}',              [ColorController::class,         'destroy']);

            Route::post('/products',                   [ProductController::class,       'store']);
            Route::put('/products/{id}',               [ProductController::class,       'update']);
            Route::delete('/products/{id}',            [ProductController::class,       'destroy']);
            Route::post('/products/{id}/images',       [\App\Http\Controllers\Api\V1\ProductImageController::class, 'store']);
            Route::delete('/products/{id}/images/{imageId}', [\App\Http\Controllers\Api\V1\ProductImageController::class, 'destroy']);

            Route::post('/variants',                   [ProductVariantController::class,'store']);
            Route::put('/variants/{id}',               [ProductVariantController::class,'update']);
            Route::delete('/variants/{id}',            [ProductVariantController::class,'destroy']);
            Route::post('/variants/{id}/tiers',        [\App\Http\Controllers\Api\V1\VariantPricingTierController::class, 'store']);

            // Bundles & Promotions
            Route::post('/bundles',                    [\App\Http\Controllers\Api\V1\ProductBundleController::class, 'store']);
            Route::delete('/bundles/{id}',             [\App\Http\Controllers\Api\V1\ProductBundleController::class, 'destroy']);

            Route::get('/promotions',                  [\App\Http\Controllers\Api\V1\PromotionController::class, 'index']);
            Route::post('/promotions',                 [\App\Http\Controllers\Api\V1\PromotionController::class, 'store']);
            Route::delete('/promotions/{id}',          [\App\Http\Controllers\Api\V1\PromotionController::class, 'destroy']);

            // Multi-Branch Management
            Route::get('/branches/{id}/stock',         [\App\Http\Controllers\Api\V1\StoreBranchController::class, 'branchStock']);
            Route::post('/branches',                   [\App\Http\Controllers\Api\V1\StoreBranchController::class, 'store']);

            // FMCG FIFO & Batch Tracking
            Route::get('/inventory/expiring-soon',     [\App\Http\Controllers\Api\V1\InventoryBatchController::class, 'expiringSoon']);
            Route::get('/variants/{id}/batches',       [\App\Http\Controllers\Api\V1\InventoryBatchController::class, 'listBatches']);
            Route::post('/variants/{id}/batches',      [\App\Http\Controllers\Api\V1\InventoryBatchController::class, 'storeBatch']);

            // Storefront CMS Marketing Banners
            Route::post('/marketing/banners',          [\App\Http\Controllers\Api\V1\MarketingBannerController::class, 'store']);
            Route::delete('/marketing/banners/{id}',   [\App\Http\Controllers\Api\V1\MarketingBannerController::class, 'destroy']);

            // Suppliers & Purchasing
            Route::get('/suppliers',                   [SupplierController::class,      'index']);
            Route::get('/suppliers/{id}',              [SupplierController::class,      'show']);
            Route::post('/suppliers',                  [SupplierController::class,      'store']);
            Route::put('/suppliers/{id}',              [SupplierController::class,      'update']);
            Route::delete('/suppliers/{id}',           [SupplierController::class,      'destroy']);

            Route::get('/purchases',                   [PurchaseController::class,      'index']);
            Route::get('/purchases/{id}',              [PurchaseController::class,      'show']);
            Route::post('/purchases',                  [PurchaseController::class,      'store']);

            // Inventory Stock Ledger & Adjustments
            Route::get('/stock-movements',             [StockMovementController::class, 'index']);
            Route::post('/stock-movements/adjust',     [StockMovementController::class, 'adjust']);

            // Cloudinary Image Media Management
            Route::get('/uploads/gallery',             [ImageUploadController::class,   'gallery']);
            Route::post('/uploads/image',              [ImageUploadController::class,   'upload']);
            Route::post('/uploads/batch',              [ImageUploadController::class,   'uploadBatch']);
            Route::delete('/uploads/image',            [ImageUploadController::class,   'destroy']);
            Route::post('/products/{id}/image',        [ImageUploadController::class,   'uploadForProduct']);
            Route::post('/variants/{id}/image',        [ImageUploadController::class,   'uploadForVariant']);

            // Sales Voiding
            Route::post('/sales/{id}/void',            [SaleController::class,          'void']);

            // Audit Logs (Accessible by Manager and Admin)
            Route::get('/audit-logs',                  [AuditLogController::class,      'index']);
            Route::get('/audit-logs/{id}',             [AuditLogController::class,      'show']);

            // Bulk / Batch Operations (1 Request vs 100)
            Route::post('/inventory/bulk-adjust',      [\App\Http\Controllers\Api\V1\BulkOperationController::class, 'bulkAdjust']);
            Route::post('/variants/bulk-price-update', [\App\Http\Controllers\Api\V1\BulkOperationController::class, 'bulkPriceUpdate']);
            Route::post('/products/bulk-import',       [\App\Http\Controllers\Api\V1\BulkOperationController::class, 'bulkImport']);
            Route::post('/purchases/bulk-receive',     [\App\Http\Controllers\Api\V1\BulkOperationController::class, 'bulkReceive']);

            // File Exports (PDF, Excel, CSV, Thermal POS format)
            Route::get('/exports/inventory/excel',       [\App\Http\Controllers\Api\V1\FileExportController::class, 'exportInventory']);
            Route::get('/exports/stock-movements/csv',    [\App\Http\Controllers\Api\V1\FileExportController::class, 'exportStockMovements']);
            Route::get('/exports/sales-report/pdf',      [\App\Http\Controllers\Api\V1\FileExportController::class, 'exportSalesReport']);
            Route::get('/exports/z-report/{id}/thermal', [\App\Http\Controllers\Api\V1\FileExportController::class, 'exportZReportThermal']);

            // Webhook Subscription Management (Events: LOW_STOCK_ALERT, PO_RECEIVED, SHIFT_DISCREPANCY, REFUND_REQUESTED, STOCK_TRANSFER_COMPLETED)
            Route::get('/webhooks',                    [\App\Http\Controllers\Api\V1\WebhookSubscriptionController::class, 'index']);
            Route::post('/webhooks/subscribe',          [\App\Http\Controllers\Api\V1\WebhookSubscriptionController::class, 'subscribe']);
            Route::post('/webhooks/test',               [\App\Http\Controllers\Api\V1\WebhookSubscriptionController::class, 'test']);
            Route::delete('/webhooks/{id}',            [\App\Http\Controllers\Api\V1\WebhookSubscriptionController::class, 'destroy']);
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

            // User Account Management
            Route::prefix('auth')->group(function () {
                Route::post('/register', [AuthController::class, 'register']);
            });

            // Admin Master Tracking Pulse & Broadcast Alert System
            Route::get('/admin/master-pulse',          [\App\Http\Controllers\Api\V1\AdminMasterController::class, 'masterPulse']);
            Route::post('/admin/broadcast-alert',      [\App\Http\Controllers\Api\V1\AdminMasterController::class, 'broadcastAlert']);
        });
    });
});

