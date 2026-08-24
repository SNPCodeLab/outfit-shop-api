<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CloudinaryMediaController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\V1\AdminMonitoringController;
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

Route::prefix('v1')->group(function () {

    // =========================================================================
    // ── 1. PUBLIC TIER (Unauthenticated) ──
    // =========================================================================
    Route::get('/health', [StatusController::class, 'index']);
    Route::get('/status', [StatusController::class, 'index'])->middleware('deprecated:2027-12-31');
    Route::get('/guide', [HelpCentreGuideController::class, 'index']);
    Route::get('/docs', [HelpCentreGuideController::class, 'index'])->middleware('deprecated:2027-12-31');

    Route::middleware('throttle:role-based')->group(function () {
        // Multi-Currency
        Route::get('/currencies/rates', [CurrencyController::class, 'rates']);
        Route::post('/currencies/convert', [CurrencyController::class, 'convert']);

        // Auth (Public)
        Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
            Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('cambodia.only');
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
            Route::post('/reset-password', [AuthController::class, 'resetPassword']);
        });

        // Catalog (Public Read)
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::get('/categories/{id}', [CategoryController::class, 'show']);
        Route::get('/clothing-sizes', [ClothingSizeController::class, 'index']);
        Route::get('/clothing-sizes/{id}', [ClothingSizeController::class, 'show']);
        Route::get('/colors', [ColorController::class, 'index']);
        Route::get('/colors/{id}', [ColorController::class, 'show']);

        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{id}', [ProductController::class, 'show']);
        Route::get('/products/{id}/images', [ProductImageController::class, 'index']);
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

        // Cart & Wishlist (Guest/Public)
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/items', [CartController::class, 'addItem']);
        Route::match(['put', 'patch'], '/cart/items/{id}', [CartController::class, 'updateItem']);
        Route::delete('/cart/items/{id}', [CartController::class, 'removeItem']);
        Route::delete('/cart', [CartController::class, 'clear']);
        Route::delete('/cart/clear', [CartController::class, 'clear'])->middleware('deprecated:2027-12-31');

        Route::get('/wishlist', [CustomerWishlistController::class, 'index']);
        Route::post('/wishlist', [CustomerWishlistController::class, 'store']);
        Route::post('/wishlist/toggle', [CustomerWishlistController::class, 'toggle']);
        Route::delete('/wishlist/{id}', [CustomerWishlistController::class, 'destroy']);

        // Variants (Public)
        Route::get('/variants', [ProductVariantController::class, 'index']);
        Route::get('/variants/barcode/{barcode}', [ProductVariantController::class, 'lookupBarcode']);
        Route::get('/variants/{id}', [ProductVariantController::class, 'show'])->whereNumber('id');
        Route::get('/variants/{id}/tiers', [VariantPricingTierController::class, 'index']);
        Route::get('/variants/{id}/barcode-label', [BarcodePrintController::class, 'barcodeLabel']);

        // Payments & CMS (Public)
        Route::get('/payments/khqr', [KhqrPaymentController::class, 'generateCustom']);
        Route::post('/payments/khqr', [KhqrPaymentController::class, 'generateCustom']);
        Route::get('/gift-cards/{code}', [GiftCardController::class, 'check']);
        Route::post('/gift-cards/check', [GiftCardController::class, 'check'])->middleware('deprecated:2027-12-31');

        Route::get('/marketing/banners', [MarketingBannerController::class, 'index']);
        Route::get('/settings/audio-cues', [SystemSettingController::class, 'audioCues']);

        // Cloudinary Media Browse
        Route::prefix('cloudinary')->group(function () {
            Route::get('/folders', [CloudinaryMediaController::class, 'getFolders']);
            Route::get('/assets', [CloudinaryMediaController::class, 'getAssets']);
        });
    });

    // =========================================================================
    // ── 2. AUTHENTICATED TIER (STAFF, CASHIER, MANAGER, ADMIN) ──
    // =========================================================================
    Route::middleware(['auth:sanctum', 'throttle:role-based'])->group(function () {

        // Session
        Route::prefix('auth')->group(function () {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/avatar', [AuthController::class, 'uploadAvatar']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::post('/revoke-all', [AuthController::class, 'revokeAll']);
            Route::post('/2fa/setup', [AuthController::class, 'setup2FA']);
            Route::post('/2fa/verify', [AuthController::class, 'verify2FA']);
        });

        // Dashboard & Alerts
        Route::get('/dashboard/role-pulse', [DashboardController::class, 'rolePulse']);
        Route::get('/alerts/active', [DashboardController::class, 'activeAlerts']);

        // Business Intel (Auth required)
        Route::get('/inventory/statistics', [InventoryValuationController::class, 'statistics']);
        Route::get('/variants/low-stock', [ProductVariantController::class, 'lowStock']);

        // Postman (Manager/Admin only)
        Route::get('/postman.json', function () {
            $path = base_path('postman/OutfitShop_Master_Collection.json');

            return response()->file($path, ['Content-Type' => 'application/json']);
        })->middleware('role:MANAGER,ADMIN');

        // -- CASHIER, MANAGER, ADMIN --
        Route::middleware(['role:CASHIER,MANAGER,ADMIN'])->group(function () {
            // Customer CRM
            Route::get('/customers', [CustomerController::class, 'index']);
            Route::get('/customers/{id}', [CustomerController::class, 'show']);
            Route::post('/customers', [CustomerController::class, 'store']);
            Route::match(['put', 'patch'], '/customers/{id}', [CustomerController::class, 'update']);
            Route::get('/customers/{id}/loyalty', [CustomerLoyaltyController::class, 'show']);
            Route::post('/customers/{id}/redeem-points', [CustomerLoyaltyController::class, 'redeem']);

            // POS Operations
            Route::get('/shifts/current', [PosShiftController::class, 'current']);
            Route::post('/shifts/open', [PosShiftController::class, 'open']);
            Route::post('/shifts/drop-cash', [PosShiftController::class, 'dropCash']);
            Route::post('/shifts/close', [PosShiftController::class, 'close']);

            Route::post('/orders', [OrderController::class, 'checkout'])->middleware('ability:sales.checkout');
            Route::post('/orders/checkout', [OrderController::class, 'checkout'])->middleware('ability:sales.checkout');

            Route::get('/orders', [OrderController::class, 'index']);
            Route::get('/orders/{id}', [OrderController::class, 'show']);
            Route::get('/orders/{id}/khqr', [KhqrPaymentController::class, 'generateForSale']);
            Route::get('/orders/{id}/receipt-thermal', [BarcodePrintController::class, 'receiptThermal']);
            Route::get('/orders/{id}/invoice-pdf', [InvoiceEstimateController::class, 'renderInvoiceHtml']);

            // Billing
            Route::get('/invoices', [InvoiceEstimateController::class, 'index']);
            Route::post('/estimates', [InvoiceEstimateController::class, 'createEstimate']);
            Route::post('/estimates/{id}/convert', [InvoiceEstimateController::class, 'convertEstimateToInvoice']);
            Route::post('/gift-cards', [GiftCardController::class, 'issue']);

            // Shipping
            Route::get('/shipping-orders', [ShippingOrderController::class, 'index']);
            Route::post('/shipping-orders', [ShippingOrderController::class, 'create']);
            Route::patch('/shipping-orders/{id}', [ShippingOrderController::class, 'updateStatus']);

            // Offline Sync
            Route::get('/offline/manifest', [OfflineSyncController::class, 'manifest']);
            Route::post('/offline/push-transactions', [OfflineSyncController::class, 'pushTransactions']);

            // Review writes
            Route::post('/products/{id}/reviews', [ProductReviewController::class, 'store']);
            Route::get('/products/{id}/download', [DigitalAssetController::class, 'download']);
        });

        // =====================================================================
        // ── 3. MANAGER TIER (MANAGER, ADMIN) ──
        // =====================================================================
        Route::middleware('role:MANAGER,ADMIN')->group(function () {
            // Customer Delete
            Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);

            // Catalog Write
            Route::post('/products', [ProductController::class, 'store']);
            Route::match(['put', 'patch'], '/products/{id}', [ProductController::class, 'update']);
            Route::delete('/products/{id}', [ProductController::class, 'destroy']);
            Route::post('/products/{id}/images', [ProductImageController::class, 'store']);
            Route::delete('/products/{id}/images/{imageId}', [ProductImageController::class, 'destroy']);

            Route::post('/categories', [CategoryController::class, 'store']);
            Route::match(['put', 'patch'], '/categories/{id}', [CategoryController::class, 'update']);
            Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

            Route::post('/brands', [BrandController::class, 'store']);
            Route::match(['put', 'patch'], '/brands/{id}', [BrandController::class, 'update']);
            Route::delete('/brands/{id}', [BrandController::class, 'destroy']);

            Route::post('/clothing-sizes', [ClothingSizeController::class, 'store']);
            Route::match(['put', 'patch'], '/clothing-sizes/{id}', [ClothingSizeController::class, 'update']);
            Route::delete('/clothing-sizes/{id}', [ClothingSizeController::class, 'destroy']);

            Route::post('/colors', [ColorController::class, 'store']);
            Route::match(['put', 'patch'], '/colors/{id}', [ColorController::class, 'update']);
            Route::delete('/colors/{id}', [ColorController::class, 'destroy']);

            Route::post('/variants', [ProductVariantController::class, 'store']);
            Route::match(['put', 'patch'], '/variants/{id}', [ProductVariantController::class, 'update']);
            Route::delete('/variants/{id}', [ProductVariantController::class, 'destroy']);
            Route::post('/variants/{id}/tiers', [VariantPricingTierController::class, 'store']);

            // Bundles & Promotions
            Route::post('/bundles', [ProductBundleController::class, 'store']);
            Route::match(['put', 'patch'], '/bundles/{id}', [ProductBundleController::class, 'update']);
            Route::delete('/bundles/{id}', [ProductBundleController::class, 'destroy']);

            Route::get('/promotions', [PromotionController::class, 'index']);
            Route::get('/promotions/{id}', [PromotionController::class, 'show']);
            Route::post('/promotions', [PromotionController::class, 'store']);
            Route::match(['put', 'patch'], '/promotions/{id}', [PromotionController::class, 'update']);
            Route::delete('/promotions/{id}', [PromotionController::class, 'destroy']);

            // Inventory & Procurement
            Route::apiResource('suppliers', SupplierController::class);
            Route::get('/purchases', [PurchaseController::class, 'index']);
            Route::get('/purchases/{id}', [PurchaseController::class, 'show']);
            Route::post('/purchases', [PurchaseController::class, 'store']);
            Route::match(['put', 'patch'], '/purchases/{id}', [PurchaseController::class, 'update'])->whereNumber('id');
            Route::delete('/purchases/{id}', [PurchaseController::class, 'destroy'])->whereNumber('id');
            Route::post('/purchases/{id}/receive', [PurchaseController::class, 'receive']);
            Route::post('/purchases/auto-generate', [InventoryForecastingController::class, 'autoGeneratePurchaseOrder']);

            // Stock
            Route::get('/stock-movements', [StockMovementController::class, 'index']);
            Route::post('/stock-movements/adjust', [StockMovementController::class, 'adjust']);
            Route::post('/inventory/stock-opname', [StockMovementController::class, 'stockOpname']);
            Route::post('/inventory/bulk-adjust', [BulkOperationController::class, 'bulkAdjust']);

            Route::get('/stock-transfers', [StockTransferController::class, 'index']);
            Route::get('/stock-transfers/{id}', [StockTransferController::class, 'show'])->whereNumber('id');
            Route::post('/stock-transfers', [StockTransferController::class, 'store']);
            Route::post('/stock-transfers/{id}/approve', [StockTransferController::class, 'approve'])->whereNumber('id');
            Route::post('/stock-transfers/{id}/pick', [StockTransferController::class, 'pick'])->whereNumber('id');
            Route::post('/stock-transfers/{id}/ship', [StockTransferController::class, 'ship'])->whereNumber('id');
            Route::post('/stock-transfers/{id}/receive', [StockTransferController::class, 'receive'])->whereNumber('id');
            Route::post('/stock-transfers/{id}/cancel', [StockTransferController::class, 'cancel'])->whereNumber('id');
            Route::delete('/stock-transfers/{id}', [StockTransferController::class, 'destroy'])->whereNumber('id');

            Route::get('/inventory-batches', [InventoryBatchController::class, 'index']);
            Route::post('/inventory-batches', [InventoryBatchController::class, 'store']);
            Route::get('/inventory/expiring-soon', [InventoryBatchController::class, 'expiringSoon']);
            Route::get('/variants/{id}/batches', [InventoryBatchController::class, 'listBatches']);
            Route::post('/variants/{id}/batches', [InventoryBatchController::class, 'storeBatch']);

            // Void
            Route::post('/orders/{id}/void', [OrderController::class, 'voidOrder'])->middleware('ability:sales.void');

            // Reports & AI
            Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
            Route::get('/inventory/restock-recommendations', [InventoryForecastingController::class, 'restockRecommendations']);
            Route::get('/reports/sales', [ReportController::class, 'sales']);
            Route::get('/reports/sales-performance', [ReportController::class, 'salesPerformance']);
            Route::get('/reports/inventory-valuation', [ReportController::class, 'inventoryValuation']);
            Route::get('/reports/stock-aging', [ReportController::class, 'stockAging']);
            Route::get('/reports/profit-margin', [ReportController::class, 'profitMargin']);
            Route::get('/reports/cash-flow', [ReportController::class, 'cashFlow']);
            Route::get('/reports/supplier-performance', [ReportController::class, 'supplierPerformance']);

            Route::get('/ai/sales-forecast', [AiIntelligenceController::class, 'salesForecast']);
            Route::get('/ai/anomaly-detection', [AiIntelligenceController::class, 'anomalyDetection']);
            Route::get('/ai/smart-restock', [AiIntelligenceController::class, 'smartRestock']);
            Route::get('/ai/customer-segmentation', [AiIntelligenceController::class, 'customerSegmentation']);
            Route::get('/ai/dynamic-pricing', [AiIntelligenceController::class, 'dynamicPricing']);

            // Exports
            Route::get('/exports/inventory/excel', [FileExportController::class, 'exportInventory']);
            Route::get('/exports/stock-movements/csv', [FileExportController::class, 'exportStockMovements']);
            Route::get('/exports/sales-report/pdf', [FileExportController::class, 'exportSalesReport']);
            Route::get('/exports/z-report/{id}/thermal', [FileExportController::class, 'exportZReportThermal']);

            // Media
            Route::get('/uploads/gallery', [ImageUploadController::class, 'gallery']);
            Route::post('/uploads/image', [ImageUploadController::class, 'upload']);
            Route::post('/uploads/batch', [ImageUploadController::class, 'uploadBatch']);
            Route::delete('/uploads/image/{publicId}', [ImageUploadController::class, 'destroyByPublicId']);
            Route::post('/products/{id}/image', [ImageUploadController::class, 'uploadForProduct']);
            Route::post('/variants/{id}/image', [ImageUploadController::class, 'uploadForVariant']);

            // Branches
            Route::get('/branches/{id}', [StoreBranchController::class, 'show'])->whereNumber('id');
            Route::get('/branches/{id}/stock', [StoreBranchController::class, 'branchStock']);
            Route::post('/branches', [StoreBranchController::class, 'store']);
            Route::match(['put', 'patch'], '/branches/{id}', [StoreBranchController::class, 'update'])->whereNumber('id');
            Route::delete('/branches/{id}', [StoreBranchController::class, 'destroy'])->whereNumber('id');

            // Compliance
            Route::post('/customers/{id}/data-exports', [PrivacyComplianceController::class, 'exportData']);
            Route::post('/customers/{id}/erasure-requests', [PrivacyComplianceController::class, 'forgetMe']);
            Route::get('/compliance/audit-retention-policy', [PrivacyComplianceController::class, 'policy']);
            Route::get('/audit-logs', [AuditLogController::class, 'index']);
            Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);
        });

        // =====================================================================
        // ── 4. ADMIN TIER (ADMIN Only) ──
        // =====================================================================
        Route::middleware(['role:ADMIN', 'admin.ip'])->group(function () {
            Route::apiResource('employees', EmployeeController::class);
            Route::post('/auth/register', [AuthController::class, 'register']);
            Route::post('/auth/admin-reset-password', [AuthController::class, 'adminResetPassword']);

            Route::get('/admin/master-pulse', [AdminMonitoringController::class, 'masterPulse']);
            Route::get('/admin/performance', [AdminMonitoringController::class, 'performance']);
            Route::get('/admin/api-analytics', [AdminMonitoringController::class, 'apiAnalytics']);
            Route::post('/admin/broadcast-alert', [AdminMonitoringController::class, 'broadcastAlert']);

            Route::get('/webhooks', [WebhookSubscriptionController::class, 'index']);
            Route::post('/webhooks/subscribe', [WebhookSubscriptionController::class, 'subscribe']);
            Route::post('/webhooks/test', [WebhookSubscriptionController::class, 'test']);
            Route::delete('/webhooks/{id}', [WebhookSubscriptionController::class, 'destroy']);
        });
    });

    // ── 5. LEGACY ALIASES (Deprecated) ──
    Route::middleware(['auth:sanctum', 'deprecated:2027-12-31'])->group(function () {
        Route::post('/sales/checkout', [OrderController::class, 'checkout']);
        Route::get('/sales', [OrderController::class, 'index']);
        Route::get('/sales/{id}', [OrderController::class, 'show']);
        Route::post('/sales/{id}/void', [OrderController::class, 'voidOrder']);
        Route::get('/sales/{id}/khqr', [KhqrPaymentController::class, 'generateForSale']);
        Route::get('/sales/{id}/receipt-thermal', [BarcodePrintController::class, 'receiptThermal']);
        Route::get('/sales/{id}/invoice-pdf', [InvoiceEstimateController::class, 'renderInvoiceHtml']);
        Route::post('/gift-cards/issue', [GiftCardController::class, 'issue']);
        Route::delete('/cart/clear', [CartController::class, 'clear']);
        Route::delete('/wishlist/{id}', [CustomerWishlistController::class, 'destroy']);
        Route::post('/gift-cards/check', [GiftCardController::class, 'check']);

        Route::get('/shipping/orders', [ShippingOrderController::class, 'index']);
        Route::post('/shipping/create', [ShippingOrderController::class, 'create']);
        Route::post('/shipping/{id}/status', [ShippingOrderController::class, 'updateStatus']);
    });
});
