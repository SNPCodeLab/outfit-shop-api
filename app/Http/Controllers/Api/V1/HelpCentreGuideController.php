<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HelpCentreGuideController extends BaseApiController
{
    /**
     * Display the official shadcn-templ style Documentation and Help Centre Guide.
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
                'tagline'     => 'System Onboarding & Architecture',
                'icon'        => 'fa-rocket',
                'description' => 'Comprehensive onboarding guide, architecture topology, access tiers, and standard response envelopes.',
                'sections'    => [
                    [
                        'title'   => 'System Classification & Architecture',
                        'content' => 'The Store Stock & Point-of-Sale Information System is an enterprise Transaction Processing System (TPS) with embedded Management Information System (MIS) reporting capabilities. It manages real-time retail checkouts, multi-location warehouse stock, automated vendor replenishment, and customer loyalty.',
                    ],
                    [
                        'title'   => '4-Tier Access Model (RBAC)',
                        'content' => 'The system enforces a hierarchical 4-tier Role-Based Access Control ladder:\n\n• Tier 1: Public / Guest — Read-only catalog browsing, size charts, store showcase.\n• Tier 2: Cashier & Staff — POS register sales, shift management, loyalty redemption, barcode lookups.\n• Tier 3: Manager — Stock adjustments, vendor purchase orders, pricing tiers, promotional campaigns.\n• Tier 4: Superadmin — Workforce HR management, system settings, database migrations, immutable audit trail.',
                    ],
                    [
                        'title'   => 'Uniform Response Structure',
                        'content' => 'All operations return predictable JSON envelopes with success flags, localized human messages, structured data payloads, and explicit HTTP status codes (200 OK, 201 Created, 401 Unauthorized, 403 Forbidden, 422 Unprocessable Entity).',
                    ],
                ],
                'tips' => [
                    'Always verify user credentials prior to executing privileged manager operations.',
                    'Keep employee logins secure and never share active cashier shift sessions.',
                ]
            ],
            [
                'id'          => 'accounts-crm',
                'title'       => 'Accounts & CRM',
                'tagline'     => 'Customer Profiles & VIP Loyalty',
                'icon'        => 'fa-users',
                'description' => 'Customer account records, VIP classification tiers, telephone checkout lookups, and loyalty point rewards.',
                'sections'    => [
                    [
                        'title'   => 'Customer Directory & Profiles',
                        'content' => 'Maintain complete customer records including full name, telephone, email address, physical delivery address, and private credit notes. Cashiers can instantly search customer profiles by phone number during POS checkout.',
                    ],
                    [
                        'title'   => 'VIP Loyalty Points Accrual',
                        'content' => 'Customers automatically earn 1 loyalty point for every $1.00 spent on qualifying purchases. Total accrued points advance customer status through Bronze, Silver, Gold, and Platinum VIP tiers.',
                    ],
                    [
                        'title'   => 'Points Redemption at POS',
                        'content' => 'Loyalty points can be redeemed directly at the POS counter for immediate transaction discounts. Standard conversion: 100 Points = $5.00 cash deduction.',
                    ],
                ],
                'tips' => [
                    'Request mobile numbers from walk-in clients to ensure their reward points are credited.',
                    'VIP customers receive automated priority notices for seasonal apparel collection drops.',
                ]
            ],
            [
                'id'          => 'inventory-matrix',
                'title'       => 'Inventory & Matrices',
                'tagline'     => '4-Tier Stock & 2D Variations Grid',
                'icon'        => 'fa-boxes-stacked',
                'description' => 'Real-time stock ledger, SalesBinder 4-tier quantity lifecycle, 2D size/color matrices, and barcode scanning.',
                'sections'    => [
                    [
                        'title'   => 'The 4-Tier Quantity Model',
                        'content' => 'Inventory is classified into four dynamic states:\n\n1. On Hand: Physical units verified in the warehouse or store branch.\n2. Reserved: Units committed to open quotation estimates, draft orders, and pending pickups.\n3. Available: Net sellable quantity = max(0, On Hand - Reserved).\n4. Incoming: Replenishment units ordered on open vendor Purchase Orders.',
                    ],
                    [
                        'title'   => '2D Size × Color Matrix Grid',
                        'content' => 'Apparel items with multiple dimensions are structured inside a 2D matrix (Sizes: S, M, L, XL, OS vs Colors: Black, White, Navy, Gold) allowing rapid mass inventory entry and wholesale bulk ordering.',
                    ],
                    [
                        'title'   => 'Continuous Barcode Scanning',
                        'content' => 'Every variant carries a unique barcode. Scanning immediately retrieves stock balances and unit pricing. Items reaching the minimum reorder point automatically trigger restocking alerts.',
                    ],
                ],
                'tips' => [
                    'Conduct weekly cycle counts on high-velocity inventory to prevent shrinkage.',
                    'Review low stock alerts daily to maintain optimal inventory turnover.',
                ]
            ],
            [
                'id'          => 'invoices-estimates',
                'title'       => 'Invoices & Billing',
                'tagline'     => 'Estimates, Invoices & 10% VAT',
                'icon'        => 'fa-file-invoice-dollar',
                'description' => 'Quotation estimates, 1-click invoice conversion, 10% VAT tax calculations, and A4 printable PDF billing.',
                'sections'    => [
                    [
                        'title'   => 'Quotation Estimates Workflow',
                        'content' => 'Create formal client quotations with custom line items, unit prices, discounts, and validity periods. Estimates hold inventory as Reserved without decrementing physical counts.',
                    ],
                    [
                        'title'   => '1-Click Convert to Invoice',
                        'content' => 'When a client approves an estimate, clicking Convert validates stock, decrements physical quantity, writes immutable stock movements, and generates an official invoice.',
                    ],
                    [
                        'title'   => '10% Tax-Exclusive VAT Calculation',
                        'content' => 'Formula:\n• Net Subtotal = Sum(Line Items) - Discounts\n• Tax Amount (10% VAT) = Net Subtotal × 0.10\n• Grand Total = Net Subtotal + Tax Amount',
                    ],
                    [
                        'title'   => 'Printable A4 Vector Invoices',
                        'content' => 'Export high-resolution A4 tax invoices with full customer bill-to metadata, line item tables, and tax breakdowns, or print 80mm ESC/POS thermal receipts.',
                    ],
                ],
                'tips' => [
                    'Set quotation validity to 14 days to mitigate wholesale price fluctuations.',
                    'Use the laser print preview simulation to verify invoice lines before physical printing.',
                ]
            ],
            [
                'id'          => 'purchasing-procurement',
                'title'       => 'Purchasing & Vendors',
                'tagline'     => 'Supplier POs & Auto-Replenishment',
                'icon'        => 'fa-truck-ramp-box',
                'description' => 'Supplier master directory, purchase orders, velocity-based restocking, and goods receiving ledgers.',
                'sections'    => [
                    [
                        'title'   => 'Supplier Directory',
                        'content' => 'Maintain vendor records including contact persons, delivery terms, payment terms, and historical unit cost agreements.',
                    ],
                    [
                        'title'   => 'Purchase Order Lifecycle',
                        'content' => 'Create POs in Draft state, submit to vendor as Ordered, track in transit as Shipped, and reconcile incoming shipments upon receipt to increment On Hand stock.',
                    ],
                    [
                        'title'   => 'Smart Automated Replenishment',
                        'content' => 'The system computes weekly sales velocity against minimum reorder levels and suggests purchase orders automatically in 1 click.',
                    ],
                ],
                'tips' => [
                    'Inspect incoming shipments against PO lines before marking goods as Received.',
                    'Review supplier lead times quarterly to fine-tune automated reorder points.',
                ]
            ],
            [
                'id'          => 'locations-branches',
                'title'       => 'Locations & Branches',
                'tagline'     => 'Multi-Warehouse & Store Logistics',
                'icon'        => 'fa-store',
                'description' => 'Multi-location inventory tracking, warehouse zones, inter-branch stock transfers, and click-and-collect.',
                'sections'    => [
                    [
                        'title'   => 'Store Branch Hierarchy',
                        'content' => 'Configure multiple retail stores and central warehouses with specific addresses, operating hours, and localized inventory ledgers.',
                    ],
                    [
                        'title'   => 'Inter-Branch Stock Transfers',
                        'content' => 'Move inventory between warehouses and retail showrooms with transit status tracking and dual-sign-off verification.',
                    ],
                    [
                        'title'   => 'Omnichannel Click-and-Collect',
                        'content' => 'Customers can order online and select in-store pickup at their preferred branch or request home dispatch via local couriers.',
                    ],
                ],
                'tips' => [
                    'Assign dedicated staff members to specific store branches to ensure operational accountability.',
                    'Ensure stock transfer manifests are verified upon physical arrival at the destination branch.',
                ]
            ],
            [
                'id'          => 'kitting-bundling',
                'title'       => 'Kitting & Bundles',
                'tagline'     => 'Combo Packages & Auto-Deduction',
                'icon'        => 'fa-cubes',
                'description' => 'Assembled multi-item combo packs, gift boxes, and dynamic sub-component stock deduction.',
                'sections'    => [
                    [
                        'title'   => 'Bundle Package Definition',
                        'content' => 'Create combo packages with unique SKUs, bundled pricing, and assigned individual sub-component quantities.',
                    ],
                    [
                        'title'   => 'Dynamic Bundle Stock Availability',
                        'content' => 'Kit availability is calculated dynamically based on the lowest common denominator of individual sub-components in stock.',
                    ],
                    [
                        'title'   => 'Automatic Component Decrement',
                        'content' => 'Selling 1 bundle automatically decrements all included sub-variants in the inventory ledger inside an atomic database transaction.',
                    ],
                ],
                'tips' => [
                    'Use bundles for seasonal promotions to increase average order values.',
                    'Verify individual component stock before launching high-volume bundle promotions.',
                ]
            ],
            [
                'id'          => 'financial-valuation',
                'title'       => 'Reports & Valuation',
                'tagline'     => 'Balance Sheet Valuation & Margins',
                'icon'        => 'fa-chart-pie',
                'description' => 'Real-time financial asset valuation (Purchased Cost vs Resale Retail Value), gross margin %, and Role-Pulse.',
                'sections'    => [
                    [
                        'title'   => 'Balance Sheet Asset Valuation',
                        'content' => '• Purchased Value (Cost Basis) = Total Stock × Unit Cost Price.\n• Resale Value (Retail Asset) = Total Stock × Unit Retail Price.\n• Potential Gross Profit = Resale Value - Purchased Value.',
                    ],
                    [
                        'title'   => 'Gross Profit Margin Percentage',
                        'content' => 'Formula: Margin % = (Gross Profit ÷ Resale Value) × 100.\nMonitored across product categories and individual brands.',
                    ],
                    [
                        'title'   => 'Role-Pulse Operational Analytics',
                        'content' => 'Real-time dashboard tracking cashier scanning speed, payment method distribution (Cash, Card, KHQR), and hourly peak traffic.',
                    ],
                ],
                'tips' => [
                    'Export financial valuation reports at month-end for accounting reconciliations.',
                    'Monitor gross margin by category to identify top performing merchandise.',
                ]
            ],
            [
                'id'          => 'security-rbac',
                'title'       => 'Security & RBAC',
                'tagline'     => 'Workforce Roles & Audit Trail',
                'icon'        => 'fa-shield-halved',
                'description' => 'Role-based access guards, employee HR profiles, session security, and immutable audit logs.',
                'sections'    => [
                    [
                        'title'   => '4-Tier Access Control',
                        'content' => 'Every action is guarded by role middleware ensuring Cashiers, Staff, Managers, and Admins can only perform authorized tasks.',
                    ],
                    [
                        'title'   => 'Employee Workforce Management',
                        'content' => 'Maintain employee records, job positions, base salaries, hire dates, and active system login associations.',
                    ],
                    [
                        'title'   => 'Immutable System Audit Logs',
                        'content' => 'The system records every stock adjustment, price change, invoice void, and user permission modification with before/after JSON diffs, employee ID, and IP address.',
                    ],
                ],
                'tips' => [
                    'Review audit logs weekly for unusual inventory corrections or voided invoices.',
                    'Deactivate employee accounts immediately upon staff departure.',
                ]
            ],
            [
                'id'          => 'pos-shifts',
                'title'       => 'POS & Cash Shifts',
                'tagline'     => 'Cash Drawer Sessions & Z-Reports',
                'icon'        => 'fa-cash-register',
                'description' => 'Cash drawer session opening floats, midday safe cash drops, and closing Z-Report reconciliation.',
                'sections'    => [
                    [
                        'title'   => 'Opening Cash Shift',
                        'content' => 'At the start of a shift, the cashier enters the counted opening float (e.g. $100.00 in small bills) to unlock the register.',
                    ],
                    [
                        'title'   => 'Midday Cash Safe Drops',
                        'content' => 'Transfer excess cash to the store safe during high-volume hours to minimize cash drawer exposure.',
                    ],
                    [
                        'title'   => 'Closing Shift & Z-Report',
                        'content' => 'Count physical cash at end of day. The system calculates expected cash (Opening Float + Cash Sales - Cash Drops) and reports exact overage or shortage.',
                    ],
                ],
                'tips' => [
                    'Perform cash drops whenever drawer cash exceeds $1,000.00.',
                    'Require manager sign-off on any closing cash variance exceeding $5.00.',
                ]
            ],
        ];

        $popularTopics = [
            '4-Tier Quantity Model', 'On Hand vs Available', 'Quotation Estimates', '1-Click Invoice Convert',
            '10% VAT Tax Formula', 'Purchased Value Valuation', 'Resale Value Asset', 'Gross Profit Margin',
            '2D Size Color Matrix', 'Barcode Scanning', 'Low Stock Thresholds', 'Z-Report Reconciliation',
            'Cash Drawer Drops', 'VIP Loyalty Points', 'Customer CRM Lookup', 'Supplier Purchase Orders',
            'Smart Restock Forecast', 'Multi-Store Branches', 'Stock Transfers', 'Combo Kit Bundling',
            'Immutable Audit Logs', '4-Tier RBAC Access', 'A4 PDF Tax Invoice', 'Thermal ESC/POS Receipts'
        ];

        if ($request->wantsJson() && !$request->has('html')) {
            return $this->successResponse([
                'title'          => 'Store Stock & Point-of-Sale MIS — Help Centre & Operations Guide',
                'tagline'        => 'Official shadcn-templ style Knowledge Base & Operations Manual',
                'total_topics'   => count($categories),
                'categories'     => $categories,
                'popular_topics' => $popularTopics,
            ], 'Help Centre Knowledge Base data retrieved');
        }

        return response($this->buildShadcnTemplHtml($categories, $popularTopics), 200)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Render the official shadcn-templ style documentation layout.
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
    <title>Components &amp; Guides | Store Stock &amp; POS MIS</title>
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
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: var(--background);
            color: var(--foreground);
            line-height: 1.5;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Header ── */
        .site-header {
            position: sticky;
            top: 0;
            z-index: 50;
            width: 100%;
            border-bottom: 1px solid var(--border);
            background-color: var(--background);
            backdrop-filter: blur(8px);
        }
        .header-container {
            display: flex;
            height: 56px;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            max-width: 1440px;
            margin: 0 auto;
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
            font-size: 11px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: var(--radius);
            background: var(--muted);
            color: var(--muted-foreground);
            border: 1px solid var(--border);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
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
            width: 220px;
            justify-content: space-between;
            transition: border-color 0.15s ease;
        }
        .search-trigger-btn:hover { border-color: var(--ring); }
        .kbd-badge {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 10px;
            font-weight: 600;
            background: var(--background);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1px 4px;
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
        }
        .theme-toggle-btn:hover { background: var(--accent); }

        /* ── Layout Container ── */
        .layout-container {
            max-width: 1440px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 240px 1fr 220px;
            min-height: calc(100vh - 56px);
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
            margin-bottom: 12px;
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
            padding: 7px 10px;
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
            padding: 32px 40px;
            max-width: 860px;
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
            font-size: 30px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--foreground);
            margin-bottom: 8px;
        }
        .doc-description {
            font-size: 15px;
            color: var(--muted-foreground);
            line-height: 1.6;
            margin-bottom: 28px;
        }

        /* ── Card Component Previews ── */
        .component-card {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: var(--card);
            margin-bottom: 32px;
            overflow: hidden;
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
            padding: 24px 20px;
        }
        .section-block {
            margin-bottom: 24px;
        }
        .section-block:last-child { margin-bottom: 0; }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--foreground);
            margin-bottom: 8px;
        }
        .section-text {
            font-size: 13.5px;
            color: var(--muted-foreground);
            line-height: 1.65;
            white-space: pre-line;
        }

        .tips-callout {
            border: 1px solid var(--border);
            border-left: 3px solid var(--primary);
            border-radius: var(--radius);
            padding: 14px 18px;
            background: var(--muted);
            margin-top: 24px;
        }
        .tips-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--foreground);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .tips-list {
            list-style: disc;
            margin-left: 18px;
            font-size: 12.5px;
            color: var(--muted-foreground);
            line-height: 1.55;
        }

        /* ── Popular Topics Cloud ── */
        .topics-section {
            margin-top: 40px;
            border-top: 1px solid var(--border);
            padding-top: 28px;
        }
        .topics-pills-wrap {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }
        .topic-badge {
            background: var(--muted);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 5px 10px;
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
            padding: 24px 16px;
        }
        .toc-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--foreground);
            margin-bottom: 12px;
        }
        .toc-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 12px;
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

        /* ── Search Dialog Modal (⌘K) ── */
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
            width: 560px;
            max-width: 90vw;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        .search-input-box {
            display: flex;
            align-items: center;
            padding: 14px 16px;
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
            max-height: 320px;
            overflow-y: auto;
            padding: 8px;
        }
        .search-result-item {
            padding: 10px 12px;
            border-radius: var(--radius);
            font-size: 13px;
            color: var(--foreground);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .search-result-item:hover { background: var(--accent); }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 1024px) {
            .layout-container { grid-template-columns: 220px 1fr; }
            .sidebar-right { display: none; }
        }
        @media (max-width: 768px) {
            .layout-container { grid-template-columns: 1fr; }
            .sidebar-left { display: none; }
            .main-content { padding: 20px 16px; }
            .search-trigger-btn { width: 140px; }
        }
    </style>
