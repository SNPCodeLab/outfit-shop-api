<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use Illuminate\Contracts\Console\Kernel;

function makeRequestItem($name, $method, $path, $folder, $authType = 'bearer', $bodyJson = null, $desc = '')
{
    $urlParts = explode('/', ltrim($path, '/'));

    $req = [
        'name' => $name,
        'request' => [
            'method' => $method,
            'header' => [
                ['key' => 'Accept', 'value' => 'application/json', 'type' => 'text'],
                ['key' => 'Content-Type', 'value' => 'application/json', 'type' => 'text'],
            ],
            'url' => [
                'raw' => '{{base_url}}/'.ltrim($path, '/'),
                'host' => ['{{base_url}}'],
                'path' => $urlParts,
            ],
            'description' => $desc,
        ],
        'response' => [],
    ];

    if ($authType === 'bearer') {
        $req['request']['auth'] = [
            'type' => 'bearer',
            'bearer' => [
                ['key' => 'token', 'value' => '{{token}}', 'type' => 'string'],
            ],
        ];
    }

    if ($bodyJson && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        $req['request']['body'] = [
            'mode' => 'raw',
            'raw' => is_array($bodyJson) ? json_encode($bodyJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $bodyJson,
            'options' => [
                'raw' => [
                    'language' => 'json',
                ],
            ],
        ];
    }

    return $req;
}

// ─────────────────────────────────────────────────────────────────────────────
// LEVEL 1: PUBLIC / STOREFRONT (No Auth Required)
// ─────────────────────────────────────────────────────────────────────────────
$publicItems = [
    // Health & System
    makeRequestItem('1.1 Health & Engine Status', 'GET', 'api/v1/health', 'Public', 'none', null, 'System heartbeat and database connectivity check'),
    makeRequestItem('1.2 Engine Status Metadata', 'GET', 'api/v1/status', 'Public', 'none', null, 'Detailed engine statistics, runtime, and version information'),

    // Auth & Login
    makeRequestItem('1.3 Employee Login (Get Sanctum Token)', 'POST', 'api/v1/auth/login', 'Public', 'none', [
        'username' => 'admin',
        'password' => 'Admin@123456',
    ], 'Authenticate and receive Bearer access token + assigned RBAC roles'),

    // Public Product Catalog
    makeRequestItem('1.4 List Active Products (Paginated & Filterable)', 'GET', 'api/v1/products?page=1&per_page=18', 'Public', 'none', null, 'Fetch omnichannel product catalog with primary images, department tags, and variant counts'),
    makeRequestItem('1.5 Get Single Product Details by ID', 'GET', 'api/v1/products/1', 'Public', 'none', null, 'Get full product details including all size/color variants and high-res Cloudinary images'),
    makeRequestItem('1.6 Get Product Colorways & Matrix', 'GET', 'api/v1/products/1/colorways', 'Public', 'none', null, 'Fetch available colorways with associated swatches and photo gallery'),
    makeRequestItem('1.7 Get Size x Color Stock Grid Matrix', 'GET', 'api/v1/products/1/matrix', 'Public', 'none', null, 'SalesBinder-style matrix inventory table showing live quantities per size/color intersection'),
    makeRequestItem('1.8 Get Product Reviews & Ratings', 'GET', 'api/v1/products/1/reviews', 'Public', 'none', null, 'List verified customer ratings and feedback'),
    makeRequestItem('1.9 Submit Customer Product Review', 'POST', 'api/v1/products/1/reviews', 'Public', 'none', [
        'customer_name' => 'Sokha Chan',
        'rating' => 5,
        'review_comment' => 'Exceptional silk fabric and perfect tailoring!',
    ], 'Submit a new product review (queued for moderation)'),
    makeRequestItem('1.10 Download Digital Product (eBook/Asset)', 'GET', 'api/v1/products/17/download', 'Public', 'none', null, 'Download purchased PDF/ePub digital assets'),

    // Master Attributes & Taxonomy
    makeRequestItem('1.11 List Master Categories & Departments', 'GET', 'api/v1/categories', 'Public', 'none', null, 'List all 15 omnichannel categories across fashion, fmcg, books, and beauty'),
    makeRequestItem('1.12 Get Category by ID', 'GET', 'api/v1/categories/1', 'Public', 'none', null, 'Category metadata and associated product counts'),
    makeRequestItem('1.13 List Clothing Sizes & Dimensions', 'GET', 'api/v1/clothing-sizes', 'Public', 'none', null, 'List all sizing codes (S, M, L, XL, EU37-39, Can, PDF)'),
    makeRequestItem('1.14 List Luxury Colors & Hex Tokens', 'GET', 'api/v1/colors', 'Public', 'none', null, 'List master color palette (Noir Black, Terracotta Clay, Midnight Navy, etc.)'),
    makeRequestItem('1.15 List Product Variants & SKUs', 'GET', 'api/v1/variants?page=1&per_page=20', 'Public', 'none', null, 'List variant-level SKUs, barcodes, and real-time inventory counts'),
    makeRequestItem('1.16 Lookup Variant by Barcode (EAN-13 / UPC)', 'GET', 'api/v1/variants/barcode/885000000101', 'Public', 'none', null, 'Instant barcode scanner lookup for POS checkouts'),
    makeRequestItem('1.17 Get Variant Wholesale Pricing Tiers', 'GET', 'api/v1/variants/1/tiers', 'Public', 'none', null, 'Fetch bulk volume discount rules (e.g. 5+ units: 10% off)'),

    // Storefront Marketing & Banners
    makeRequestItem('1.18 List Active Marketing Banners', 'GET', 'api/v1/marketing/banners', 'Public', 'none', null, 'Fetch promotional hero slides, carousel banners, and editorial campaign creatives'),
    makeRequestItem('1.19 List Active Promotions & Discount Codes', 'GET', 'api/v1/promotions/active', 'Public', 'none', null, 'Fetch active seasonal discounts and flash sales'),
    makeRequestItem('1.20 Verify Coupon / Promo Code', 'POST', 'api/v1/promotions/verify-coupon', 'Public', 'none', [
        'coupon_code' => 'KHMERNEWYEAR2026',
        'cart_subtotal' => 150.00,
    ], 'Validate coupon eligibility and calculate dynamic discount amount'),
    makeRequestItem('1.21 Check Gift Card Balance & Validity', 'POST', 'api/v1/gift-cards/check', 'Public', 'none', [
        'card_code' => 'KM-2026-8888-9999',
        'pin' => '1234',
    ], 'Check remaining balance and expiration of a 16-digit voucher card'),
    makeRequestItem('1.22 List Product Bundles & Package Deals', 'GET', 'api/v1/bundles', 'Public', 'none', null, 'Fetch curated gift sets and bundled outfits with combo pricing'),
    makeRequestItem('1.23 Generate Dynamic KHQR Bakong String', 'GET', 'api/v1/payments/khqr?amount=125.00&currency=USD', 'Public', 'none', null, 'Generate universal EMVCo compliant KHQR for mobile app payments'),
    makeRequestItem('1.24 Get Audio Cue Sound URLs (Beeps/Chimes)', 'GET', 'api/v1/settings/audio-cues', 'Public', 'none', null, 'Fetch UI audio feedback assets (barcode beep, payment chime, cash drawer pop)'),
];

// ─────────────────────────────────────────────────────────────────────────────
// LEVEL 2: CASHIER & POS OPERATOR (Role: CASHIER / STAFF)
// ─────────────────────────────────────────────────────────────────────────────
$cashierItems = [
    // POS Register & Shift Management
    makeRequestItem('2.1 Get Current POS Cash Drawer Shift', 'GET', 'api/v1/shifts/current', 'Cashier', 'bearer', null, 'Get active shift balance, total cash in drawer, and sales count'),
    makeRequestItem('2.2 Open New POS Register Shift', 'POST', 'api/v1/shifts/open', 'Cashier', 'bearer', [
        'store_id' => 1,
        'opening_cash' => 100.00,
        'notes' => 'Morning shift register opening.',
    ], 'Start shift with opening cash float in register'),
    makeRequestItem('2.3 Record Mid-Day Cash Drop (Safe Skim)', 'POST', 'api/v1/shifts/drop-cash', 'Cashier', 'bearer', [
        'amount' => 500.00,
        'reason' => 'Midday cash skim transferred to store safe.',
    ], 'Record cash drop from register drawer to vault'),
    makeRequestItem('2.4 Close Shift & Generate Z-Report', 'POST', 'api/v1/shifts/close', 'Cashier', 'bearer', [
        'actual_cash_counted' => 750.00,
        'closing_notes' => 'Shift balanced with zero cash discrepancy.',
    ], 'Close register, calculate cash over/short discrepancy, and lock Z-report'),

    // POS Checkout & Sales Transactions
    makeRequestItem('2.5 Complete Point-of-Sale Checkout', 'POST', 'api/v1/sales/checkout', 'Cashier', 'bearer', [
        'customer_id' => 1,
        'payment_method' => 'KHQR_BAKONG',
        'items' => [
            ['variant_id' => 1, 'quantity' => 1, 'unit_price' => 125.00, 'discount_amount' => 0.00],
            ['variant_id' => 10, 'quantity' => 1, 'unit_price' => 145.00, 'discount_amount' => 10.00],
        ],
        'discount_amount' => 10.00,
        'tax_amount' => 0.00,
        'notes' => 'In-store POS retail purchase.',
    ], 'ACID-compliant sales transaction deducting inventory and generating invoice receipt'),
    makeRequestItem('2.6 List Sales Invoices & Receipts', 'GET', 'api/v1/sales?page=1&per_page=15', 'Cashier', 'bearer', null, 'List transaction history with customer names and payment status'),
    makeRequestItem('2.7 Get Single Sale Invoice Details', 'GET', 'api/v1/sales/1', 'Cashier', 'bearer', null, 'Get breakdown of sold items, applied discounts, tax, and payments'),
    makeRequestItem('2.8 Generate KHQR Payment for Sale', 'GET', 'api/v1/sales/1/khqr', 'Cashier', 'bearer', null, 'Generate dynamic KHQR Bakong barcode for a specific pending invoice'),
    makeRequestItem('2.9 Generate 80mm ESC/POS Thermal Receipt', 'GET', 'api/v1/sales/1/receipt-thermal', 'Cashier', 'bearer', null, 'Render 80mm thermal receipt payload for receipt printers'),
    makeRequestItem('2.10 Void / Cancel Sale & Restock Inventory', 'POST', 'api/v1/sales/1/void', 'Cashier', 'bearer', [
        'reason' => 'Customer requested immediate exchange before leaving counter.',
    ], 'Void transaction and automatically return items to stock'),

    // Customer & Loyalty Management
    makeRequestItem('2.11 List Registered Customers', 'GET', 'api/v1/customers?page=1&per_page=20', 'Cashier', 'bearer', null, 'Search customer CRM database by phone, name, or email'),
    makeRequestItem('2.12 Register New Walk-In Customer', 'POST', 'api/v1/customers', 'Cashier', 'bearer', [
        'customer_name' => 'Chenda Pich',
        'phone' => '012999888',
        'email' => 'chenda.pich@example.com',
        'gender' => 'FEMALE',
        'address' => 'Street 2004, Phnom Penh',
    ], 'Register new customer for points accumulation and order tracking'),
    makeRequestItem('2.13 Get Customer Details & Purchase History', 'GET', 'api/v1/customers/1', 'Cashier', 'bearer', null, 'Fetch customer profile, total spend, and past orders'),
    makeRequestItem('2.14 Get Customer VIP Loyalty & Points Balance', 'GET', 'api/v1/customers/1/loyalty', 'Cashier', 'bearer', null, 'Check VIP Tier (Bronze/Silver/Gold/Platinum) and point balance'),
    makeRequestItem('2.15 Redeem Customer Loyalty Points for Voucher', 'POST', 'api/v1/customers/1/redeem-points', 'Cashier', 'bearer', [
        'points_to_redeem' => 100,
    ], 'Convert 100 points into a $5.00 discount voucher'),

    // Shipping & Delivery Booking
    makeRequestItem('2.16 Create Courier Delivery / Click-and-Collect Order', 'POST', 'api/v1/shipping/create', 'Cashier', 'bearer', [
        'sale_id' => 1,
        'courier_name' => 'VIRAK_BUNTHAM',
        'recipient_name' => 'Chenda Pich',
        'recipient_phone' => '012999888',
        'delivery_address' => 'Siem Reap Branch Office, Wat Bo Road',
        'shipping_fee' => 2.50,
    ], 'Dispatch order via Virak Buntham, J&T, Grab, or Store Pickup'),
    makeRequestItem('2.17 List Shipping & Courier Orders', 'GET', 'api/v1/shipping/orders', 'Cashier', 'bearer', null, 'Track status of dispatched packages'),
    makeRequestItem('2.18 Update Shipping Dispatch Status', 'POST', 'api/v1/shipping/1/status', 'Cashier', 'bearer', [
        'status' => 'IN_TRANSIT',
        'tracking_number' => 'VBT-88992211',
        'courier_notes' => 'Package handed over to driver.',
    ], 'Update courier tracking state (PENDING, DISPATCHED, IN_TRANSIT, DELIVERED)'),
];

// ─────────────────────────────────────────────────────────────────────────────
// LEVEL 3: INVENTORY & WAREHOUSE MANAGER (Role: MANAGER)
// ─────────────────────────────────────────────────────────────────────────────
$managerItems = [
    // Catalog CRUD Operations
    makeRequestItem('3.1 Create New Master Product', 'POST', 'api/v1/products', 'Manager', 'bearer', [
        'category_id' => 1,
        'brand_id' => 1,
        'product_name' => 'KhmeRiel Luxury Silk Wrap Dress',
        'product_type' => 'STANDARD_PHYSICAL',
        'description' => '100% premium mulberry silk wrap dress.',
        'image_url' => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905171/KHMERIEL_DRESSES_CRINOLINE_COLUMN_GOWN_TERRACOTTA_LOOK_cloth_096.png',
        'status' => 'ACTIVE',
    ], 'Create master product catalog entry'),
    makeRequestItem('3.2 Update Product Details', 'PUT', 'api/v1/products/1', 'Manager', 'bearer', [
        'product_name' => 'KhmeRiel Silk Evening Column Gown (Updated Edition)',
        'description' => 'Revised couture description.',
    ], 'Update product title, description, and tags'),
    makeRequestItem('3.3 Delete Product (Soft Delete)', 'DELETE', 'api/v1/products/1', 'Manager', 'bearer', null, 'Soft delete product from active catalog'),

    // Variant & SKU Management
    makeRequestItem('3.4 Create Product Variant (Size x Color SKU)', 'POST', 'api/v1/variants', 'Manager', 'bearer', [
        'product_id' => 1,
        'size_id' => 1,
        'color_id' => 1,
        'sku' => 'DRS-096-BLK-S',
        'barcode' => '885000000104',
        'cost_price' => 45.00,
        'sale_price' => 125.00,
        'quantity' => 20,
        'reorder_level' => 10,
    ], 'Create new SKU variant with cost price, retail price, and reorder trigger threshold'),
    makeRequestItem('3.5 Update Variant Price & Reorder Threshold', 'PUT', 'api/v1/variants/1', 'Manager', 'bearer', [
        'cost_price' => 42.00,
        'sale_price' => 129.00,
        'reorder_level' => 15,
    ], 'Adjust pricing and safety stock thresholds'),
    makeRequestItem('3.6 List Low Stock Alerts (Below Reorder Point)', 'GET', 'api/v1/variants/low-stock', 'Manager', 'bearer', null, 'Fetch all SKUs that have breached safety stock limits'),
    makeRequestItem('3.7 Generate Barcode Label (Printable PDF/SVG)', 'GET', 'api/v1/variants/1/barcode-label', 'Manager', 'bearer', null, 'Generate 50x30mm retail barcode sticker with price and SKU'),
    makeRequestItem('3.8 Create B2B Bulk Pricing Tier for SKU', 'POST', 'api/v1/variants/1/tiers', 'Manager', 'bearer', [
        'min_quantity' => 10,
        'tier_price' => 95.00,
        'tier_label' => 'B2B Wholesale Tier 1',
    ], 'Define wholesale volume discount pricing for retail partners'),

    // Cloudinary Media Uploads
    makeRequestItem('3.9 Direct Image Upload to Cloudinary', 'POST', 'api/v1/uploads/image', 'Manager', 'bearer', [
        'image' => '(binary file)',
        'folder' => 'khmeriel/products',
    ], 'Upload product photography to Cloudinary CDN'),
    makeRequestItem('3.10 Attach Image Gallery to Product', 'POST', 'api/v1/products/1/images', 'Manager', 'bearer', [
        'image_url' => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905145/KHMERIEL_TOPS_FLUID_DRAPED_SILK_BLOUSE_CREAM_BEIGE_LOOK_cloth_012.png',
        'image_public_id' => 'KHMERIEL_TOPS_FLUID_DRAPED_SILK_BLOUSE_CREAM_BEIGE_LOOK_cloth_012',
        'is_primary' => false,
        'sort_order' => 1,
    ], 'Add additional editorial photoshoot angle to gallery'),
    makeRequestItem('3.11 Browse Cloudinary Asset Gallery', 'GET', 'api/v1/uploads/gallery?folder=khmeriel/products', 'Manager', 'bearer', null, 'List all live CDN media assets in Cloudinary account'),

    // Inventory Restocking & Velocity PO Algorithm
    makeRequestItem('3.12 AI Inventory Restock Velocity Recommendations', 'GET', 'api/v1/inventory/restock-recommendations?days=14', 'Manager', 'bearer', null, 'Calculates 14-day sales velocity and suggests exact reorder quantities'),
    makeRequestItem('3.13 One-Click Auto-Generate Purchase Order (PO)', 'POST', 'api/v1/purchases/auto-generate', 'Manager', 'bearer', [
        'supplier_id' => 1,
        'velocity_days' => 14,
        'safety_stock_multiplier' => 1.5,
    ], 'Automatically creates draft PO for all low stock items from preferred supplier'),
    makeRequestItem('3.14 Create Manual Purchase Order (Stock-In)', 'POST', 'api/v1/purchases', 'Manager', 'bearer', [
        'supplier_id' => 1,
        'order_date' => date('Y-m-d'),
        'items' => [
            ['variant_id' => 1, 'quantity_ordered' => 50, 'unit_cost' => 45.00],
            ['variant_id' => 2, 'quantity_ordered' => 50, 'unit_cost' => 45.00],
        ],
        'notes' => 'Quarterly seasonal restock shipment.',
    ], 'Record supplier stock-in and update average cost'),
    makeRequestItem('3.15 List Purchase Orders', 'GET', 'api/v1/purchases', 'Manager', 'bearer', null, 'List historical supplier purchase orders'),
    makeRequestItem('3.16 Get Single Purchase Order Details', 'GET', 'api/v1/purchases/1', 'Manager', 'bearer', null, 'View items, received quantities, and cost totals on a PO'),

    // FMCG Expiry Date Batch Tracking
    makeRequestItem('3.17 Create Expirable Inventory Batch (Lot/Expiry)', 'POST', 'api/v1/variants/16/batches', 'Manager', 'bearer', [
        'batch_number' => 'LOT-2026-B08',
        'manufacturing_date' => '2026-08-01',
        'expiry_date' => '2027-08-01',
        'quantity' => 120,
    ], 'Track batch numbers and expiration dates for beers, foods, and cosmetics'),
    makeRequestItem('3.18 List Expiring-Soon Batches (FIFO / FEFO Alert)', 'GET', 'api/v1/inventory/expiring-soon?days_threshold=60', 'Manager', 'bearer', null, 'Identify products expiring within next 60 days to trigger flash clearances'),

    // Stock Movement Audit & Manual Adjustments
    makeRequestItem('3.19 List Audit Stock Movement Ledger', 'GET', 'api/v1/stock-movements?page=1&per_page=20', 'Manager', 'bearer', null, 'Immutable double-entry stock ledger recording every SALE, PURCHASE, and ADJUSTMENT'),
    makeRequestItem('3.20 Perform Manual Stock Adjustment', 'POST', 'api/v1/stock-movements/adjust', 'Manager', 'bearer', [
        'variant_id' => 1,
        'quantity_change' => -2,
        'movement_type' => 'DAMAGE',
        'reason' => 'Fabric damaged during floor display handling.',
    ], 'Adjust inventory for damages, shrinkage, or audit reconciliation'),

    // Suppliers CRM
    makeRequestItem('3.21 List Suppliers', 'GET', 'api/v1/suppliers', 'Manager', 'bearer', null, 'List wholesale textile suppliers, breweries, and publishers'),
    makeRequestItem('3.22 Create New Supplier', 'POST', 'api/v1/suppliers', 'Manager', 'bearer', [
        'supplier_name' => 'Angkor Silk Weaving Cooperative',
        'contact_person' => 'Serey Vuth',
        'phone' => '023888999',
        'email' => 'supply@angkorsilk.com.kh',
        'address' => 'Banteay Srei District, Siem Reap',
    ], 'Register new verified vendor'),
    makeRequestItem('3.23 Update Supplier Profile', 'PUT', 'api/v1/suppliers/1', 'Manager', 'bearer', [
        'contact_person' => 'Serey Vuth (Director)',
    ], 'Update supplier contact details'),
    makeRequestItem('3.24 Delete Supplier', 'DELETE', 'api/v1/suppliers/1', 'Manager', 'bearer', null, 'Remove supplier'),
];

// ─────────────────────────────────────────────────────────────────────────────
// LEVEL 4: EXECUTIVE ADMINISTRATOR & AUDITOR (Role: ADMIN / SUPERADMIN)
// ─────────────────────────────────────────────────────────────────────────────
$adminItems = [
    // Executive Analytics & KPI Dashboard
    makeRequestItem('4.1 Get Executive Dashboard Statistics & KPIs', 'GET', 'api/v1/dashboard/stats', 'Admin', 'bearer', null, 'Executive summary of total revenue, today sales, active shifts, top sellers, and low stock warnings'),

    // Employee & User Administration
    makeRequestItem('4.2 List All Staff & Employee Accounts', 'GET', 'api/v1/employees', 'Admin', 'bearer', null, 'List all system users with assigned RBAC roles and store locations'),
    makeRequestItem('4.3 Create New Staff Account with RBAC Role', 'POST', 'api/v1/employees', 'Admin', 'bearer', [
        'employee_name' => 'Dara Sam',
        'username' => 'dara.sam',
        'password' => 'Dara@123456',
        'position' => 'Senior Cashier',
        'phone' => '098112233',
        'email' => 'dara.sam@kesararamwithdigital.tech',
        'role' => 'CASHIER',
        'status' => 'ACTIVE',
    ], 'Provision new staff account and assign CASHIER, MANAGER, or ADMIN role'),
    makeRequestItem('4.4 Get Single Employee Details', 'GET', 'api/v1/employees/1', 'Admin', 'bearer', null, 'Fetch employee profile and security permissions'),
    makeRequestItem('4.5 Update Employee Role, Store & Credentials', 'PUT', 'api/v1/employees/1', 'Admin', 'bearer', [
        'position' => 'Store Assistant Manager',
        'role' => 'MANAGER',
    ], 'Promote staff or update system permissions'),
    makeRequestItem('4.6 Deactivate / Terminate Staff Account', 'DELETE', 'api/v1/employees/1', 'Admin', 'bearer', null, 'Revoke system access and invalidate all active Sanctum tokens'),

    // Master System Settings & Promotions
    makeRequestItem('4.7 Create Campaign Promotion Code', 'POST', 'api/v1/promotions', 'Admin', 'bearer', [
        'promotion_name' => 'Water Festival Super Flash Sale',
        'coupon_code' => 'BONOMTOUK2026',
        'discount_type' => 'PERCENTAGE',
        'discount_value' => 20.00,
        'min_purchase_amount' => 50.00,
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-07',
        'is_active' => true,
    ], 'Create storewide or coupon-based promotional campaign'),
    makeRequestItem('4.8 Delete Promotion', 'DELETE', 'api/v1/promotions/1', 'Admin', 'bearer', null, 'Deactivate and delete promo code'),
    makeRequestItem('4.9 Issue 16-Digit Gift Card Voucher', 'POST', 'api/v1/gift-cards/issue', 'Admin', 'bearer', [
        'purchaser_customer_id' => 1,
        'initial_balance' => 100.00,
        'pin' => '5678',
        'expiry_date' => date('Y-m-d', strtotime('+1 year')),
    ], 'Issue stored-value customer gift card with secure PIN'),
    makeRequestItem('4.10 Create Marketing Campaign Banner', 'POST', 'api/v1/marketing/banners', 'Admin', 'bearer', [
        'title' => 'Monogram Silk Capsule 2026',
        'subtitle' => 'High Couture Meets Cambodian Heritage',
        'banner_url' => 'https://res.cloudinary.com/od8t271n/image/upload/v1786905148/KHMERIEL_ACCESSORIES_EMBROIDERED_MONOGRAM_SILK_SCARF_BLACK_LOOK_cloth_377.png',
        'call_to_action_url' => '/catalog/dresses',
        'display_order' => 1,
        'is_active' => true,
    ], 'Publish new homepage marketing banner'),
    makeRequestItem('4.11 Delete Marketing Banner', 'DELETE', 'api/v1/marketing/banners/1', 'Admin', 'bearer', null, 'Remove banner slide'),
    makeRequestItem('4.12 Create Master Product Category', 'POST', 'api/v1/categories', 'Admin', 'bearer', [
        'category_name' => 'Watches & Fine Timepieces',
        'department_type' => 'FASHION_APPAREL',
        'description' => 'Luxury wristwatches and leather straps.',
    ], 'Add new omnichannel department to system taxonomy'),
    makeRequestItem('4.13 Update Category Details', 'PUT', 'api/v1/categories/1', 'Admin', 'bearer', [
        'description' => 'Updated luxury evening gowns description.',
    ], 'Update category metadata'),
    makeRequestItem('4.14 Delete Category', 'DELETE', 'api/v1/categories/1', 'Admin', 'bearer', null, 'Delete department category'),
];

$finalCollection = [
    'info' => [
        'name' => 'KhmeRiel Omnichannel MIS & POS Engine — Master RBAC API Collection (118 Endpoints)',
        '_postman_id' => 'khmeriel-omnichannel-rbac-v1-master',
        'description' => "Complete, exhaustive REST API collection for KhmeRiel (Store Stock MIS & Point-of-Sale Engine) with 100% RBAC role-level classification.\n\nStructure:\n1. Level 0: Public / Storefront (No Auth)\n2. Level 1: Cashier & POS Operator (CASHIER / STAFF)\n3. Level 2: Inventory & Warehouse Manager (MANAGER)\n4. Level 3: Executive Administrator & Auditor (ADMIN / SUPERADMIN)",
        'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
    ],
    'item' => [
        [
            'name' => '1. Public & Customer Storefront (No Auth)',
            'description' => 'Storefront browsing, category taxonomy, live stock queries, customer reviews, KHQR generation, and audio feedback.',
            'item' => $publicItems,
        ],
        [
            'name' => '2. Level 1: Cashier & POS Operator (Role: CASHIER / STAFF)',
            'description' => 'Cash drawer shifts (open, drop, close Z-Report), POS checkout, invoice receipt generation, customer points redemption, and delivery dispatch.',
            'item' => $cashierItems,
        ],
        [
            'name' => '3. Level 2: Inventory & Warehouse Manager (Role: MANAGER)',
            'description' => 'Master product catalog CRUD, SKU variant matrices, Cloudinary media upload, restock recommendations, 1-click PO auto-generation, FEFO batch tracking, and double-entry stock adjustments.',
            'item' => $managerItems,
        ],
        [
            'name' => '4. Level 3: Executive Administrator & Auditor (Role: ADMIN / SUPERADMIN)',
            'description' => 'Executive KPI dashboard, staff management with RBAC roles, storewide promotions, 16-digit gift card vouchers, marketing banners, and system taxonomy.',
            'item' => $adminItems,
        ],
    ],
    'variable' => [
        ['key' => 'base_url', 'value' => 'https://api.kesararamwithdigital.tech', 'type' => 'string'],
        ['key' => 'local_url', 'value' => 'http://127.0.0.1:8000', 'type' => 'string'],
        ['key' => 'token', 'value' => 'YOUR_BEARER_SANCTUM_TOKEN_HERE', 'type' => 'string'],
    ],
];

$outputFile = __DIR__.'/../docs/KHMERIEL_MASTER_API_RBAC_COLLECTION.postman_collection.json';
file_put_contents($outputFile, json_encode($finalCollection, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

echo "Successfully generated: {$outputFile}\n";
echo 'Total Public Endpoints: '.count($publicItems)."\n";
echo 'Total Cashier Endpoints: '.count($cashierItems)."\n";
echo 'Total Manager Endpoints: '.count($managerItems)."\n";
echo 'Total Admin Endpoints: '.count($adminItems)."\n";
