<?php

declare(strict_types=1);

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\V1\AdminAnalyticsController;
use App\Http\Controllers\Api\V1\AdminMasterController;
use App\Http\Controllers\Api\V1\AdminPerformanceController;
use App\Http\Controllers\Api\V1\AiIntelligenceController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BarcodePrintController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\BulkOperationController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ClothingSizeController;
use App\Http\Controllers\Api\V1\ColorController;
use App\Http\Controllers\Api\V1\CurrencyController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\CustomerLoyaltyController;
use App\Http\Controllers\Api\V1\CustomerWishlistController;
use App\Http\Controllers\Api\V1\DigitalAssetController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\FileExportController;
use App\Http\Controllers\Api\V1\GiftCardController;
use App\Http\Controllers\Api\V1\HelpCentreGuideController;
use App\Http\Controllers\Api\V1\ImageUploadController;
use App\Http\Controllers\Api\V1\InventoryBatchController;
use App\Http\Controllers\Api\V1\InventoryForecastingController;
use App\Http\Controllers\Api\V1\InventoryValuationController;
use App\Http\Controllers\Api\V1\InvoiceEstimateController;
use App\Http\Controllers\Api\V1\KhqrPaymentController;
use App\Http\Controllers\Api\V1\MarketingBannerController;
use App\Http\Controllers\Api\V1\OfflineSyncController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PosShiftController;
use App\Http\Controllers\Api\V1\PrivacyComplianceController;
use App\Http\Controllers\Api\V1\ProductBundleController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductImageController;
use App\Http\Controllers\Api\V1\ProductMatrixController;
use App\Http\Controllers\Api\V1\ProductReviewController;
use App\Http\Controllers\Api\V1\ProductVariantController;
use App\Http\Controllers\Api\V1\PromotionController;
use App\Http\Controllers\Api\V1\PurchaseController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\ShippingOrderController;
use App\Http\Controllers\Api\V1\StatusController;
use App\Http\Controllers\Api\V1\StockMovementController;
use App\Http\Controllers\Api\V1\StockTransferController;
use App\Http\Controllers\Api\V1\StoreBranchController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\SystemSettingController;
use App\Http\Controllers\Api\V1\VariantPricingTierController;
use App\Http\Controllers\Api\V1\WebhookSubscriptionController;
use App\Http\Response\ApiResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OutfitShop-Backend-APIRESTful API — v1
|--------------------------------------------------------------------------
|
| Access Tiers:
|   TIER 1 (PUBLIC)        — No token required. Read-only storefront, catalog, cart, wishlist.
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
    Route::get('/guide', [HelpCentreGuideController::class, 'index']);
    Route::get('/docs', [HelpCentreGuideController::class, 'index']);
    Route::get('/postman.json', function () {
        $path = base_path('API-Delivery-Package/postman_collection.json');

        return response()->file($path, ['Content-Type' => 'application/json']);
    });

    // Multi-Currency (USD Primary + KHR Secondary with NBC Official Benchmarks)
    Route::get('/currencies/rates', [CurrencyController::class, 'rates']);
    Route::post('/currencies/convert', [CurrencyController::class, 'convert']);

    // Authentication — rate-limited to prevent brute-force (5 attempts / min)
    Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])->name('login');
    });

    // Public Product Catalog — read-only, no token needed
    Route::get('/categories', [CategoryController::class,      'index']);
    Route::get('/categories/{id}', [CategoryController::class,      'show']);

    Route::get('/clothing-sizes', [ClothingSizeController::class,  'index']);
    Route::get('/clothing-sizes/{id}', [ClothingSizeController::class,  'show']);

    Route::get('/colors', [ColorController::class,         'index']);
    Route::get('/colors/{id}', [ColorController::class,         'show']);

    Route::get('/products', [ProductController::class,       'index']);
    Route::get('/products/{id}', [ProductController::class,       'show']);
    Route::get('/products/{id}/images', [ProductImageController::class, 'index']);
    Route::get('/products/{id}/matrix', [ProductMatrixController::class, 'matrix']);
    Route::get('/products/{id}/colorways', [ProductMatrixController::class, 'colorways']);
    Route::get('/products/{id}/download', [DigitalAssetController::class, 'download']);
    Route::get('/products/{id}/reviews', [ProductReviewController::class, 'index']);
    Route::post('/products/{id}/reviews', [ProductReviewController::class, 'store']);

    Route::get('/brands', [BrandController::class, 'index']);
    Route::get('/brands/{id}', [BrandController::class, 'show']);

    Route::get('/bundles', [ProductBundleController::class, 'index']);
    Route::get('/bundles/{id}', [ProductBundleController::class, 'show']);

    Route::get('/promotions/active', [PromotionController::class, 'active']);
    Route::post('/promotions/verify-coupon', [PromotionController::class, 'verifyCoupon']);

    Route::get('/branches', [StoreBranchController::class, 'index']);

    // Shopping Cart Endpoints (Guest Session & Customer Cart)
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/items', [CartController::class, 'addItem']);
    Route::put('/cart/items/{id}', [CartController::class, 'updateItem']);
    Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);

    // Customer Wishlist Endpoints
    Route::get('/wishlist', [CustomerWishlistController::class, 'index']);
    Route::post('/wishlist', [CustomerWishlistController::class, 'store']);
    Route::post('/wishlist/toggle', [CustomerWishlistController::class, 'toggle']);
    Route::delete('/wishlist/{id}', [CustomerWishlistController::class, 'destroy']);

    Route::get('/variants', [ProductVariantController::class, 'index']);
    Route::get('/variants/low-stock', [ProductVariantController::class, 'lowStock']);
    Route::get('/variants/barcode/{barcode}', [ProductVariantController::class, 'lookupBarcode']);
    Route::get('/variants/{id}', [ProductVariantController::class, 'show'])->whereNumber('id');
    Route::get('/variants/{id}/tiers', [VariantPricingTierController::class, 'index']);
    Route::get('/variants/{id}/barcode-label', [BarcodePrintController::class, 'barcodeLabel']);

    // Inventory Statistics & Valuation (High-speed cached)
    Route::get('/inventory/statistics', [InventoryValuationController::class, 'statistics']);

    // Payments, KHQR & Hardware Print Services (Orders & Legacy Sales aliases)
    Route::get('/payments/khqr', [KhqrPaymentController::class, 'generateCustom']);
    Route::get('/orders/{id}/khqr', [KhqrPaymentController::class, 'generateForSale']);
    Route::get('/orders/{id}/receipt-thermal', [BarcodePrintController::class, 'receiptThermal']);
    Route::get('/orders/{id}/invoice-pdf', [InvoiceEstimateController::class, 'renderInvoiceHtml']);
    Route::get('/sales/{id}/khqr', [KhqrPaymentController::class, 'generateForSale']);
    Route::get('/sales/{id}/receipt-thermal', [BarcodePrintController::class, 'receiptThermal']);
    Route::get('/sales/{id}/invoice-pdf', [InvoiceEstimateController::class, 'renderInvoiceHtml']);
    Route::post('/gift-cards/check', [GiftCardController::class, 'check']);

    // Storefront CMS Banners & System Audio Settings
    Route::get('/marketing/banners', [MarketingBannerController::class, 'index']);
    Route::get('/settings/audio-cues', [SystemSettingController::class, 'audioCues']);

    // =========================================================================
    // TIER 2 — AUTHENTICATED (CASHIER, STAFF, MANAGER, ADMIN)
    // =========================================================================
    Route::middleware(['auth:sanctum', 'throttle:role-based'])->group(function () {

        // -- Session Management & Token Rotation --
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::post('/revoke-all', [AuthController::class, 'revokeAll']);
            Route::post('/2fa/setup', [AuthController::class, 'setup2FA']);
            Route::post('/2fa/verify', [AuthController::class, 'verify2FA']);
        });

        // -- Customer Management & Loyalty Points --
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::put('/customers/{id}', [CustomerController::class, 'update']);
        Route::get('/customers/{id}/loyalty', [CustomerLoyaltyController::class, 'show']);
        Route::post('/customers/{id}/redeem-points', [CustomerLoyaltyController::class, 'redeem']);

        // -- POS Cash Register Shifts (Z-Report) --
        Route::get('/shifts/current', [PosShiftController::class, 'current']);
        Route::post('/shifts/open', [PosShiftController::class, 'open']);
        Route::post('/shifts/drop-cash', [PosShiftController::class, 'dropCash']);
        Route::post('/shifts/close', [PosShiftController::class, 'close']);

        // -- Orders & Checkouts (First-class endpoints) --
        Route::post('/orders/checkout', [OrderController::class, 'checkout']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);

        // -- Legacy POS Sales Aliases (Backward-Compatibility) --
        Route::post('/sales/checkout', [OrderController::class, 'checkout']);
        Route::get('/sales', [OrderController::class, 'index']);
        Route::get('/sales/{id}', [OrderController::class, 'show']);

        Route::get('/invoices', [InvoiceEstimateController::class, 'index']);
        Route::post('/estimates', [InvoiceEstimateController::class, 'createEstimate']);
        Route::post('/estimates/{id}/convert', [InvoiceEstimateController::class, 'convertEstimateToInvoice']);
        Route::post('/gift-cards/issue', [GiftCardController::class, 'issue']);

        // -- Omnichannel Shipping & Click-and-Collect --
        Route::get('/shipping/orders', [ShippingOrderController::class, 'index']);
        Route::post('/shipping/create', [ShippingOrderController::class, 'create']);
        Route::post('/shipping/{id}/status', [ShippingOrderController::class, 'updateStatus']);

        // -- Offline POS Mode Synchronization & Conflict Resolution --
        Route::get('/offline/manifest', [OfflineSyncController::class, 'manifest']);
        Route::post('/offline/push-transactions', [OfflineSyncController::class, 'pushTransactions']);

        // -- Live Role-Pulse Analytics (Pie & Agile Graph tracking per role) --
        Route::get('/dashboard/role-pulse', [DashboardController::class, 'rolePulse']);

        // Active Broadcast Alerts feed for all logged-in staff
        Route::get('/alerts/active', function () {
            $alerts = DB::table('system_broadcast_alerts')
                ->whereRaw('is_active is true')
                ->orderBy('alert_id', 'DESC')
                ->get();

            return ApiResponse::success(
                $alerts,
                'Active broadcast alerts retrieved'
            );
        });

        // =====================================================================
        // TIER 3 — MANAGER (role: MANAGER or ADMIN)
        // =====================================================================
        Route::middleware('role:MANAGER,ADMIN')->group(function () {

            // Analytics, Dashboard & Restock Forecasting
            Route::get('/dashboard/stats', [DashboardController::class,     'stats']);
            Route::get('/inventory/restock-recommendations', [InventoryForecastingController::class, 'restockRecommendations']);
            Route::post('/purchases/auto-generate', [InventoryForecastingController::class, 'autoGeneratePurchaseOrder']);

            // Catalog Write Access
            Route::post('/categories', [CategoryController::class,      'store']);
            Route::put('/categories/{id}', [CategoryController::class,      'update']);
            Route::delete('/categories/{id}', [CategoryController::class,      'destroy']);

            Route::post('/brands', [BrandController::class, 'store']);
            Route::put('/brands/{id}', [BrandController::class, 'update']);
            Route::delete('/brands/{id}', [BrandController::class, 'destroy']);

            Route::post('/clothing-sizes', [ClothingSizeController::class,  'store']);
            Route::put('/clothing-sizes/{id}', [ClothingSizeController::class,  'update']);
            Route::delete('/clothing-sizes/{id}', [ClothingSizeController::class,  'destroy']);

            Route::post('/colors', [ColorController::class,         'store']);
            Route::put('/colors/{id}', [ColorController::class,         'update']);
            Route::delete('/colors/{id}', [ColorController::class,         'destroy']);

            Route::post('/products', [ProductController::class,       'store']);
            Route::put('/products/{id}', [ProductController::class,       'update']);
            Route::delete('/products/{id}', [ProductController::class,       'destroy']);
            Route::post('/products/{id}/images', [ProductImageController::class, 'store']);
            Route::delete('/products/{id}/images/{imageId}', [ProductImageController::class, 'destroy']);

            Route::post('/variants', [ProductVariantController::class, 'store']);
            Route::put('/variants/{id}', [ProductVariantController::class, 'update']);
            Route::delete('/variants/{id}', [ProductVariantController::class, 'destroy']);
            Route::post('/variants/{id}/tiers', [VariantPricingTierController::class, 'store']);

            // Bundles & Promotions
            Route::post('/bundles', [ProductBundleController::class, 'store']);
            Route::delete('/bundles/{id}', [ProductBundleController::class, 'destroy']);

            Route::get('/promotions', [PromotionController::class, 'index']);
            Route::post('/promotions', [PromotionController::class, 'store']);
            Route::delete('/promotions/{id}', [PromotionController::class, 'destroy']);

            // Multi-Branch Management
            Route::get('/branches/{id}/stock', [StoreBranchController::class, 'branchStock']);
            Route::post('/branches', [StoreBranchController::class, 'store']);

            // FMCG FIFO & Batch Tracking
            Route::get('/inventory/expiring-soon', [InventoryBatchController::class, 'expiringSoon']);
            Route::get('/variants/{id}/batches', [InventoryBatchController::class, 'listBatches']);
            Route::post('/variants/{id}/batches', [InventoryBatchController::class, 'storeBatch']);

            // Storefront CMS Marketing Banners
            Route::post('/marketing/banners', [MarketingBannerController::class, 'store']);
            Route::delete('/marketing/banners/{id}', [MarketingBannerController::class, 'destroy']);

            // Suppliers & Purchasing
            Route::get('/suppliers', [SupplierController::class,      'index']);
            Route::get('/suppliers/{id}', [SupplierController::class,      'show']);
            Route::post('/suppliers', [SupplierController::class,      'store']);
            Route::put('/suppliers/{id}', [SupplierController::class,      'update']);
            Route::delete('/suppliers/{id}', [SupplierController::class,      'destroy']);

            Route::get('/purchases', [PurchaseController::class,      'index']);
            Route::get('/purchases/{id}', [PurchaseController::class,      'show']);
            Route::post('/purchases', [PurchaseController::class,      'store']);

            // Inventory Stock Ledger & Adjustments
            Route::get('/stock-movements', [StockMovementController::class, 'index']);
            Route::post('/stock-movements/adjust', [StockMovementController::class, 'adjust']);

            // Cloudinary Image Media Management
            Route::get('/uploads/gallery', [ImageUploadController::class,   'gallery']);
            Route::post('/uploads/image', [ImageUploadController::class,   'upload']);
            Route::post('/uploads/batch', [ImageUploadController::class,   'uploadBatch']);
            Route::delete('/uploads/image', [ImageUploadController::class,   'destroy']);
            Route::post('/products/{id}/image', [ImageUploadController::class,   'uploadForProduct']);
            Route::post('/variants/{id}/image', [ImageUploadController::class,   'uploadForVariant']);

            // Orders & Sales Voiding
            Route::post('/orders/{id}/void', [OrderController::class,         'void']);
            Route::post('/sales/{id}/void', [OrderController::class,         'void']);

            // Audit Logs (Accessible by Manager and Admin)
            Route::get('/audit-logs', [AuditLogController::class,      'index']);
            Route::get('/audit-logs/{id}', [AuditLogController::class,      'show']);

            // Bulk / Batch Operations (1 Request vs 100)
            Route::post('/inventory/bulk-adjust', [BulkOperationController::class, 'bulkAdjust']);
            Route::post('/variants/bulk-price-update', [BulkOperationController::class, 'bulkPriceUpdate']);
            Route::post('/products/bulk-import', [BulkOperationController::class, 'bulkImport']);
            Route::post('/purchases/bulk-receive', [BulkOperationController::class, 'bulkReceive']);

            // File Exports (PDF, Excel, CSV, Thermal POS format)
            Route::get('/exports/inventory/excel', [FileExportController::class, 'exportInventory']);
            Route::get('/exports/stock-movements/csv', [FileExportController::class, 'exportStockMovements']);
            Route::get('/exports/sales-report/pdf', [FileExportController::class, 'exportSalesReport']);
            Route::get('/exports/z-report/{id}/thermal', [FileExportController::class, 'exportZReportThermal']);

            // Multi-Store Stock Transfers (5-Stage Lifecycle: Request -> Approve -> Pick -> Ship -> Receive)
            Route::get('/stock-transfers', [StockTransferController::class, 'index']);
            Route::get('/stock-transfers/{id}', [StockTransferController::class, 'show']);
            Route::post('/stock-transfers', [StockTransferController::class, 'store']);
            Route::post('/stock-transfers/{id}/approve', [StockTransferController::class, 'approve']);
            Route::post('/stock-transfers/{id}/pick', [StockTransferController::class, 'pick']);
            Route::post('/stock-transfers/{id}/ship', [StockTransferController::class, 'ship']);
            Route::post('/stock-transfers/{id}/receive', [StockTransferController::class, 'receive']);
            Route::post('/stock-transfers/{id}/cancel', [StockTransferController::class, 'cancel']);

            // Advanced MIS Financial & Operational Reports
            Route::get('/reports/sales', [ReportController::class, 'sales']);
            Route::get('/reports/inventory-valuation', [ReportController::class, 'inventoryValuation']);
            Route::get('/reports/stock-aging', [ReportController::class, 'stockAging']);
            Route::get('/reports/customer-purchase-history', [ReportController::class, 'customerPurchaseHistory']);
            Route::get('/reports/supplier-performance', [ReportController::class, 'supplierPerformance']);
            Route::get('/reports/profit-margin', [ReportController::class, 'profitMargin']);
            Route::get('/reports/cash-flow', [ReportController::class, 'cashFlow']);

            // AI Predictive Retail Intelligence & Fraud Anomaly Detection
            Route::get('/ai/sales-forecast', [AiIntelligenceController::class, 'salesForecast']);
            Route::get('/ai/anomaly-detection', [AiIntelligenceController::class, 'anomalyDetection']);
            Route::get('/ai/smart-restock', [AiIntelligenceController::class, 'smartRestock']);
            Route::get('/ai/customer-segmentation', [AiIntelligenceController::class, 'customerSegmentation']);
            Route::get('/ai/dynamic-pricing', [AiIntelligenceController::class, 'dynamicPricing']);

            // GDPR & PCI-DSS Compliance & Data Portability
            Route::post('/compliance/customers/{id}/export-data', [PrivacyComplianceController::class, 'exportData']);
            Route::post('/compliance/customers/{id}/forget-me', [PrivacyComplianceController::class, 'forgetMe']);
            Route::get('/compliance/audit-retention-policy', [PrivacyComplianceController::class, 'policy']);

            // Webhook Subscription Management (Events: LOW_STOCK_ALERT, PO_RECEIVED, SHIFT_DISCREPANCY, REFUND_REQUESTED, STOCK_TRANSFER_COMPLETED)
            Route::get('/webhooks', [WebhookSubscriptionController::class, 'index']);
            Route::post('/webhooks/subscribe', [WebhookSubscriptionController::class, 'subscribe']);
            Route::post('/webhooks/test', [WebhookSubscriptionController::class, 'test']);
            Route::delete('/webhooks/{id}', [WebhookSubscriptionController::class, 'destroy']);
        });

        // =====================================================================
        // TIER 4 — ADMIN (role: ADMIN only)
        // =====================================================================
        Route::middleware('role:ADMIN')->group(function () {

            // Employee Management — full CRUD
            Route::get('/employees', [EmployeeController::class,      'index']);
            Route::get('/employees/{id}', [EmployeeController::class,      'show']);
            Route::post('/employees', [EmployeeController::class,      'store']);
            Route::put('/employees/{id}', [EmployeeController::class,      'update']);
            Route::delete('/employees/{id}', [EmployeeController::class,      'destroy']);

            // User Account Management
            Route::prefix('auth')->group(function () {
                Route::post('/register', [AuthController::class, 'register']);
            });

            // Admin Master Tracking Pulse, APM Performance & API Traffic Analytics
            Route::get('/admin/master-pulse', [AdminMasterController::class, 'masterPulse']);
            Route::get('/admin/performance', [AdminPerformanceController::class, 'performance']);
            Route::get('/admin/api-analytics', [AdminAnalyticsController::class, 'analytics']);
            Route::post('/admin/broadcast-alert', [AdminMasterController::class, 'broadcastAlert']);
        });
    });
});
