<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CloudinaryMediaController;
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
    Route::get('/health', [StatusController::class, 'index'])->name('health');
    Route::get('/status', [StatusController::class, 'index'])->middleware('deprecated:2027-12-31');
    Route::get('/guide', [HelpCentreGuideController::class, 'index'])->name('guide');
    Route::get('/docs', [HelpCentreGuideController::class, 'index'])->middleware('deprecated:2027-12-31');
    // The collection JSON embeds working login credentials for testing - it
    // must never be served publicly. Managers/Admins fetch it after login.
    Route::get('/postman.json', function () {
        $path = base_path('postman/OutfitShop_Master_Collection.json');

        return response()->file($path, ['Content-Type' => 'application/json']);
    })->middleware(['auth:sanctum', 'role:MANAGER,ADMIN', 'throttle:role-based']);

    // -------------------------------------------------------------------------
    // PUBLIC CONTENT (throttled via the guest branch of the role-based
    // limiter: 30 requests/min per IP). The health/docs endpoints above stay
    // unthrottled so uptime monitors are never blocked.
    // -------------------------------------------------------------------------
    Route::middleware('throttle:role-based')->group(function () {

        // Multi-Currency (USD Primary + KHR Secondary with NBC Official Benchmarks)
        Route::get('/currencies/rates', [CurrencyController::class, 'rates']);
        Route::post('/currencies/convert', [CurrencyController::class, 'convert']);

        // Authentication — rate-limited to prevent brute-force (5 attempts / min)
        // cambodia.only: login is restricted to Cambodian IP space only.
        Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
            Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('cambodia.only');
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
            Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');
        });

        // Public Product Catalog — read-only, no token needed
        Route::get('/categories', [CategoryController::class,      'index']);
        Route::get('/categories/{id}', [CategoryController::class,      'show']);

        Route::get('/clothing-sizes', [ClothingSizeController::class,  'index']);
        Route::get('/clothing-sizes/{id}', [ClothingSizeController::class,  'show']);

        Route::get('/colors', [ColorController::class,         'index']);
        Route::get('/colors/{id}', [ColorController::class,         'show']);

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/products/{id}/images', [ProductImageController::class, 'index'])->name('products.images.index');
        Route::get('/products/{id}/matrix', [ProductMatrixController::class, 'matrix']);
        Route::get('/products/{id}/colorways', [ProductMatrixController::class, 'colorways']);
        Route::get('/products/{id}/reviews', [ProductReviewController::class, 'index']);

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
        Route::match(['put', 'patch'], '/cart/items/{id}', [CartController::class, 'updateItem']);
        Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
        // RESTful collection delete is canonical; /cart/clear deprecated alias
        Route::delete('/cart', [CartController::class, 'clear']);
        Route::delete('/cart/clear', [CartController::class, 'clear'])->middleware('deprecated:2027-12-31');

        // Customer Wishlist Endpoints
        Route::get('/wishlist', [CustomerWishlistController::class, 'index']);
        Route::post('/wishlist', [CustomerWishlistController::class, 'store']);
        Route::post('/wishlist/toggle', [CustomerWishlistController::class, 'toggle']);
        Route::delete('/wishlist/{id}', [CustomerWishlistController::class, 'destroy']);

        Route::get('/variants', [ProductVariantController::class, 'index']);
        Route::get('/variants/barcode/{barcode}', [ProductVariantController::class, 'lookupBarcode']);
        Route::get('/variants/{id}', [ProductVariantController::class, 'show'])->whereNumber('id');
        Route::get('/variants/{id}/tiers', [VariantPricingTierController::class, 'index']);
        Route::get('/variants/{id}/barcode-label', [BarcodePrintController::class, 'barcodeLabel']);

        // Payments & KHQR (POST is the correct verb for an operation accepting
        // input and generating a payload; GET kept as a legacy alias)
        Route::get('/payments/khqr', [KhqrPaymentController::class, 'generateCustom'])->middleware('deprecated:2027-12-31');
        Route::post('/payments/khqr', [KhqrPaymentController::class, 'generateCustom']);

        // Gift card balance check (RESTful read: GET /gift-cards/{code};
        // POST /gift-cards/check kept as a legacy alias)
        Route::get('/gift-cards/{code}', [GiftCardController::class, 'check']);
        Route::post('/gift-cards/check', [GiftCardController::class, 'check'])->middleware('deprecated:2027-12-31');

        // Storefront CMS Banners & System Audio Settings
        Route::get('/marketing/banners', [MarketingBannerController::class, 'index']);
        Route::get('/settings/audio-cues', [SystemSettingController::class, 'audioCues']);

        // Cloudinary Media Browse & Search API (24 Folders & 1,843 Assets Proxy)
        Route::prefix('cloudinary')->group(function () {
            Route::get('/folders', [CloudinaryMediaController::class, 'getFolders']);
            Route::get('/assets', [CloudinaryMediaController::class, 'getAssets']);
        });

    }); // end throttled public content group

    // =========================================================================
    // TIER 2 — AUTHENTICATED (CASHIER, STAFF, MANAGER, ADMIN)
    // =========================================================================
    Route::middleware(['auth:sanctum', 'throttle:role-based'])->group(function () {

        // -- Session Management & Token Rotation --
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me'])->name('auth.me');
            Route::post('/avatar', [AuthController::class, 'uploadAvatar'])->name('auth.avatar');
            Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('auth.refresh');
            Route::post('/revoke-all', [AuthController::class, 'revokeAll'])->name('auth.revoke-all');
            Route::post('/2fa/setup', [AuthController::class, 'setup2FA'])->name('auth.2fa.setup');
            Route::post('/2fa/verify', [AuthController::class, 'verify2FA'])->name('auth.2fa.verify');
        });

        // -- Customer Management & Loyalty Points --
        // Writes require CASHIER or above: the STAFF permission map grants
        // customers.read only, and routes now enforce that server-side.
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::get('/customers/{id}', [CustomerController::class, 'show']);
        Route::middleware('role:CASHIER,MANAGER,ADMIN')->group(function () {
            Route::post('/customers', [CustomerController::class, 'store']);
            Route::match(['put', 'patch'], '/customers/{id}', [CustomerController::class, 'update']);
        });
        Route::get('/customers/{id}/loyalty', [CustomerLoyaltyController::class, 'show']);
        Route::post('/customers/{id}/redeem-points', [CustomerLoyaltyController::class, 'redeem']);

        // -- Inventory Business Intelligence (formerly public: cost/resale
        //    valuation and stock levels are competitive data, not storefront) --
        Route::get('/inventory/statistics', [InventoryValuationController::class, 'statistics']);
        Route::get('/variants/low-stock', [ProductVariantController::class, 'lowStock']);

        // -- POS Cash Register Shifts (Z-Report) --
        Route::get('/shifts/current', [PosShiftController::class, 'current']);
        Route::post('/shifts/open', [PosShiftController::class, 'open']);
        Route::post('/shifts/drop-cash', [PosShiftController::class, 'dropCash']);
        Route::post('/shifts/close', [PosShiftController::class, 'close']);

        // -- Orders & Checkouts (First-class endpoints) --
        // Token-ability enforcement (defense in depth on top of role checks):
        // the token itself must carry sales.checkout / sales.void.
        Route::post('/orders/checkout', [OrderController::class, 'checkout'])->middleware('ability:sales.checkout')->name('orders.checkout');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');

        // -- Legacy POS Sales Aliases (deprecated; sunset 2027-12-31) --
        Route::middleware('deprecated:2027-12-31')->group(function () {
            Route::post('/sales/checkout', [OrderController::class, 'checkout'])->middleware('ability:sales.checkout');
            Route::get('/sales', [OrderController::class, 'index']);
            Route::get('/sales/{id}', [OrderController::class, 'show']);
        });

        // -- Receipts, Invoices & KHQR (authenticated: customer PII protection;
        //    previously public and reachable by sequential ID) --
        Route::get('/orders/{id}/khqr', [KhqrPaymentController::class, 'generateForSale']);
        Route::get('/orders/{id}/receipt-thermal', [BarcodePrintController::class, 'receiptThermal']);
        Route::get('/orders/{id}/invoice-pdf', [InvoiceEstimateController::class, 'renderInvoiceHtml']);
        Route::get('/sales/{id}/khqr', [KhqrPaymentController::class, 'generateForSale']);
        Route::get('/sales/{id}/receipt-thermal', [BarcodePrintController::class, 'receiptThermal']);
        Route::get('/sales/{id}/invoice-pdf', [InvoiceEstimateController::class, 'renderInvoiceHtml']);

        // -- Digital Asset Downloads (authenticated + purchase-verified) --
        Route::get('/products/{id}/download', [DigitalAssetController::class, 'download']);

        // -- Product Review writes (authenticated; reads stay public) --
        Route::post('/products/{id}/reviews', [ProductReviewController::class, 'store']);

        Route::get('/invoices', [InvoiceEstimateController::class, 'index']);
        Route::post('/estimates', [InvoiceEstimateController::class, 'createEstimate']);
        Route::post('/estimates/{id}/convert', [InvoiceEstimateController::class, 'convertEstimateToInvoice']);
        // RESTful create is canonical; /gift-cards/issue deprecated alias
        Route::post('/gift-cards', [GiftCardController::class, 'issue']);
        Route::post('/gift-cards/issue', [GiftCardController::class, 'issue'])->middleware('deprecated:2027-12-31');

        // -- Omnichannel Shipping & Click-and-Collect --
        // RESTful resource names are canonical; /shipping/* aliases are
        // deprecated (sunset 2027-12-31) and kept for backward compatibility.
        Route::middleware('deprecated:2027-12-31')->group(function () {
            Route::get('/shipping/orders', [ShippingOrderController::class, 'index']);
            Route::post('/shipping/create', [ShippingOrderController::class, 'create']);
            Route::post('/shipping/{id}/status', [ShippingOrderController::class, 'updateStatus']);
        });

        Route::get('/shipping-orders', [ShippingOrderController::class, 'index']);
        Route::post('/shipping-orders', [ShippingOrderController::class, 'create']);
        Route::patch('/shipping-orders/{id}', [ShippingOrderController::class, 'updateStatus']);

        // -- Offline POS Mode Synchronization & Conflict Resolution --
        Route::get('/offline/manifest', [OfflineSyncController::class, 'manifest']);
        Route::post('/offline/push-transactions', [OfflineSyncController::class, 'pushTransactions']);

        // -- Live Role-Pulse Analytics (Pie & Agile Graph tracking per role) --
        Route::get('/dashboard/role-pulse', [DashboardController::class, 'rolePulse'])->name('dashboard.role-pulse');

        // Active Broadcast Alerts feed for all logged-in staff
        Route::get('/alerts/active', [DashboardController::class, 'activeAlerts'])->name('alerts.active');

        // =====================================================================
        // TIER 3 — MANAGER (role: MANAGER or ADMIN)
        // =====================================================================
        Route::middleware('role:MANAGER,ADMIN')->group(function () {

            // Analytics, Dashboard & Restock Forecasting
            Route::get('/dashboard/stats', [DashboardController::class,     'stats']);
            Route::get('/inventory/restock-recommendations', [InventoryForecastingController::class, 'restockRecommendations']);
            Route::post('/purchases/auto-generate', [InventoryForecastingController::class, 'autoGeneratePurchaseOrder']);

            // Gift Card Management
            Route::get('/gift-cards', [GiftCardController::class, 'index']);

            // Catalog Write Access
            Route::post('/categories', [CategoryController::class,      'store']);
            Route::match(['put', 'patch'], '/categories/{id}', [CategoryController::class,      'update']);
            Route::delete('/categories/{id}', [CategoryController::class,      'destroy']);

            Route::post('/brands', [BrandController::class, 'store']);
            Route::match(['put', 'patch'], '/brands/{id}', [BrandController::class, 'update']);
            Route::delete('/brands/{id}', [BrandController::class, 'destroy']);

            Route::post('/clothing-sizes', [ClothingSizeController::class,  'store']);
            Route::match(['put', 'patch'], '/clothing-sizes/{id}', [ClothingSizeController::class,  'update']);
            Route::delete('/clothing-sizes/{id}', [ClothingSizeController::class,  'destroy']);

            Route::post('/colors', [ColorController::class,         'store']);
            Route::match(['put', 'patch'], '/colors/{id}', [ColorController::class,         'update']);
            Route::delete('/colors/{id}', [ColorController::class,         'destroy']);

            Route::post('/products', [ProductController::class,       'store']);
            Route::match(['put', 'patch'], '/products/{id}', [ProductController::class,       'update']);
            Route::delete('/products/{id}', [ProductController::class,       'destroy']);
            Route::post('/products/{id}/images', [ProductImageController::class, 'store']);
            Route::delete('/products/{id}/images/{imageId}', [ProductImageController::class, 'destroy']);

            Route::post('/variants', [ProductVariantController::class, 'store']);
            Route::match(['put', 'patch'], '/variants/{id}', [ProductVariantController::class, 'update']);
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
            Route::match(['put', 'patch'], '/suppliers/{id}', [SupplierController::class,      'update']);
            Route::delete('/suppliers/{id}', [SupplierController::class,      'destroy']);

            Route::get('/purchases', [PurchaseController::class,      'index']);
            Route::get('/purchases/{id}', [PurchaseController::class,      'show']);
            Route::post('/purchases', [PurchaseController::class,      'store']);

            // Inventory Stock Ledger & Adjustments
            Route::get('/stock-movements', [StockMovementController::class, 'index']);
            Route::post('/stock-movements/adjust', [StockMovementController::class, 'adjust']);
            Route::post('/inventory/stock-opname', [StockMovementController::class, 'stockOpname']);

            // Cloudinary Image Media Management
            Route::get('/uploads/gallery', [ImageUploadController::class,   'gallery']);
            Route::post('/uploads/image', [ImageUploadController::class,   'upload']);
            Route::post('/uploads/batch', [ImageUploadController::class,   'uploadBatch']);
            // Path-parameterized delete is canonical (rule N6); the
            // query/body-parameter delete remains as a deprecated alias.
            Route::delete('/uploads/image/{publicId}', [ImageUploadController::class, 'destroyByPublicId']);
            Route::delete('/uploads/image', [ImageUploadController::class,   'destroy'])->middleware('deprecated:2027-12-31');
            Route::post('/products/{id}/image', [ImageUploadController::class,   'uploadForProduct']);
            Route::post('/variants/{id}/image', [ImageUploadController::class,   'uploadForVariant']);

            // Orders & Sales Voiding (role-gated; token-ability enforcement
            // kept on checkout only - see M11 note in audit report)
            Route::post('/orders/{id}/void', [OrderController::class, 'void']);
            Route::post('/sales/{id}/void', [OrderController::class, 'void']);

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
            Route::get('/stock-transfers/{id}', [StockTransferController::class, 'show'])->whereNumber('id');
            Route::post('/stock-transfers', [StockTransferController::class, 'store']);
            Route::post('/stock-transfers/{id}/approve', [StockTransferController::class, 'approve'])->whereNumber('id');
            Route::post('/stock-transfers/{id}/pick', [StockTransferController::class, 'pick'])->whereNumber('id');
            Route::post('/stock-transfers/{id}/ship', [StockTransferController::class, 'ship'])->whereNumber('id');
            Route::post('/stock-transfers/{id}/receive', [StockTransferController::class, 'receive'])->whereNumber('id');
            Route::post('/stock-transfers/{id}/cancel', [StockTransferController::class, 'cancel'])->whereNumber('id');

            // Advanced MIS Financial & Operational Reports
            Route::get('/reports/sales', [ReportController::class, 'sales']);
            Route::get('/reports/sales-performance', [ReportController::class, 'salesPerformance']);
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
            // Two-level resource paths are canonical (rule N4); the deep
            // /compliance/* paths are deprecated aliases.
            Route::post('/customers/{id}/data-exports', [PrivacyComplianceController::class, 'exportData']);
            Route::post('/customers/{id}/erasure-requests', [PrivacyComplianceController::class, 'forgetMe']);
            Route::middleware('deprecated:2027-12-31')->group(function () {
                Route::post('/compliance/customers/{id}/export-data', [PrivacyComplianceController::class, 'exportData']);
                Route::post('/compliance/customers/{id}/forget-me', [PrivacyComplianceController::class, 'forgetMe']);
            });
            Route::get('/compliance/audit-retention-policy', [PrivacyComplianceController::class, 'policy']);

            // Webhook Subscription Management (Events: LOW_STOCK_ALERT, PO_RECEIVED, SHIFT_DISCREPANCY, REFUND_REQUESTED, STOCK_TRANSFER_COMPLETED)
            Route::get('/webhooks', [WebhookSubscriptionController::class, 'index']);
            Route::post('/webhooks/subscribe', [WebhookSubscriptionController::class, 'subscribe']);
            Route::post('/webhooks/test', [WebhookSubscriptionController::class, 'test']);
            Route::delete('/webhooks/{id}', [WebhookSubscriptionController::class, 'destroy']);
        });

        // =====================================================================
        // TIER 4 — ADMIN (role: ADMIN only). Optional IP allow-list
        // (ADMIN_IP_WHITELIST env) adds defense in depth once configured;
        // it is disabled while the variable is unset.
        // =====================================================================
        Route::middleware(['role:ADMIN', 'admin.ip'])->group(function () {

            // Employee Management — full CRUD
            Route::get('/employees', [EmployeeController::class,      'index']);
            Route::get('/employees/{id}', [EmployeeController::class,      'show']);
            Route::post('/employees', [EmployeeController::class,      'store']);
            Route::match(['put', 'patch'], '/employees/{id}', [EmployeeController::class,      'update']);
            Route::delete('/employees/{id}', [EmployeeController::class,      'destroy']);

            // User Account Management
            Route::prefix('auth')->group(function () {
                Route::post('/register', [AuthController::class, 'register']);
                Route::post('/admin-reset-password', [AuthController::class, 'adminResetPassword']);
            });

            // Admin Master Tracking Pulse, APM Performance & API Traffic Analytics
            Route::get('/admin/master-pulse', [AdminMasterController::class, 'masterPulse']);
            Route::get('/admin/performance', [AdminPerformanceController::class, 'performance']);
            Route::get('/admin/api-analytics', [AdminAnalyticsController::class, 'analytics']);
            Route::post('/admin/broadcast-alert', [AdminMasterController::class, 'broadcastAlert']);
        });
    });
});