</head>
<body>
    <header class='site-header'>
        <div class='header-container'>
            <a href='/guide' class='brand-text-block'>
                <span class='brand-text'>STORE STOCK &amp; POS</span>
                <span class='brand-tag'>MIS GUIDE</span>
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

    <div class='layout-container'>
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
                <span>Components</span>
                <span>/</span>
                <span id='breadcrumb-active' style='color: var(--foreground); font-weight: 600;'>Getting Started</span>
            </div>

            <h1 class='doc-title' id='doc-title'>Getting Started</h1>
            <p class='doc-description' id='doc-description'>Comprehensive onboarding guide, architecture topology, access tiers, and standard response envelopes.</p>

            <div class='component-card'>
                <div class='component-card-header'>
                    <div class='component-card-title'>
                        <i class='fa-solid fa-book-open'></i>
                        <span id='component-title'>System Specification &amp; Workflow</span>
                    </div>
                    <span class='brand-tag'>Operational Manual</span>
                </div>
                <div class='component-card-body' id='component-body'>
                    <!-- Injected dynamically -->
                </div>
            </div>

            <div class='topics-section'>
                <div class='sidebar-heading' style='padding: 0;'>Popular Topics &amp; Terminology</div>
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

    <!-- Search Modal Dialog (⌘K) -->
    <div class='search-modal-backdrop' id='search-modal' onclick='closeSearchModal(event)'>
        <div class='search-dialog' onclick='event.stopPropagation()'>
            <div class='search-input-box'>
                <i class='fa-solid fa-magnifying-glass' style='color: var(--muted-foreground);'></i>
                <input type='text' id='search-input' class='search-dialog-input' placeholder='Type a topic, matrix rule, or tax calculation...'>
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
            let bodyHtml = `<p style='font-size: 14px; color: var(--foreground); font-weight: 500; margin-bottom: 24px; line-height: 1.6;'>\${data.description}</p>`;
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
                    <div class='tips-title'><i class='fa-solid fa-lightbulb' style='color: #eab308;'></i> Practical Tips &amp; Rules</div>
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
