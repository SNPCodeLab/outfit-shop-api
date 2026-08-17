<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HelpCentreGuideController extends BaseApiController
{
    /**
     * Display the comprehensive shadcn-styled Help Centre and Knowledge Base.
     * Serves interactive HTML or structured JSON depending on request.
     *
     * @param Request $request
     * @return Response|JsonResponse
     */
    public function index(Request $request)
    {
        $categories = [
            [
                'id'          => 'getting-started',
                'icon'        => 'fa-rocket',
                'title'       => 'System Onboarding & Architecture',
                'tagline'     => 'Foundational architecture, access tiers, and operational rules.',
                'overview'    => 'The Store Stock & Point-of-Sale Information System is an enterprise Transaction Processing System (TPS) with embedded Management Information System (MIS) reporting. It manages real-time retail sales, omnichannel fulfillment, warehouse inventory, and automated purchasing.',
                'sections'    => [
                    [
                        'heading' => 'Core Operational Architecture',
                        'content' => 'The system is organized into a modular headless architecture where the backend manages relational integrity, audit tracking, and transactional ACID compliance across 48 operational tables.',
                    ],
                    [
                        'heading' => '4-Tier Access Model',
                        'content' => '1. Public / Guest (Catalog browsing, size charts, store showcase)\n2. Cashier & Staff (POS register, shifts, customer loyalty, barcode scanning)\n3. Manager (Inventory restock, pricing tiers, purchasing POs, promotions)\n4. Superadmin (HR workforce, system configurations, immutable audit trail)',
                    ],
                    [
                        'heading' => 'Standard Response Format',
                        'content' => 'All operations return uniform JSON envelopes containing success flags, localized human messages, and structured data payloads with HTTP status codes (200, 201, 401, 403, 422).',
                    ]
                ],
                'tips' => [
                    'Always verify user roles prior to attempting privileged manager operations.',
                    'Keep employee credentials secure and never share cashier register shift logins.',
                ]
            ],
            [
                'id'          => 'accounts',
                'icon'        => 'fa-users',
                'title'       => 'Customer Accounts & VIP Loyalty',
                'tagline'     => 'Customer CRM, VIP classification, store credit, and loyalty points.',
                'overview'    => 'Manage retail customer relationships, search profiles by telephone at checkout, track lifetime spending velocity, and reward loyal clients with automated VIP tiers.',
                'sections'    => [
                    [
                        'heading' => 'Customer Profile Management',
                        'content' => 'Register customer records with full name, phone number, email address, delivery location, and private credit notes. Cashiers can instantly search customers by telephone during checkout.',
                    ],
                    [
                        'heading' => 'VIP Loyalty Point Accrual',
                        'content' => 'Customers automatically earn 1 loyalty point for every \$1.00 spent. Point balances dynamically advance customer tier status (Bronze, Silver, Gold, Platinum).',
                    ],
                    [
                        'heading' => 'Point Redemption at Checkout',
                        'content' => 'Loyalty points can be redeemed at checkout for instant cash deductions. 100 points = \$5.00 discount on the transaction.',
                    ]
                ],
                'tips' => [
                    'Prompt walk-in clients for their mobile number at checkout to ensure loyalty points are credited.',
                    'VIP customers receive priority notification for seasonal collection drops.',
                ]
            ],
            [
                'id'          => 'inventory',
                'icon'        => 'fa-boxes-stacked',
                'title'       => 'Inventory & 2D Matrix Lifecycle',
                'tagline'     => '4-Tier quantity model, 2D size/color matrices, and barcode scanning.',
                'overview'    => 'Maintain inventory accuracy across the enterprise using the SalesBinder 4-tier quantity lifecycle, 2D variant matrices, barcode label generation, and batch expiry tracking.',
                'sections'    => [
                    [
                        'heading' => 'The 4-Tier Quantity Model',
                        'content' => '• On Hand: Physical stock counted in the warehouse or store.\n• Reserved: Units committed to open quotes, estimates, and pending orders.\n• Available: Immediate net sellable quantity = On Hand - Reserved.\n• Incoming: Units on open purchase orders from suppliers.',
                    ],
                    [
                        'heading' => '2D Size × Color Matrix',
                        'content' => 'Products with multiple variations are managed through an intuitive 2D grid matrix (e.g. Small, Medium, Large vs Black, White, Navy) for instant stock entry and wholesale mass ordering.',
                    ],
                    [
                        'heading' => 'Barcode Scanning & Reorder Thresholds',
                        'content' => 'Each SKU carries a unique barcode. Scanning immediately retrieves stock balances. When available quantity falls below the reorder level, the system triggers restock alerts.',
                    ]
                ],
                'tips' => [
                    'Run weekly cycle counts on top 20% velocity items to prevent shrinkage.',
                    'Check low stock alerts daily to maintain optimal inventory turnover.',
                ]
            ],
            [
                'id'          => 'documents',
                'icon'        => 'fa-file-invoice-dollar',
                'title'       => 'Invoices, Estimates & Billing',
                'tagline'     => 'Quotation estimates, 1-click invoice conversion, and A4 PDF printing.',
                'overview'    => 'Create formal client quotations, convert approved quotes to active invoices in 1 click, calculate 10% VAT tax, and render high-resolution printable documents.',
                'sections'    => [
                    [
                        'heading' => 'Estimates & Quotes Workflow',
                        'content' => 'Generate formal quotation estimates with custom line items, unit prices, discounts, and expiration dates. Estimates reserve inventory without decrementing physical counts.',
                    ],
                    [
                        'heading' => '1-Click Convert to Invoice',
                        'content' => 'When a customer approves an estimate, clicking Convert instantly validates stock availability, locks inventory, decrements physical quantity, and generates an official invoice.',
                    ],
                    [
                        'heading' => '10% Tax-Exclusive VAT Calculation',
                        'content' => 'Formula: Net Subtotal = Total Item Lines - Discounts.\nTax Amount (10%) = Net Subtotal × 0.10.\nGrand Total = Net Subtotal + Tax Amount.',
                    ],
                    [
                        'heading' => 'Printable Documents & Thermal Receipts',
                        'content' => 'Export high-resolution A4 tax invoices with full customer bill-to metadata or print compact 80mm ESC/POS thermal receipts with barcode tracking.',
                    ]
                ],
                'tips' => [
                    'Set estimate expiry dates to 14 days to protect against wholesale price fluctuations.',
                    'Use the built-in print preview animation to verify invoice line items before printing.',
                ]
            ],
            [
                'id'          => 'purchasing',
                'icon'        => 'fa-truck-ramp-box',
                'title'       => 'Purchasing & Vendor Procurement',
                'tagline'     => 'Supplier directory, purchase orders, and automated replenishment.',
                'overview'    => 'Manage vendor relationships, create purchase orders, track incoming shipments, and automate restocking based on sales velocity.',
                'sections'    => [
                    [
                        'heading' => 'Supplier Master Directory',
                        'content' => 'Maintain vendor records including contact persons, delivery terms, payment terms, and historical unit cost agreements.',
                    ],
                    [
                        'heading' => 'Purchase Order Lifecycle',
                        'content' => 'Create POs in Draft state, submit to vendor as Ordered, track in transit as Shipped, and perform goods receiving reconciliation to increment On Hand stock.',
                    ],
                    [
                        'heading' => 'Automated Smart Replenishment',
                        'content' => 'The system analyzes weekly sales velocity against minimum reorder levels and generates suggested purchase orders in 1 click.',
                    ]
                ],
                'tips' => [
                    'Always inspect incoming shipments against PO line items before marking stock as Received.',
                    'Review supplier lead times quarterly to fine-tune reorder points.',
                ]
            ],
            [
                'id'          => 'locations',
                'icon'        => 'fa-store',
                'title'       => 'Multi-Store Locations & Branches',
                'tagline'     => 'Warehouse zones, retail branches, and omnichannel fulfillment.',
                'overview'    => 'Isolate inventory counts across multiple store locations, central warehouses, and manage stock transfers and click-and-collect orders.',
                'sections'    => [
                    [
                        'heading' => 'Store Branch Hierarchy',
                        'content' => 'Configure multiple branches with specific addresses, operating hours, and localized inventory ledgers.',
                    ],
                    [
                        'heading' => 'Inter-Branch Stock Transfers',
                        'content' => 'Move inventory between central warehouses and retail showrooms with transit status tracking and dual-authorization verification.',
                    ],
                    [
                        'heading' => 'Omnichannel Click-and-Collect',
                        'content' => 'Customers can order online and select in-store pickup at their preferred branch or request home delivery via local couriers.',
                    ]
                ],
                'tips' => [
                    'Assign dedicated staff members to specific store branches to ensure accountability.',
                    'Ensure stock transfer manifests are verified upon arrival at the destination branch.',
                ]
            ],
            [
                'id'          => 'kitting',
                'icon'        => 'fa-cubes',
                'title'       => 'Kitting & Bundling Packages',
                'tagline'     => 'Multi-item gift sets, combo kits, and auto-stock deduction.',
                'overview'    => 'Combine individual product items into assembled combo kits (e.g. Shirt + Tie + Cufflinks) with automatic component inventory deduction upon sale.',
                'sections'    => [
                    [
                        'heading' => 'Bundle Definition',
                        'content' => 'Create bundle packages with unique SKUs, bundled pricing, and assigned individual sub-component quantities.',
                    ],
                    [
                        'heading' => 'Dynamic Kit Stock Availability',
                        'content' => 'Kit availability is calculated automatically based on the lowest common denominator of individual sub-components in stock.',
                    ],
                    [
                        'heading' => 'Automatic Component Decrement',
                        'content' => 'Selling 1 bundle kit automatically decrements all included sub-variants in the inventory ledger inside an atomic transaction.',
                    ]
                ],
                'tips' => [
                    'Use bundles for seasonal promotions to increase average transaction order value.',
                    'Verify individual component stock before launching high-volume bundle promotions.',
                ]
            ],
            [
                'id'          => 'reports',
                'icon'        => 'fa-chart-pie',
                'title'       => 'Financial Valuation & Reports',
                'tagline'     => 'Asset valuation, cost basis, gross margin, and cashier speed.',
                'overview'    => 'Gain real-time visibility into balance sheet inventory value, potential retail revenue, profit margins, and POS register velocity.',
                'sections'    => [
                    [
                        'heading' => 'Balance Sheet Asset Valuation',
                        'content' => '• Purchased Value (Cost Basis) = Total Stock × Unit Cost Price.\n• Resale Value (Retail Asset) = Total Stock × Unit Retail Price.\n• Potential Gross Profit = Resale Value - Purchased Value.',
                    ],
                    [
                        'heading' => 'Gross Margin Percentage',
                        'content' => 'Formula: Margin % = (Gross Profit ÷ Resale Value) × 100.\nMonitored across product categories and individual brands.',
                    ],
                    [
                        'heading' => 'Role-Pulse Operational Analytics',
                        'content' => 'Real-time dashboard tracking cashier scanning speed, payment method distribution (Cash, Card, KHQR), and hourly peak traffic.',
                    ]
                ],
                'tips' => [
                    'Export financial valuation reports at month-end for accounting reconciliations.',
                    'Monitor gross margin by category to identify top performing merchandise.',
                ]
            ],
            [
                'id'          => 'security',
                'icon'        => 'fa-shield-halved',
                'title'       => 'Security, RBAC & Audit Trail',
                'tagline'     => '4-Tier RBAC, employee profiles, and immutable audit logs.',
                'overview'    => 'Enforce strict role-based access control, manage employee HR profiles, and record every administrative change in an immutable audit ledger.',
                'sections'    => [
                    [
                        'heading' => '4-Tier Access Control',
                        'content' => 'Every action is guarded by role middleware ensuring Cashiers, Staff, Managers, and Admins can only perform authorized tasks.',
                    ],
                    [
                        'heading' => 'Employee Workforce Management',
                        'content' => 'Maintain employee records, job positions, base salaries, hire dates, and active system login associations.',
                    ],
                    [
                        'heading' => 'Immutable System Audit Logs',
                        'content' => 'The system records every stock adjustment, price change, invoice void, and user permission modification with before/after JSON diffs, employee ID, and IP address.',
                    ]
                ],
                'tips' => [
                    'Review audit logs weekly for unusual inventory corrections or voided invoices.',
                    'Deactivate employee accounts immediately upon staff departure.',
                ]
            ],
            [
                'id'          => 'pos-shifts',
                'icon'        => 'fa-cash-register',
                'title'       => 'POS Hardware & Cash Shifts',
                'tagline'     => 'Cash drawer sessions, midday cash drops, and closing Z-Reports.',
                'overview'    => 'Manage physical cash drawer operations, register opening floats, perform safe drops, and reconcile closing cash with automatic Z-Report calculations.',
                'sections'    => [
                    [
                        'heading' => 'Opening Cash Shift',
                        'content' => 'At start of shift, the cashier enters the counted opening float (e.g. \$100.00 in small bills) to open the register.',
                    ],
                    [
                        'heading' => 'Midday Cash Safe Drops',
                        'content' => 'Transfer excess cash to the store safe during high-volume hours to minimize cash drawer exposure.',
                    ],
                    [
                        'heading' => 'Closing Shift & Z-Report',
                        'content' => 'Count physical cash at end of day. The system calculates expected cash (Opening Float + Cash Sales - Cash Drops) and reports exact overage or shortage.',
                    ]
                ],
                'tips' => [
                    'Perform cash drops whenever drawer cash exceeds \$1,000.00.',
                    'Require manager sign-off on any closing cash variance exceeding \$5.00.',
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
                'title'          => 'Store Stock & Point-of-Sale MIS — Help Centre & Knowledge Base',
                'tagline'        => 'Find clear answers, step-by-step guides, and practical tips to help you get more from the system.',
                'total_topics'   => count($categories),
                'categories'     => $categories,
                'popular_topics' => $popularTopics,
            ], 'Help Centre Knowledge Base data retrieved');
        }

        return response($this->buildHtmlPage($categories, $popularTopics), 200)
            ->header('Content-Type', 'text/html');
    }

    /**
     * Build modern shadcn-styled Help Centre UI with drawer modal.
     *
     * @param array $categories
     * @param array $popularTopics
     * @return string
     */
    protected function buildHtmlPage(array $categories, array $popularTopics): string
    {
        $categoriesJson = json_encode($categories);

        $cardsHtml = '';
        foreach ($categories as $index => $cat) {
            $sectionsCount = count($cat['sections']);
            $cardsHtml .= "
            <div class='kb-card' data-id='{$cat['id']}' onclick='openTopicModal({$index})'>
                <div class='kb-card-header'>
                    <div class='kb-icon-wrap'><i class='fa-solid {$cat['icon']}'></i></div>
                    <div style='flex: 1;'>
                        <h3 class='kb-card-title'>{$cat['title']}</h3>
                        <span class='kb-badge'>{$sectionsCount} Guides</span>
                    </div>
                </div>
                <p class='kb-card-tagline'>{$cat['tagline']}</p>
                <p class='kb-card-desc'>{$cat['overview']}</p>
                <div class='kb-card-footer'>
                    <span class='kb-read-link'>Read complete guide <i class='fa-solid fa-arrow-right'></i></span>
                </div>
            </div>";
        }

        $topicsHtml = '';
        foreach ($popularTopics as $topic) {
            $topicsHtml .= "<button class='topic-pill' onclick='filterByTopic(\"" . htmlspecialchars($topic, ENT_QUOTES) . "\")'>" . htmlspecialchars($topic) . "</button>";
        }

        return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Help Centre & Operational Guide | Store Stock & POS MIS</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'>
    <style>
        :root {
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-700: #334155;
            --slate-900: #0f172a;
            --radius: 3px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--slate-50);
            color: var(--slate-900);
            line-height: 1.5;
            font-size: 13px;
            -webkit-font-smoothing: antialiased;
        }
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--slate-200);
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 40;
        }
        .brand-block {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--slate-900);
        }
        .brand-title {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: -0.02em;
            text-transform: uppercase;
        }
        .brand-badge {
            background: var(--slate-100);
            color: var(--slate-700);
            border: 1px solid var(--slate-200);
            padding: 2px 8px;
            border-radius: var(--radius);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
        }
        .nav-actions { display: flex; gap: 10px; align-items: center; }
        .nav-btn {
            font-size: 12px;
            font-weight: 600;
            color: var(--slate-700);
            text-decoration: none;
            padding: 6px 12px;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            background: #ffffff;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .nav-btn:hover { background: var(--slate-100); color: var(--slate-900); }
        .nav-btn-primary {
            background: var(--slate-900);
            color: #ffffff;
            border-color: var(--slate-900);
        }
        .nav-btn-primary:hover { background: var(--slate-700); color: #ffffff; }

        .hero {
            background: #ffffff;
            border-bottom: 1px solid var(--slate-200);
            padding: 48px 20px 40px 20px;
            text-align: center;
        }
        .hero-pre {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--slate-500);
            margin-bottom: 10px;
        }
        .hero-title {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--slate-900);
            margin-bottom: 10px;
        }
        .hero-subtitle {
            font-size: 15px;
            color: var(--slate-500);
            max-width: 600px;
            margin: 0 auto 24px auto;
        }
        .search-wrap {
            max-width: 540px;
            margin: 0 auto;
            position: relative;
        }
        .search-input {
            width: 100%;
            padding: 12px 16px 12px 42px;
            font-size: 14px;
            border: 1px solid var(--slate-300);
            border-radius: var(--radius);
            outline: none;
            background: #ffffff;
            color: var(--slate-900);
            transition: border-color 0.15s ease;
        }
        .search-input:focus { border-color: var(--slate-900); }
        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--slate-400);
            font-size: 14px;
        }

        .container {
            max-width: 1160px;
            margin: 36px auto 60px auto;
            padding: 0 16px;
        }
        .sec-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 20px;
        }
        .sec-pre {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--slate-500);
        }
        .sec-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--slate-900);
        }

        .kb-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 16px;
            margin-bottom: 40px;
        }
        .kb-card {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            padding: 22px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            transition: border-color 0.15s ease, transform 0.15s ease;
        }
        .kb-card:hover {
            border-color: var(--slate-400);
            transform: translateY(-2px);
        }
        .kb-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }
        .kb-icon-wrap {
            width: 38px;
            height: 38px;
            background: var(--slate-100);
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            color: var(--slate-900);
        }
        .kb-card-title {
            font-size: 15px;
            font-weight: 700;
            letter-spacing: -0.01em;
            color: var(--slate-900);
        }
        .kb-badge {
            display: inline-block;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--slate-500);
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            padding: 1px 6px;
            border-radius: var(--radius);
            margin-top: 2px;
        }
        .kb-card-tagline {
            font-size: 12px;
            font-weight: 600;
            color: var(--slate-700);
            margin-bottom: 8px;
        }
        .kb-card-desc {
            font-size: 12px;
            color: var(--slate-500);
            line-height: 1.45;
            margin-bottom: 16px;
            flex-grow: 1;
        }
        .kb-card-footer {
            border-top: 1px solid var(--slate-100);
            padding-top: 12px;
        }
        .kb-read-link {
            font-size: 12px;
            font-weight: 700;
            color: var(--slate-900);
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .topics-box {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 36px;
        }
        .topics-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 14px;
        }
        .topic-pill {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            color: var(--slate-700);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .topic-pill:hover {
            background: var(--slate-900);
            color: #ffffff;
            border-color: var(--slate-900);
        }

        /* ── Shadcn Topic Modal Drawer ── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 20px;
            animation: fadeIn 0.15s ease-out;
        }
        .modal-container {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            max-width: 720px;
            width: 100%;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
        .modal-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--slate-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--slate-50);
        }
        .modal-title-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .modal-body {
            padding: 24px;
            overflow-y: auto;
            flex-grow: 1;
        }
        .modal-close-btn {
            background: transparent;
            border: 1px solid var(--slate-200);
            width: 28px;
            height: 28px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: var(--slate-500);
            font-size: 13px;
        }
        .modal-close-btn:hover { background: var(--slate-200); color: var(--slate-900); }
        .guide-section {
            margin-bottom: 20px;
            border-bottom: 1px solid var(--slate-100);
            padding-bottom: 16px;
        }
        .guide-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .guide-heading {
            font-size: 14px;
            font-weight: 700;
            color: var(--slate-900);
            margin-bottom: 6px;
        }
        .guide-text {
            font-size: 13px;
            color: var(--slate-700);
            line-height: 1.6;
            white-space: pre-line;
        }
        .tips-box {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            padding: 14px 16px;
            margin-top: 20px;
        }

        .footer {
            border-top: 1px solid var(--slate-200);
            background: #ffffff;
            padding: 24px 16px;
            text-align: center;
            font-size: 12px;
            color: var(--slate-500);
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 640px) {
            .hero { padding: 32px 16px; }
            .hero-title { font-size: 26px; }
            .kb-grid { grid-template-columns: 1fr; }
            .modal-container { max-height: 92vh; }
        }
    </style>
</head>
<body>
    <nav class='navbar'>
        <div class='brand-block'>
            <span class='brand-title'>STORE STOCK &amp; POS</span>
            <span class='brand-badge'>HELP CENTRE</span>
        </div>
        <div class='nav-actions'>
            <a href='/api/v1/guide' class='nav-btn'><i class='fa-solid fa-code'></i> JSON Guide</a>
            <a href='/api/v1/health' class='nav-btn'><i class='fa-solid fa-signal'></i> Status</a>
        </div>
    </nav>

    <header class='hero'>
        <div class='hero-pre'>Knowledge Base &amp; Operations Manual</div>
        <h1 class='hero-title'>How can we help?</h1>
        <p class='hero-subtitle'>Find clear answers, step-by-step guides, and practical tips to help you get more from Store Stock &amp; Point-of-Sale MIS.</p>
        <div class='search-wrap'>
            <i class='fa-solid fa-magnifying-glass search-icon'></i>
            <input type='text' id='search-input' class='search-input' placeholder='Search operational workflows, inventory matrices, or tax rules...'>
        </div>
    </header>

    <main class='container'>
        <div class='sec-head'>
            <div>
                <div class='sec-pre'>Explore by Topic</div>
                <h2 class='sec-title'>Browse categories</h2>
            </div>
            <span style='font-size: 12px; color: var(--slate-500);' id='category-counter'>" . count($categories) . " categories available</span>
        </div>

        <div class='kb-grid' id='cards-container'>
            {$cardsHtml}
        </div>

        <div class='topics-box'>
            <div class='sec-pre'>Find Something Specific</div>
            <h2 class='sec-title' style='font-size: 17px; margin-top: 2px;'>Popular topics</h2>
            <div class='topics-grid'>
                {$topicsHtml}
            </div>
        </div>
    </main>

    <!-- Modal Drawer for Full Guide Reading -->
    <div class='modal-overlay' id='guide-modal' onclick='closeTopicModal(event)'>
        <div class='modal-container' onclick='event.stopPropagation()'>
            <div class='modal-header'>
                <div class='modal-title-wrap'>
                    <div class='kb-icon-wrap' id='modal-icon'><i class='fa-solid fa-book'></i></div>
                    <div>
                        <h3 class='kb-card-title' id='modal-title' style='font-size: 16px;'>Guide Title</h3>
                        <span class='kb-badge' id='modal-tagline'>Tagline</span>
                    </div>
                </div>
                <button class='modal-close-btn' onclick='closeTopicModal()'><i class='fa-solid fa-xmark'></i></button>
            </div>
            <div class='modal-body' id='modal-content'>
                <!-- Injected via JavaScript -->
            </div>
        </div>
    </div>

    <footer class='footer'>
        <div>Store Stock &amp; Point-of-Sale Management Information System • Operational Standard</div>
        <div style='margin-top: 4px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 11px; color: var(--slate-400);'>Enterprise Architecture v1.0.0</div>
    </footer>

    <script>
        const categoriesData = {$categoriesJson};

        function openTopicModal(index) {
            const data = categoriesData[index];
            if (!data) return;

            document.getElementById('modal-title').innerText = data.title;
            document.getElementById('modal-tagline').innerText = data.tagline;
            document.getElementById('modal-icon').innerHTML = `<i class=\"fa-solid \${data.icon}\"></i>`;

            let bodyHtml = `<p style=\"font-size: 13px; color: var(--slate-700); margin-bottom: 20px; font-weight: 500; line-height: 1.5;\">\${data.overview}</p>`;

            data.sections.forEach(sec => {
                bodyHtml += `
                <div class=\"guide-section\">
                    <h4 class=\"guide-heading\">\${sec.heading}</h4>
                    <p class=\"guide-text\">\${sec.content}</p>
                </div>`;
            });

            if (data.tips && data.tips.length > 0) {
                bodyHtml += `
                <div class=\"tips-box\">
                    <div style=\"font-weight: 700; font-size: 11px; text-transform: uppercase; color: var(--slate-700); margin-bottom: 6px;\"><i class=\"fa-solid fa-lightbulb\" style=\"color: #eab308; margin-right: 4px;\"></i> Practical Tips</div>
                    <ul style=\"list-style: disc; margin-left: 18px; font-size: 12px; color: var(--slate-600); line-height: 1.5;\">
                        \${data.tips.map(t => `<li>\${t}</li>`).join('')}
                    </ul>
                </div>`;
            }

            document.getElementById('modal-content').innerHTML = bodyHtml;
            document.getElementById('guide-modal').style.display = 'flex';
        }

        function closeTopicModal() {
            document.getElementById('guide-modal').style.display = 'none';
        }

        document.getElementById('search-input').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            let matchCount = 0;

            document.querySelectorAll('.kb-card').forEach(card => {
                const text = card.innerText.toLowerCase();
                const match = text.includes(query);
                card.style.display = match ? 'flex' : 'none';
                if (match) matchCount++;
            });

            document.getElementById('category-counter').innerText = `\${matchCount} categories found`;
        });

        function filterByTopic(topic) {
            document.getElementById('search-input').value = topic;
            document.getElementById('search-input').dispatchEvent(new Event('input'));
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeTopicModal();
        });
    </script>
</body>
</html>";
    }
}
