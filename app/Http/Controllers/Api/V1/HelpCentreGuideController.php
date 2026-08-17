<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HelpCentreGuideController extends BaseApiController
{
    /**
     * Display the cognitive, concise, and psychology-optimized Documentation & Guide.
     *
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function index(Request $request)
    {
        $categories = [
            [
                'id'          => 'getting-started',
                'title'       => 'Getting Started',
                'tagline'     => 'Fast 3-Minute System Quickstart',
                'icon'        => 'fa-rocket',
                'description' => 'Everything you need to know to log in, understand user permissions, and operate your daily store workflow.',
                'sections'    => [
                    [
                        'title'   => '1. The 4 User Access Levels',
                        'content' => '• Guest: Browse catalog and size charts (view-only).\n• Cashier: Scan barcodes, handle POS sales, open/close cash shifts.\n• Manager: Restock inventory, create purchase orders, approve discounts.\n• Admin: Manage staff accounts, edit system settings, view financial logs.',
                    ],
                    [
                        'title'   => '2. Daily Operational Routine',
                        'content' => '1. Morning: Open register shift and enter starting cash float.\n2. Day: Scan items at POS, accept payments (Cash, Card, QR), print receipts.\n3. Evening: Close shift, count drawer cash, print Z-Report.',
                    ],
                    [
                        'title'   => '3. Safe Security Habits',
                        'content' => '• Lock your screen when leaving the cash counter.\n• Never share login passwords between staff members.',
                    ],
                ],
                'tips' => [
                    'Press ⌘K (or Ctrl+K) anywhere in this guide to instantly find what you need.',
                ]
            ],
            [
                'id'          => 'accounts-crm',
                'title'       => 'Customers & Loyalty',
                'tagline'     => 'Customer Profiles & Reward Points',
                'icon'        => 'fa-users',
                'description' => 'Search customers by phone number, give loyalty points on every purchase, and redeem instant discounts.',
                'sections'    => [
                    [
                        'title'   => '1. Fast Customer Lookup',
                        'content' => '• Type customer phone number at POS checkout.\n• Existing profile loads with name, VIP level, and points balance in 1 second.',
                    ],
                    [
                        'title'   => '2. How Loyalty Points Work',
                        'content' => '• Earn: Customer gets 1 Point for every $1.00 spent.\n• Redeem: 100 Points = $5.00 instant cash discount at checkout.\n• Tiers: Bronze (0-499 pts) → Silver (500 pts) → Gold (1,500 pts) → VIP Platinum (3,000+ pts).',
                    ],
                    [
                        'title'   => '3. Why This Works (Customer Psychology)',
                        'content' => 'Customers return 3x more often when they see their points growing on their printed receipt.',
                    ],
                ],
                'tips' => [
                    'Always ask: "Do you have a loyalty phone number?" before hitting payment.',
                ]
            ],
            [
                'id'          => 'inventory-matrix',
                'title'       => 'Inventory & Matrix',
                'tagline'     => '4-Tier Stock & Size/Color Grid',
                'icon'        => 'fa-boxes-stacked',
                'description' => 'Know your real stock counts at a glance and prevent overselling using the 4-tier quantity rule.',
                'sections'    => [
                    [
                        'title'   => '1. The 4 Stock Numbers Explained',
                        'content' => '• On Hand: Physical items in your store right now.\n• Reserved: Items held for customer quotes or pending pickup.\n• Available: What you can sell today = (On Hand − Reserved).\n• Incoming: Stock already ordered from supplier on the way.',
                    ],
                    [
                        'title'   => '2. Size × Color Matrix',
                        'content' => 'Clothing items are organized in a clean 2D grid (Sizes S to XL across Colors Black, White, Navy). Enter quantities in 1 screen without creating separate items.',
                    ],
                    [
                        'title'   => '3. Barcode Scanning',
                        'content' => 'Point scanner at clothing tag. Price and stock appear immediately. Stock decrements automatically when payment succeeds.',
                    ],
                ],
                'tips' => [
                    'Never sell below Available quantity to avoid customer disappointment.',
                ]
            ],
            [
                'id'          => 'invoices-estimates',
                'title'       => 'Invoices & Quotes',
                'tagline'     => 'Estimates to Invoice & 10% VAT',
                'icon'        => 'fa-file-invoice-dollar',
                'description' => 'Send price quotes to clients, convert them to official invoices in 1 click, and print clean A4 or receipt slips.',
                'sections'    => [
                    [
                        'title'   => '1. Quotes vs Invoices',
                        'content' => '• Estimate (Quote): Reserves stock temporarily without deducting inventory.\n• Invoice: Confirms sale, deducts physical stock, and creates payment record.',
                    ],
                    [
                        'title'   => '2. 1-Click Invoice Conversion',
                        'content' => 'When customer says "Yes", open the Estimate and click Convert. Stock decrements instantly and official receipt is ready to print.',
                    ],
                    [
                        'title'   => '3. 10% VAT Formula',
                        'content' => '• Subtotal = Items Total − Discount\n• Tax (10% VAT) = Subtotal × 0.10\n• Grand Total = Subtotal + Tax',
                    ],
                ],
                'tips' => [
                    'Quotes stay valid for 14 days by default.',
                ]
            ],
            [
                'id'          => 'purchasing-procurement',
                'title'       => 'Purchasing & POs',
                'tagline'     => 'Supplier Orders & Restocking',
                'icon'        => 'fa-truck-ramp-box',
                'description' => 'Order merchandise from suppliers, track delivery transit, and receive stock into inventory with 1 tap.',
                'sections'    => [
                    [
                        'title'   => '1. Purchase Order 3-Step Lifecycle',
                        'content' => '1. Draft: Pick items and enter agreed supplier unit costs.\n2. Ordered: Send PO to vendor (items show as Incoming stock).\n3. Received: Count boxes on arrival and tap Receive Stock to add to On Hand.',
                    ],
                    [
                        'title'   => '2. Smart Reorder Alerts',
                        'content' => 'The system flags items with a red badge whenever On Hand falls below the minimum safety threshold (e.g. fewer than 5 units).',
                    ],
                ],
                'tips' => [
                    'Always count physical items before tapping "Receive Stock" to catch supplier delivery errors.',
                ]
            ],
            [
                'id'          => 'locations-branches',
                'title'       => 'Store Locations',
                'tagline'     => 'Multi-Store & Stock Transfers',
                'icon'        => 'fa-store',
                'description' => 'Manage inventory across multiple store branches and central warehouses without confusion.',
                'sections'    => [
                    [
                        'title'   => '1. Multi-Store Isolation',
                        'content' => 'Each store branch has its own live inventory balance. Cashiers only see and sell stock available at their active branch.',
                    ],
                    [
                        'title'   => '2. Transferring Stock Between Stores',
                        'content' => '1. Origin store creates Stock Transfer manifest.\n2. Driver transports items (status: In Transit).\n3. Destination manager checks items and taps Accept to update local counts.',
                    ],
                ],
                'tips' => [
                    'Stock transfers require destination manager sign-off to ensure zero lost items.',
                ]
            ],
            [
                'id'          => 'kitting-bundling',
                'title'       => 'Bundles & Kits',
                'tagline'     => 'Combo Sets & Auto-Deductions',
                'icon'        => 'fa-cubes',
                'description' => 'Sell multi-item gift sets or outfit combos with automatic sub-item stock deduction.',
                'sections'    => [
                    [
                        'title'   => '1. How Combo Bundles Work',
                        'content' => '• Example: "Weekend Outfit Set" = 1x Shirt + 1x Pants + 1x Cap.\n• Set a special package price to encourage bulk buys.',
                    ],
                    [
                        'title'   => '2. Dynamic Kit Availability',
                        'content' => 'The bundle is only available if all individual sub-items are in stock. Selling 1 bundle deducts all sub-items simultaneously.',
                    ],
                ],
                'tips' => [
                    'Combos increase Average Order Value (AOV) by up to 35%.',
                ]
            ],
            [
                'id'          => 'financial-valuation',
                'title'       => 'Valuation & Margins',
                'tagline'     => 'Asset Worth & Profit Formulas',
                'icon'        => 'fa-chart-pie',
                'description' => 'Know exactly how much money is sitting on your shelves and calculate gross profit margins in seconds.',
                'sections'    => [
                    [
                        'title'   => '1. Balance Sheet Stock Worth',
                        'content' => '• Purchased Value (Cost Basis): Total Units × What You Paid.\n• Resale Value (Retail Worth): Total Units × Selling Price.\n• Potential Profit = Resale Value − Purchased Value.',
                    ],
                    [
                        'title'   => '2. Gross Margin % Formula',
                        'content' => '• Gross Margin % = (Profit ÷ Resale Value) × 100\n• Healthy apparel target: 45% to 65% gross margin.',
                    ],
                ],
                'tips' => [
                    'Check your Gross Margin report weekly to identify your most profitable clothing items.',
                ]
            ],
            [
                'id'          => 'security-rbac',
                'title'       => 'Security & Roles',
                'tagline'     => 'Permissions & Audit Trails',
                'icon'        => 'fa-shield-halved',
                'description' => 'Role-based access controls and automatic immutable logs for every price change and stock adjustment.',
                'sections'    => [
                    [
                        'title'   => '1. Separation of Responsibilities',
                        'content' => '• Cashiers cannot edit prices or delete sales.\n• Only Managers can adjust stock or void receipts.\n• Prevents internal shrinkage and unauthorized discounts.',
                    ],
                    [
                        'title'   => '2. Automatic Audit Trail',
                        'content' => 'Every action records who did it, exact timestamp, and what changed (Before & After values).',
                    ],
                ],
                'tips' => [
                    'Review voided sales logs every Monday morning.',
                ]
            ],
            [
                'id'          => 'pos-shifts',
                'title'       => 'Cash Shifts & Z-Report',
                'tagline'     => 'Daily Drawer Balance & End of Day',
                'icon'        => 'fa-cash-register',
                'description' => 'Simple 3-step cash drawer reconciliation to ensure 100% accurate daily cash counts.',
                'sections'    => [
                    [
                        'title'   => '1. Morning Opening Float',
                        'content' => 'Count starting cash (e.g. $100 in change). Enter amount to unlock the POS register.',
                    ],
                    [
                        'title'   => '2. Midday Cash Safe Drop',
                        'content' => 'When drawer holds more than $1,000, move excess cash to the store safe. System tracks the drop automatically.',
                    ],
                    [
                        'title'   => '3. Closing Z-Report Formula',
                        'content' => '• Expected Cash = (Opening Float + Cash Sales) − Cash Drops\n• Count physical cash. If difference is $0.00, your drawer is perfectly balanced.',
                    ],
                ],
                'tips' => [
                    'Always count cash in private away from customer view before shift sign-off.',
                ]
            ],
        ];

        $popularTopics = [
            '4-Tier Quantity Model', 'On Hand vs Available', 'Loyalty Points Rule', '10% VAT Formula',
            'Gross Profit Margin', 'Barcode Scanning', 'Opening Cash Float', 'Closing Z-Report',
            'Estimate to Invoice', 'Stock Transfers', 'Combo Bundles', 'Safe Cash Drops',
            'Role Permissions', 'Customer Phone Search', 'Audit Trail', 'Print Tax Invoice'
        ];

        if ($request->wantsJson() && !$request->has('html')) {
            return $this->successResponse([
                'title'          => 'Documentation & Operations Guide',
                'tagline'        => 'Clear, Actionable Knowledge Base',
                'total_topics'   => count($categories),
                'categories'     => $categories,
                'popular_topics' => $popularTopics,
            ], 'Documentation data retrieved');
        }

        return response($this->buildShadcnTemplHtml($categories, $popularTopics), 200)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Render the official shadcn-templ documentation layout with responsive sizing.
     *
     * @param array $categories
     * @param array $popularTopics
     * @return string
     */
    protected function buildShadcnTemplHtml(array $categories, array $popularTopics): string
    {
        $categoriesJson = json_encode($categories);

        $sidebarNavHtml = '';
        foreach ($categories as $index => $cat) {
            $activeClass = $index === 0 ? 'active' : '';
            $sidebarNavHtml .= "
            <a href='#{$cat['id']}' class='nav-item {$activeClass}' data-category='{$cat['id']}' onclick='selectCategory({$index})'>
                <i class='fa-solid {$cat['icon']} nav-item-icon'></i>
                <span>{$cat['title']}</span>
            </a>";
        }

        $topicsPillsHtml = '';
        foreach ($popularTopics as $topic) {
            $topicsPillsHtml .= "<button class='topic-badge' onclick='searchTopic(\"" . htmlspecialchars($topic, ENT_QUOTES) . "\")'>" . htmlspecialchars($topic) . "</button>";
        }

        return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Documentation | Operations Guide</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'>
    <style>
        :root {
            --background: #ffffff;
            --foreground: #09090b;
            --card: #ffffff;
            --card-foreground: #09090b;
            --popover: #ffffff;
            --popover-foreground: #09090b;
            --primary: #18181b;
            --primary-foreground: #fafafa;
            --secondary: #f4f4f5;
            --secondary-foreground: #18181b;
            --muted: #f4f4f5;
            --muted-foreground: #71717a;
            --accent: #f4f4f5;
            --accent-foreground: #18181b;
            --destructive: #ef4444;
            --destructive-foreground: #fafafa;
            --border: #e4e4e7;
            --input: #e4e4e7;
            --ring: #18181b;
            --radius: 3px;
        }
        .dark {
            --background: #09090b;
            --foreground: #fafafa;
            --card: #09090b;
            --card-foreground: #fafafa;
            --popover: #09090b;
            --popover-foreground: #fafafa;
            --primary: #fafafa;
            --primary-foreground: #18181b;
            --secondary: #27272a;
            --secondary-foreground: #fafafa;
            --muted: #27272a;
            --muted-foreground: #a1a1aa;
            --accent: #27272a;
            --accent-foreground: #fafafa;
            --destructive: #7f1d1d;
            --destructive-foreground: #fafafa;
            --border: #27272a;
            --input: #27272a;
            --ring: #d4d4d8;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            width: 100%;
            min-height: 100vh;
            background-color: var(--background);
            color: var(--foreground);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.5;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Fullscreen Responsive Layout ── */
        .site-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: 100%;
        }

        /* ── Sticky Header ── */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 50;
            width: 100%;
            border-bottom: 1px solid var(--border);
            background-color: var(--background);
            backdrop-filter: blur(12px);
        }
        .header-container {
            display: flex;
            height: 56px;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            max-width: 100%;
            width: 100%;
        }
        .brand-text-block {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: var(--foreground);
        }
        .brand-text {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: -0.02em;
            text-transform: uppercase;
        }
        .brand-tag {
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: var(--radius);
            background: var(--muted);
            color: var(--muted-foreground);
            border: 1px solid var(--border);
            text-transform: uppercase;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .search-trigger-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--muted);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 6px 12px;
            font-size: 12px;
            color: var(--muted-foreground);
            cursor: pointer;
            width: 240px;
            justify-content: space-between;
            transition: all 0.15s ease;
        }
        .search-trigger-btn:hover { border-color: var(--ring); color: var(--foreground); }
        .kbd-badge {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 10px;
            font-weight: 600;
            background: var(--background);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1px 5px;
        }
        .theme-toggle-btn {
            background: transparent;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--foreground);
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .theme-toggle-btn:hover { background: var(--accent); }

        /* ── Fullscreen Main Grid ── */
        .layout-grid {
            display: grid;
            grid-template-columns: 260px minmax(0, 1fr) 240px;
            min-height: calc(100vh - 56px);
            width: 100%;
        }

        /* ── Left Sidebar Navigation ── */
        .sidebar-left {
            position: sticky;
            top: 56px;
            height: calc(100vh - 56px);
            overflow-y: auto;
            border-right: 1px solid var(--border);
            padding: 24px 16px;
        }
        .sidebar-heading {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted-foreground);
            margin-bottom: 10px;
            padding: 0 8px;
        }
        .nav-group {
            display: flex;
            flex-direction: column;
            gap: 2px;
            margin-bottom: 24px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 500;
            color: var(--muted-foreground);
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .nav-item:hover {
            color: var(--foreground);
            background: var(--accent);
        }
        .nav-item.active {
            color: var(--primary-foreground);
            background: var(--primary);
            font-weight: 600;
        }
        .nav-item.active .nav-item-icon {
            color: var(--primary-foreground);
        }
        .nav-item-icon {
            font-size: 13px;
            width: 16px;
            text-align: center;
        }

        /* ── Main Content Area ── */
        .main-content {
            padding: 36px 48px;
            max-width: 100%;
            min-width: 0;
        }
        .breadcrumb-row {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--muted-foreground);
            margin-bottom: 12px;
        }
        .doc-title {
            font-size: 32px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--foreground);
            margin-bottom: 8px;
        }
        .doc-description {
            font-size: 15px;
            color: var(--muted-foreground);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        /* ── Premium Component Cards ── */
        .component-card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--card);
            margin-bottom: 36px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }
        .component-card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--muted);
        }
        .component-card-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--foreground);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .component-card-body {
            padding: 28px 24px;
        }
        .section-block {
            margin-bottom: 24px;
            padding: 16px 18px;
            background: var(--muted);
            border: 1px solid var(--border);
            border-radius: var(--radius);
        }
        .section-block:last-child { margin-bottom: 0; }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--foreground);
            margin-bottom: 8px;
            letter-spacing: -0.01em;
        }
        .section-text {
            font-size: 13.5px;
            color: var(--foreground);
            line-height: 1.7;
            white-space: pre-line;
        }

        .tips-callout {
            border: 1px solid #fef08a;
            background: #fefce8;
            color: #713f12;
            border-left: 3px solid #eab308;
            border-radius: var(--radius);
            padding: 14px 18px;
            margin-top: 24px;
        }
        .dark .tips-callout {
            background: #1e1b4b;
            border-color: #3730a3;
            color: #e0e7ff;
            border-left-color: #818cf8;
        }
        .tips-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .tips-list {
            list-style: none;
            font-size: 13px;
            line-height: 1.5;
        }

        /* ── Popular Topics Cloud ── */
        .topics-section {
            margin-top: 48px;
            border-top: 1px solid var(--border);
            padding-top: 32px;
        }
        .topics-pills-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }
        .topic-badge {
            background: var(--muted);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 500;
            color: var(--muted-foreground);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .topic-badge:hover {
            background: var(--primary);
            color: var(--primary-foreground);
            border-color: var(--primary);
        }

        /* ── Right TOC Sidebar ── */
        .sidebar-right {
            position: sticky;
            top: 56px;
            height: calc(100vh - 56px);
            overflow-y: auto;
            border-left: 1px solid var(--border);
            padding: 24px 20px;
        }
        .toc-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--foreground);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .toc-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 13px;
        }
        .toc-link {
            color: var(--muted-foreground);
            text-decoration: none;
            transition: color 0.15s ease;
            line-height: 1.4;
        }
        .toc-link:hover, .toc-link.active {
            color: var(--foreground);
            font-weight: 600;
        }

        /* ── Search Modal Dialog (⌘K) ── */
        .search-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: flex-start;
            justify-content: center;
            padding-top: 100px;
            z-index: 100;
            animation: fadeIn 0.15s ease-out;
        }
        .search-dialog {
            background: var(--background);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            width: 600px;
            max-width: 90vw;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }
        .search-input-box {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border);
            gap: 12px;
        }
        .search-dialog-input {
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            font-size: 14px;
            color: var(--foreground);
        }
        .search-results-list {
            max-height: 360px;
            overflow-y: auto;
            padding: 8px;
        }
        .search-result-item {
            padding: 10px 14px;
            border-radius: var(--radius);
            font-size: 13px;
            color: var(--foreground);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.1s ease;
        }
        .search-result-item:hover { background: var(--accent); }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* ── Fluid Breakpoints ── */
        @media (max-width: 1200px) {
            .layout-grid { grid-template-columns: 240px minmax(0, 1fr); }
            .sidebar-right { display: none; }
            .main-content { padding: 32px 36px; }
        }
        @media (max-width: 840px) {
            .layout-grid { grid-template-columns: 1fr; }
            .sidebar-left { display: none; }
            .main-content { padding: 24px 16px; }
            .search-trigger-btn { width: 160px; }
        }
    </style>
