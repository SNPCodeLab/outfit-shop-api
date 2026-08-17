<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HelpCentreGuideController extends BaseApiController
{
    /**
     * Display the comprehensive SalesBinder-style Help Centre and API Documentation Guide.
     * Serves HTML or JSON depending on request Accept header.
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
                'title'       => 'Getting Started',
                'description' => 'Quickstart guide, base API gateway URLs, authentication workflow, and error envelopes.',
                'articles'    => [
                    'Base URL: https://api.kesararamwithdigital.tech/api/v1',
                    'Authentication: Bearer <sanctum_token> via Authorization header',
                    'Standard Response Envelopes: { success: true, data: {...}, message: "..." }',
                    'Rate Limiting: 120 req/min for authenticated users, 10 req/min for login',
                ],
            ],
            [
                'id'          => 'accounts',
                'icon'        => 'fa-users',
                'title'       => 'Accounts & Customers',
                'description' => 'Customer CRM profiles, VIP phone lookups, private account notes, and loyalty point balances.',
                'articles'    => [
                    'Customer Registration & Phone Search (/customers?search=012888999)',
                    'Customer VIP Loyalty Points & Tier Calculation (/customers/{id}/loyalty)',
                    'Points Redemption at POS Checkout (/customers/{id}/redeem-points)',
                    'Customer Storefront Wishlists (/wishlist & /wishlist/toggle)',
                ],
            ],
            [
                'id'          => 'inventory',
                'icon'        => 'fa-boxes-stacked',
                'title'       => 'Inventory Management',
                'description' => 'Real-time stock levels, SalesBinder 2D size/color matrices, low inventory notifications, and batch tracking.',
                'articles'    => [
                    'SalesBinder 4-Tier Lifecycle: On-Hand, Reserved, Available, Incoming',
                    '2D Size × Color Matrix Grid (/products/{id}/matrix)',
                    'Continuous Barcode Scanner Lookup (/variants/barcode/{barcode})',
                    'Low Stock Forecasts & Reorder Levels (/variants/low-stock)',
                    'FMCG FIFO Expiry Tracking & Lot Numbers (/inventory/expiring-soon)',
                ],
            ],
            [
                'id'          => 'documents',
                'icon'        => 'fa-file-invoice-dollar',
                'title'       => 'Invoices & Estimates',
                'description' => 'Billing documents, quotation estimates, 1-click invoice conversion, and A4 printable PDF views.',
                'articles'    => [
                    'Creating Quotation Estimates (/estimates)',
                    '1-Click Convert Estimate to Invoice (/estimates/{id}/convert)',
                    '10.00% Tax-Exclusive VAT Calculation Formula',
                    'Printable A4 / PDF Tax Invoice View (/sales/{id}/invoice-pdf)',
                    'Thermal 80mm ESC/POS Receipt Format (/sales/{id}/receipt-thermal)',
                ],
            ],
            [
                'id'          => 'purchasing',
                'icon'        => 'fa-truck-ramp-box',
                'title'       => 'Purchasing & Suppliers',
                'description' => 'Supplier vendor records, purchase orders, incoming stock replenishment, and cost tracking.',
                'articles'    => [
                    'Master Suppliers Directory (/suppliers)',
                    'Creating Procurement Purchase Orders (/purchases)',
                    'Automated Restock Forecast & Auto-PO (/purchases/auto-generate)',
                    'Immutable Stock Movement Audit Ledger (/stock-movements)',
                ],
            ],
            [
                'id'          => 'locations',
                'icon'        => 'fa-store',
                'title'       => 'Locations & Store Branches',
                'description' => 'Multi-warehouse facilities, flagship retail stores, and isolated per-branch stock levels.',
                'articles'    => [
                    'Store Branch Locations Directory (/branches)',
                    'Per-Branch Stock Balances (/branches/{id}/stock)',
                    'Omnichannel Shipping & Dispatch Tracking (/shipping/orders)',
                    'Click-and-Collect In-Store Fulfillment (/shipping/create)',
                ],
            ],
            [
                'id'          => 'kitting',
                'icon'        => 'fa-cubes',
                'title'       => 'Kitting & Bundling',
                'description' => 'Combine and assemble multi-item packages and gift sets with automatic piece inventory deduction.',
                'articles'    => [
                    'Product Bundle Package Catalog (/bundles)',
                    'Creating Assembled Kits & Combos (/bundles)',
                    'Real-Time Dynamic Bundle Stock Availability Calculation',
                ],
            ],
            [
                'id'          => 'reports',
                'icon'        => 'fa-chart-pie',
                'title'       => 'Reports & Financial Valuation',
                'description' => 'Purchased cost basis vs resale retail valuation, gross profit margins, and Role-Pulse charts.',
                'articles'    => [
                    'SalesBinder Valuation: Purchased Value vs Resale Value (/inventory/statistics)',
                    'Gross Profit & Margin Percentage Breakdown',
                    'Role-Pulse Live Analytics: Cashier Speed & Payment Distribution (/dashboard/role-pulse)',
                    'Admin Master Command Pulse & Timesheets (/admin/master-pulse)',
                ],
            ],
            [
                'id'          => 'settings',
                'icon'        => 'fa-shield-halved',
                'title'       => 'Security & User Permissions',
                'description' => '4-Tier Role-Based Access Control (Admin, Manager, Cashier, Staff) and immutable audit logs.',
                'articles'    => [
                    '4-Tier RBAC Ladder: Admin, Manager, Cashier, Staff',
                    'Employee HR Profiles & Salary Records (/employees)',
                    'User Credential Provisioning (/auth/register)',
                    'Immutable System Audit Logs & JSON Diffs (/audit-logs)',
                ],
            ],
        ];

        $popularTopics = [
            'Archive', 'Receiving Stock', 'Pricing Tiers', 'Reports', 'Customers', 'Financial Valuation',
            'Variations Matrix', 'Quantity Overview', 'Estimates', 'Invoices', '10% VAT Tax', 'Bakong KHQR',
            'POS Shifts', 'Z-Report', 'Audit Logs', 'Barcode Scanning', 'Kitting', 'Suppliers', 'Permissions',
            'Restock Forecast', 'Units of Measure', 'Thermal Receipts', 'Cloudinary CDN', 'Sanctum Tokens'
        ];

        // Return JSON if requested
        if ($request->wantsJson() && !$request->has('html')) {
            return $this->successResponse([
                'title'          => 'KhmeRiel MIS & POS — SalesBinder Help Centre & API Guide',
                'tagline'        => 'Find clear answers, step-by-step guides, and practical tips to help you get more from KhmeRiel.',
                'portal_url'     => 'https://app.kesararamwithdigital.tech',
                'api_gateway'    => 'https://api.kesararamwithdigital.tech/api/v1',
                'categories'     => $categories,
                'popular_topics' => $popularTopics,
            ], 'Help Centre Guide data retrieved');
        }

        // Otherwise return unified, clean, single-tone HTML Web Page
        return response($this->buildHtmlPage($categories, $popularTopics), 200)
            ->header('Content-Type', 'text/html');
    }

    /**
     * Build clean, ultra-professional, single-tone monochrome Help Centre Web UI.
     *
     * @param array $categories
     * @param array $popularTopics
     * @return string
     */
    protected function buildHtmlPage(array $categories, array $popularTopics): string
    {
        $categoriesHtml = '';
        foreach ($categories as $cat) {
            $articlesHtml = '';
            foreach ($cat['articles'] as $art) {
                $articlesHtml .= "<li style='margin-bottom: 6px; font-size: 13px; color: #475569;'><span style='color: #0f172a; margin-right: 6px;'>•</span>{$art}</li>";
            }

            $categoriesHtml .= "
            <div class='kb-card'>
                <div class='kb-card-header'>
                    <div class='kb-icon-box'><i class='fa-solid {$cat['icon']}'></i></div>
                    <h3 class='kb-card-title'>{$cat['title']}</h3>
                </div>
                <p class='kb-card-desc'>{$cat['description']}</p>
                <ul class='kb-article-list'>{$articlesHtml}</ul>
                <div class='kb-card-footer'>
                    <a href='#{$cat['id']}' class='kb-browse-link'>Browse documentation <i class='fa-solid fa-arrow-right'></i></a>
                </div>
            </div>";
        }

        $topicsHtml = '';
        foreach ($popularTopics as $topic) {
            $topicsHtml .= "<span class='topic-pill'>{$topic}</span>";
        }

        return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Help Centre & Developer Guide | KhmeRiel MIS & POS</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'>
    <style>
        :root {
            --bg-canvas: #f8fafc;
            --bg-surface: #ffffff;
            --border-color: #e2e8f0;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary: #0f172a;
            --radius: 3px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-canvas);
            color: var(--text-main);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text-main);
        }
        .nav-logo { height: 32px; object-fit: contain; }
        .nav-title { font-size: 15px; font-weight: 700; letter-spacing: -0.01em; }
        .nav-badge {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 3px 8px;
            border-radius: var(--radius);
            font-size: 11px;
            font-weight: 600;
        }
        .nav-links { display: flex; gap: 16px; align-items: center; }
        .nav-btn {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
            text-decoration: none;
            padding: 6px 12px;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            background: #ffffff;
            transition: all 0.15s ease;
        }
        .nav-btn:hover { background: #f8fafc; border-color: #cbd5e1; }
        .nav-btn-primary {
            background: var(--primary);
            color: #ffffff;
            border-color: var(--primary);
        }
        .nav-btn-primary:hover { background: #1e293b; color: #ffffff; }

        .hero-section {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 60px 24px 50px 24px;
            text-align: center;
        }
        .hero-pretitle {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            margin-bottom: 12px;
        }
        .hero-title {
            font-size: 38px;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 14px;
            color: var(--text-main);
        }
        .hero-subtitle {
            font-size: 16px;
            color: var(--text-muted);
            max-width: 620px;
            margin: 0 auto 30px auto;
        }
        .search-box {
            max-width: 580px;
            margin: 0 auto;
            position: relative;
        }
        .search-input {
            width: 100%;
            padding: 14px 18px 14px 44px;
            font-size: 14px;
            border: 1px solid #cbd5e1;
            border-radius: var(--radius);
            outline: none;
            background: #ffffff;
            transition: border-color 0.15s ease;
        }
        .search-input:focus { border-color: var(--primary); ring: 1px solid var(--primary); }
        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
        }

        .main-container {
            max-width: 1180px;
            margin: 40px auto 60px auto;
            padding: 0 20px;
        }
        .section-header {
            margin-bottom: 24px;
        }
        .section-pretitle {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            margin-bottom: 4px;
        }
        .section-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .kb-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 50px;
        }
        .kb-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 24px;
            display: flex;
            flex-direction: column;
            transition: border-color 0.15s ease;
        }
        .kb-card:hover { border-color: #94a3b8; }
        .kb-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }
        .kb-icon-box {
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: var(--text-main);
        }
        .kb-card-title {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .kb-card-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 16px;
            line-height: 1.45;
        }
        .kb-article-list {
            list-style: none;
            margin-bottom: 20px;
            flex-grow: 1;
        }
        .kb-card-footer {
            border-top: 1px solid #f1f5f9;
            padding-top: 14px;
        }
        .kb-browse-link {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-main);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .kb-browse-link:hover { text-decoration: underline; }

        .topics-card {
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 30px;
            margin-bottom: 40px;
        }
        .topics-flex {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 16px;
        }
        .topic-pill {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .topic-pill:hover { background: #0f172a; color: #ffffff; border-color: #0f172a; }

        .support-banner {
            background: #0f172a;
            color: #ffffff;
            border-radius: var(--radius);
            padding: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .support-title { font-size: 20px; font-weight: 700; margin-bottom: 6px; }
        .support-text { font-size: 13px; color: #94a3b8; }
        .support-actions { display: flex; gap: 12px; }
        .support-btn {
            background: #ffffff;
            color: #0f172a;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: var(--radius);
        }
        .support-btn-outline {
            background: transparent;
            color: #ffffff;
            border: 1px solid #334155;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            padding: 10px 18px;
            border-radius: var(--radius);
        }

        .footer {
            border-top: 1px solid var(--border-color);
            background: #ffffff;
            padding: 30px 20px;
            text-align: center;
            font-size: 12px;
            color: var(--text-muted);
        }
    </style>
</head>
<body>
    <nav class='navbar'>
        <a href='/guide' class='nav-brand'>
            <img src='https://res.cloudinary.com/od8t271n/image/upload/v1786898754/KhmerRiel.png' alt='Logo' class='nav-logo'>
            <span class='nav-title'>KhmeRiel MIS & POS</span>
            <span class='nav-badge'>Help Centre</span>
        </a>
        <div class='nav-links'>
            <a href='https://app.kesararamwithdigital.tech' target='_blank' class='nav-btn'>Storefront App</a>
            <a href='/api/v1/health' class='nav-btn'>API Status</a>
            <a href='/postman/production.json' download class='nav-btn nav-btn-primary'><i class='fa-solid fa-download' style='margin-right: 6px;'></i>Postman Suite</a>
        </div>
    </nav>

    <header class='hero-section'>
        <div class='hero-pretitle'>Help Centre & Developer Knowledge Base</div>
        <h1 class='hero-title'>How can we help?</h1>
        <p class='hero-subtitle'>Find clear answers, step-by-step guides, and practical tips to help you get more from KhmeRiel MIS & POS.</p>
        <div class='search-box'>
            <i class='fa-solid fa-magnifying-glass search-icon'></i>
            <input type='text' id='kb-search' class='search-input' placeholder='Search guides, APIs, inventory matrices, or tax rules...'>
        </div>
    </header>

    <main class='main-container'>
        <div class='section-header'>
            <div class='section-pretitle'>Explore by Topic</div>
            <h2 class='section-title'>Browse categories</h2>
        </div>

        <div class='kb-grid'>
            {$categoriesHtml}
        </div>

        <div class='topics-card'>
            <div class='section-pretitle'>Find Something Specific</div>
            <h2 class='section-title' style='font-size: 20px;'>Popular topics</h2>
            <div class='topics-flex'>
                {$topicsHtml}
            </div>
        </div>

        <div class='support-banner'>
            <div>
                <h3 class='support-title'>Still need a hand?</h3>
                <p class='support-text'>Our developer support and store operations team is ready to help you find an answer.</p>
            </div>
            <div class='support-actions'>
                <a href='mailto:support@kesararamwithdigital.tech' class='support-btn'><i class='fa-solid fa-envelope' style='margin-right: 6px;'></i>Contact Support</a>
                <a href='/api/v1/guide' class='support-btn-outline'><i class='fa-solid fa-code' style='margin-right: 6px;'></i>API JSON Directory</a>
            </div>
        </div>
    </main>

    <footer class='footer'>
        <div>KhmeRiel Clothing MIS & POS • Architecture inspired by SalesBinder & Ralph Lauren</div>
        <div style='margin-top: 4px; font-family: monospace; font-size: 11px;'>Base Gateway: https://api.kesararamwithdigital.tech/api/v1</div>
    </footer>

    <script>
        document.getElementById('kb-search').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            document.querySelectorAll('.kb-card').forEach(card => {
                const text = card.innerText.toLowerCase();
                card.style.display = text.includes(query) ? 'flex' : 'none';
            });
        });
    </script>
</body>
</html>";
    }
}
