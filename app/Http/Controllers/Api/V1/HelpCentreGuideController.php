<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HelpCentreGuideController extends BaseApiController
{
    /**
     * Return the structured Documentation & Operations Guide as pure JSON.
     */
    public function index(Request $request): JsonResponse
    {
        $categories = [
            [
                'id' => 'getting-started',
                'title' => 'Getting Started',
                'tagline' => 'Fast 3-Minute System Quickstart',
                'icon' => 'fa-rocket',
                'description' => 'Everything you need to know to log in, understand user permissions, and operate your daily store workflow.',
                'sections' => [
                    [
                        'title' => '1. The 4 User Access Levels',
                        'content' => "• Guest: Browse catalog and size charts (view-only).\n• Cashier: Scan barcodes, handle POS sales, open/close cash shifts.\n• Manager: Restock inventory, create purchase orders, approve discounts.\n• Admin: Manage staff accounts, edit system settings, view financial logs.",
                    ],
                    [
                        'title' => '2. Daily Operational Routine',
                        'content' => "1. Morning: Open register shift and enter starting cash float.\n2. Day: Scan items at POS, accept payments (Cash, Card, QR), print receipts.\n3. Evening: Close shift, count drawer cash, print Z-Report.",
                    ],
                    [
                        'title' => '3. Safe Security Habits',
                        'content' => "• Lock your screen when leaving the cash counter.\n• Never share login passwords between staff members.",
                    ],
                ],
                'tips' => [
                    'Press ⌘K (or Ctrl+K) anywhere in this guide to instantly find what you need.',
                ],
            ],
            [
                'id' => 'accounts-crm',
                'title' => 'Customers & Loyalty',
                'tagline' => 'Customer Profiles & Reward Points',
                'icon' => 'fa-users',
                'description' => 'Search customers by phone number, give loyalty points on every purchase, and redeem instant discounts.',
                'sections' => [
                    [
                        'title' => '1. Fast Customer Lookup',
                        'content' => "• Type customer phone number at POS checkout.\n• Existing profile loads with name, VIP level, and points balance in 1 second.",
                    ],
                    [
                        'title' => '2. How Loyalty Points Work',
                        'content' => "• Earn: Customer gets 1 Point for every $1.00 spent.\n• Redeem: 100 Points = $5.00 instant cash discount at checkout.\n• Tiers: Bronze (0-499 pts) → Silver (500 pts) → Gold (1,500 pts) → VIP Platinum (3,000+ pts).",
                    ],
                ],
                'tips' => [
                    'Always ask for phone number before scanning items so points are credited properly.',
                ],
            ],
            [
                'id' => 'inventory-management',
                'title' => 'Inventory & Matrix',
                'tagline' => 'Stock Balances, Sizes & Colors',
                'icon' => 'fa-boxes-stacked',
                'description' => 'Understand the 4-tier quantity lifecycle, barcode scanning, and 2D Size x Color grid layout.',
                'sections' => [
                    [
                        'title' => '1. The 4-Tier Quantity Model',
                        'content' => "• On Hand: Physical stock in store right now.\n• Reserved: Items held in active quotes or customer cart holds.\n• Available = On Hand − Reserved (what you can safely sell).\n• Incoming: Stock ordered from suppliers on Purchase Orders in transit.",
                    ],
                    [
                        'title' => '2. 2D Size x Color Matrix',
                        'content' => "• View real-time availability across all size and color variations in a compact table.\n• Instantly see out-of-stock variations in red and low stock in amber.",
                    ],
                ],
                'tips' => [
                    'Never sell below Available stock to prevent inventory overselling.',
                ],
            ],
            [
                'id' => 'pos-sales-checkout',
                'title' => 'Point of Sale & KHQR',
                'tagline' => 'Barcode Scanning, Payments & Receipts',
                'icon' => 'fa-barcode',
                'description' => 'Process checkout in seconds with barcode scanner support, cash change calculator, and dynamic KHQR.',
                'sections' => [
                    [
                        'title' => '1. 3-Second Fast Checkout',
                        'content' => "1. Scan barcode or tap item from touch catalog.\n2. Choose payment method (Cash, Card, QR, ABA, Bakong).\n3. Enter amount tendered -> change due calculated automatically -> receipt prints.",
                    ],
                    [
                        'title' => '2. Dynamic KHQR / Bakong Payments',
                        'content' => "• Tap KHQR payment method at checkout to show the dynamic QR code on screen.\n• Supports all Cambodian bank apps (ABA, ACLEDA, Canadia, Wing, etc.).",
                    ],
                ],
                'tips' => [
                    'For cash payments, system automatically converts USD and Khmer Riel at 4,100 KHR/$1.',
                ],
            ],
            [
                'id' => 'estimates-invoicing',
                'title' => 'Invoicing & Tax',
                'tagline' => '1-Click Quotes, Invoices & VAT',
                'icon' => 'fa-file-invoice-dollar',
                'description' => 'Create price estimates, convert approved quotes to invoices in 1 click, and auto-calculate 10% VAT.',
                'sections' => [
                    [
                        'title' => '1. 1-Click Estimate to Invoice Conversion',
                        'content' => "• Create quote: reserves items without deducting stock immediately.\n• Customer approves: click \"Convert to Invoice\" -> stock deducts atomically and payment registers.",
                    ],
                    [
                        'title' => '2. 10% Tax-Exclusive (VAT) Formula',
                        'content' => "• Subtotal = Items Total − Discount\n• Tax (10% VAT) = Subtotal × 0.10\n• Grand Total = Subtotal + Tax",
                    ],
                ],
                'tips' => [
                    'Quotes stay valid for 14 days by default.',
                ],
            ],
            [
                'id' => 'purchasing-procurement',
                'title' => 'Purchasing & POs',
                'tagline' => 'Supplier Orders & Restocking',
                'icon' => 'fa-truck-ramp-box',
                'description' => 'Order merchandise from suppliers, track delivery transit, and receive stock into inventory with 1 tap.',
                'sections' => [
                    [
                        'title' => '1. Purchase Order 3-Step Lifecycle',
                        'content' => "1. Draft: Pick items and enter agreed supplier unit costs.\n2. Ordered: Send PO to vendor (items show as Incoming stock).\n3. Received: Count boxes on arrival and tap Receive Stock to add to On Hand.",
                    ],
                    [
                        'title' => '2. Smart Reorder Alerts',
                        'content' => 'The system flags items whenever On Hand falls below the minimum threshold (e.g. fewer than 5 units).',
                    ],
                ],
                'tips' => [
                    'Always count physical items before tapping "Receive Stock" to verify supplier shipment counts.',
                ],
            ],
            [
                'id' => 'locations-branches',
                'title' => 'Store Locations',
                'tagline' => 'Multi-Store & Stock Transfers',
                'icon' => 'fa-store',
                'description' => 'Manage inventory across multiple store branches and central warehouses without confusion.',
                'sections' => [
                    [
                        'title' => '1. Multi-Store Isolation',
                        'content' => 'Each store branch has its own live inventory balance. Cashiers sell stock available at their active branch.',
                    ],
                    [
                        'title' => '2. Transferring Stock Between Stores',
                        'content' => "1. Origin store creates Stock Transfer manifest.\n2. Driver transports items.\n3. Destination manager checks items and taps Accept to update local counts.",
                    ],
                ],
                'tips' => [
                    'Stock transfers require destination manager sign-off to ensure zero lost items.',
                ],
            ],
            [
                'id' => 'kitting-bundling',
                'title' => 'Bundles & Kits',
                'tagline' => 'Combo Sets & Auto-Deductions',
                'icon' => 'fa-cubes',
                'description' => 'Sell multi-item gift sets or outfit combos with automatic sub-item stock deduction.',
                'sections' => [
                    [
                        'title' => '1. How Combo Bundles Work',
                        'content' => "• Example: \"Weekend Outfit Set\" = 1x Shirt + 1x Pants + 1x Cap.\n• Set a package price to encourage bulk buys.",
                    ],
                    [
                        'title' => '2. Dynamic Kit Availability',
                        'content' => 'The bundle is only available if all individual sub-items are in stock. Selling 1 bundle deducts all sub-items simultaneously.',
                    ],
                ],
                'tips' => [
                    'Combos increase Average Order Value (AOV).',
                ],
            ],
            [
                'id' => 'financial-valuation',
                'title' => 'Valuation & Margins',
                'tagline' => 'Asset Worth & Profit Formulas',
                'icon' => 'fa-chart-pie',
                'description' => 'Understand the math behind asset valuation and margin tracking.',
                'sections' => [
                    [
                        'title' => '1. Asset Valuation Formulas',
                        'content' => "• Cost Value = ∑ (Quantity on Hand × Cost Price)\n• Retail Value = ∑ (Quantity on Hand × Sale Price)\n• Potential Gross Profit = Retail Value − Cost Value",
                    ],
                    [
                        'title' => '2. Profit Margin Formulas',
                        'content' => "• Gross Margin (%) = ((Sale Price − Cost Price) / Sale Price) × 100\n• Markup (%) = ((Sale Price − Cost Price) / Cost Price) × 100",
                    ],
                ],
                'tips' => [
                    'Maintain a gross margin above 45% on core apparel lines.',
                ],
            ],
            [
                'id' => 'pos-shifts',
                'title' => 'Cash Shifts & Z-Report',
                'tagline' => 'Daily Drawer Balance & End of Day',
                'icon' => 'fa-cash-register',
                'description' => 'Simple 3-step cash drawer reconciliation to ensure 100% accurate daily cash counts.',
                'sections' => [
                    [
                        'title' => '1. Morning Opening Float',
                        'content' => 'Count starting cash. Enter amount to unlock the POS register.',
                    ],
                    [
                        'title' => '2. Midday Cash Safe Drop',
                        'content' => 'When drawer holds excess cash, move to the store safe. System tracks drops automatically.',
                    ],
                    [
                        'title' => '3. Closing Z-Report Formula',
                        'content' => "• Expected Cash = (Opening Float + Cash Sales) − Cash Drops\n• Discrepancy = Closing Cash − Expected Cash (0 = BALANCED).",
                    ],
                ],
                'tips' => [
                    'Always count cash in private before shift sign-off.',
                ],
            ],
        ];

        $popularTopics = [
            '4-Tier Quantity Model', 'On Hand vs Available', 'Loyalty Points Rule', '10% VAT Formula',
            'Gross Profit Margin', 'Barcode Scanning', 'Opening Cash Float', 'Closing Z-Report',
            'Estimate to Invoice', 'Stock Transfers', 'Combo Bundles', 'Safe Cash Drops',
            'Role Permissions', 'Customer Phone Search', 'Audit Trail', 'Tax Invoices',
        ];

        return $this->successResponse([
            'title' => 'OutfitShop Documentation & Operations Guide',
            'tagline' => 'Clear, Actionable Knowledge Base for OutfitShop Ecommerce Clothing API',
            'brand' => config('api.brand'),
            'banner_url' => 'https://res.cloudinary.com/od8t271n/image/upload/v1787064621/bleu-SNPCodeLab.png',
            'total_topics' => count($categories),
            'categories' => $categories,
            'popular_topics' => $popularTopics,
        ], 'OutfitShop documentation data retrieved');
    }
}