</head>
<body>
    <div class='site-wrapper'>
        <header class='site-header'>
            <div class='header-container'>
                <a href='/guide' class='brand-text-block'>
                    <span class='brand-text'>GUIDE</span>
                    <span class='brand-tag'>DOCUMENTATION</span>
                </a>
                <div class='header-actions'>
                    <button class='search-trigger-btn' onclick='openSearchModal()'>
                        <span><i class='fa-solid fa-magnifying-glass' style='margin-right: 6px;'></i>Search guides...</span>
                        <span class='kbd-badge'>⌘K</span>
                    </button>
                    <button class='theme-toggle-btn' onclick='toggleTheme()' title='Toggle Theme'>
                        <i class='fa-solid fa-moon' id='theme-icon'></i>
                    </button>
                </div>
            </div>
        </header>

        <div class='layout-grid'>
            <!-- Left Sidebar Navigation -->
            <aside class='sidebar-left'>
                <div class='sidebar-heading'>Operational Guides</div>
                <nav class='nav-group' id='sidebar-nav'>
                    {$sidebarNavHtml}
                </nav>
                <div class='sidebar-heading'>Developer Tools</div>
                <nav class='nav-group'>
                    <a href='/api/v1/guide' class='nav-item'>
                        <i class='fa-solid fa-code nav-item-icon'></i>
                        <span>JSON Directory</span>
                    </a>
                    <a href='/api/v1/health' class='nav-item'>
                        <i class='fa-solid fa-signal nav-item-icon'></i>
                        <span>System Status</span>
                    </a>
                </nav>
            </aside>

            <!-- Main Documentation Content -->
            <main class='main-content'>
                <div class='breadcrumb-row'>
                    <span>Docs</span>
                    <span>/</span>
                    <span id='breadcrumb-active' style='color: var(--foreground); font-weight: 600;'>Getting Started</span>
                </div>

                <h1 class='doc-title' id='doc-title'>Getting Started</h1>
                <p class='doc-description' id='doc-description'>Fast 3-Minute System Quickstart</p>

                <div class='component-card'>
                    <div class='component-card-header'>
                        <div class='component-card-title'>
                            <i class='fa-solid fa-book-open'></i>
                            <span id='component-title'>Action Steps &amp; Workflows</span>
                        </div>
                        <span class='brand-tag'>Quick Guide</span>
                    </div>
                    <div class='component-card-body' id='component-body'>
                        <!-- Injected dynamically -->
                    </div>
                </div>

                <div class='topics-section'>
                    <div class='sidebar-heading' style='padding: 0;'>Quick Search Topics</div>
                    <div class='topics-pills-wrap'>
                        {$topicsPillsHtml}
                    </div>
                </div>
            </main>

            <!-- Right TOC Sidebar -->
            <aside class='sidebar-right'>
                <div class='toc-title'>On This Page</div>
                <div class='toc-list' id='toc-list'>
                    <!-- Injected dynamically -->
                </div>
            </aside>
        </div>
    </div>

    <!-- Search Modal Dialog (⌘K) -->
    <div class='search-modal-backdrop' id='search-modal' onclick='closeSearchModal(event)'>
        <div class='search-dialog' onclick='event.stopPropagation()'>
            <div class='search-input-box'>
                <i class='fa-solid fa-magnifying-glass' style='color: var(--muted-foreground);'></i>
                <input type='text' id='search-input' class='search-dialog-input' placeholder='Type a topic or shortcut...'>
                <span class='kbd-badge' style='cursor: pointer;' onclick='closeSearchModal()'>ESC</span>
            </div>
            <div class='search-results-list' id='search-results'>
                <!-- Injected dynamically -->
            </div>
        </div>
    </div>

    <script>
        const categoriesData = {$categoriesJson};
        let currentCategoryIndex = 0;

        function renderCategory(index) {
            currentCategoryIndex = index;
            const data = categoriesData[index];
            if (!data) return;

            document.getElementById('breadcrumb-active').innerText = data.title;
            document.getElementById('doc-title').innerText = data.title;
            document.getElementById('doc-description').innerText = data.description;
            document.getElementById('component-title').innerText = data.tagline;

            // Render Sections
            let bodyHtml = `<p style='font-size: 14px; color: var(--foreground); font-weight: 500; margin-bottom: 20px; line-height: 1.6;'>\${data.description}</p>`;
            let tocHtml = '';

            data.sections.forEach((sec, sIndex) => {
                bodyHtml += `
                <div class='section-block' id='sec-\${sIndex}'>
                    <h3 class='section-title'>\${sec.title}</h3>
                    <p class='section-text'>\${sec.content}</p>
                </div>`;

                tocHtml += `<a href='#sec-\${sIndex}' class='toc-link'>\${sec.title}</a>`;
            });

            if (data.tips && data.tips.length > 0) {
                bodyHtml += `
                <div class='tips-callout'>
                    <div class='tips-title'><i class='fa-solid fa-lightbulb'></i> Pro Tip</div>
                    <ul class='tips-list'>
                        \${data.tips.map(t => `<li>\${t}</li>`).join('')}
                    </ul>
                </div>`;
            }

            document.getElementById('component-body').innerHTML = bodyHtml;
            document.getElementById('toc-list').innerHTML = tocHtml;

            // Update Left Nav Active state
            document.querySelectorAll('.nav-item').forEach((el, i) => {
                if (i === index) {
                    el.classList.add('active');
                } else {
                    el.classList.remove('active');
                }
            });
        }

        function selectCategory(index) {
            renderCategory(index);
        }

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            document.getElementById('theme-icon').className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
        }

        // Init theme from preference
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            document.getElementById('theme-icon').className = 'fa-solid fa-sun';
        }

        // ⌘K Search Modal
        function openSearchModal() {
            document.getElementById('search-modal').style.display = 'flex';
            document.getElementById('search-input').focus();
            renderSearchResults('');
        }

        function closeSearchModal() {
            document.getElementById('search-modal').style.display = 'none';
        }

        function renderSearchResults(query) {
            const q = query.toLowerCase();
            let resultsHtml = '';

            categoriesData.forEach((cat, cIndex) => {
                const matchTitle = cat.title.toLowerCase().includes(q);
                const matchDesc = cat.description.toLowerCase().includes(q);
                const matchSections = cat.sections.some(s => s.title.toLowerCase().includes(q) || s.content.toLowerCase().includes(q));

                if (!q || matchTitle || matchDesc || matchSections) {
                    resultsHtml += `
                    <div class='search-result-item' onclick='selectCategory(\${cIndex}); closeSearchModal();'>
                        <div>
                            <div style='font-weight: 600;'>\${cat.title}</div>
                            <div style='font-size: 11px; color: var(--muted-foreground);'>\${cat.tagline}</div>
                        </div>
                        <i class='fa-solid fa-arrow-right' style='font-size: 11px; color: var(--muted-foreground);'></i>
                    </div>`;
                }
            });

            document.getElementById('search-results').innerHTML = resultsHtml || '<div style=\"padding: 16px; text-align: center; color: var(--muted-foreground); font-size: 13px;\">No matching documentation topics found.</div>';
        }

        document.getElementById('search-input').addEventListener('input', function(e) {
            renderSearchResults(e.target.value);
        });

        function searchTopic(topic) {
            openSearchModal();
            document.getElementById('search-input').value = topic;
            renderSearchResults(topic);
        }

        // Keyboard Shortcut ⌘K / Ctrl+K
        document.addEventListener('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                openSearchModal();
            }
            if (e.key === 'Escape') {
                closeSearchModal();
            }
        });

        // Initialize first category
        renderCategory(0);
    </script>
</body>
</html>";
    }
}
