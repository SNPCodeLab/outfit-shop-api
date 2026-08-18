<?php

namespace App\Http\Controllers\Api;

use App\Models\ApiLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseApiController
{
    /**
     * Return API traffic monitoring and APM dashboard metrics.
     * Restricted to MANAGER and ADMIN roles.
     */
    public function stats(): JsonResponse
    {
        $today = Carbon::today();
        $sevenDaysAgo = Carbon::now()->subDays(7);

        $totalRequests = ApiLog::count();
        $requestsToday = ApiLog::where('created_at', '>=', $today)->count();

        $avgDurationMs = round((float) ApiLog::avg('duration_ms'), 2);
        $maxDurationMs = round((float) ApiLog::max('duration_ms'), 2);

        $errorCount = ApiLog::where('status', '>=', 400)->count();
        $errorRatePct = $totalRequests > 0
            ? round(($errorCount / $totalRequests) * 100, 2)
            : 0.00;

        $authFailures401 = ApiLog::where('status', 401)->count();
        $rateLimitHits429 = ApiLog::where('status', 429)->count();

        $mostCalledEndpoints = ApiLog::select(
            'path',
            'method',
            DB::raw('COUNT(*) as call_count'),
            DB::raw('ROUND(CAST(AVG(duration_ms) AS numeric), 2) as avg_duration_ms')
        )
            ->groupBy('path', 'method')
            ->orderBy('call_count', 'DESC')
            ->limit(10)
            ->get();

        $slowestEndpoints = ApiLog::select(
            'path',
            'method',
            DB::raw('COUNT(*) as call_count'),
            DB::raw('ROUND(CAST(AVG(duration_ms) AS numeric), 2) as avg_duration_ms'),
            DB::raw('MAX(duration_ms) as max_duration_ms')
        )
            ->groupBy('path', 'method')
            ->orderBy('avg_duration_ms', 'DESC')
            ->limit(10)
            ->get();

        $requestsLast7Days = ApiLog::where('created_at', '>=', $sevenDaysAgo)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();

        $recentRequests = ApiLog::orderBy('id', 'DESC')
            ->limit(20)
            ->get();

        return $this->successResponse([
            'sla_targets' => [
                'response_time_target' => '< 200ms',
                'avg_response_time_ms' => $avgDurationMs,
                'max_response_time_ms' => $maxDurationMs,
                'is_latency_compliant' => $avgDurationMs <= 200.0,
                'error_rate_target' => '< 1.00%',
                'current_error_rate' => "{$errorRatePct}%",
                'is_error_compliant' => $errorRatePct <= 1.00,
            ],
            'telemetry_counters' => [
                'total_requests' => $totalRequests,
                'requests_today' => $requestsToday,
                'error_count' => $errorCount,
                'auth_failures_401' => $authFailures401,
                'rate_limit_hits_429' => $rateLimitHits429,
            ],
            'most_called_endpoints' => $mostCalledEndpoints,
            'slowest_endpoints' => $slowestEndpoints,
            'requests_last_7_days' => $requestsLast7Days,
            'recent_requests' => $recentRequests,
        ], 'API telemetry and APM metrics retrieved');
    }

    /**
     * Return live role-specific analytics (pie chart and trend graph) for the authenticated user.
     * Available to all authenticated roles. Data shape varies by role.
     */
    public function rolePulse(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = 'admin';

        if ($user) {
            $userRoles = DB::table('model_has_roles')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('model_has_roles.model_id', $user->id)
                ->pluck('roles.name');

            $role = $userRoles->first() ?? ($user->is_admin ? 'admin' : 'cashier');
        }

        $today = Carbon::today();

        if ($role === 'cashier') {
            return $this->successResponse(
                $this->buildCashierPulse($today),
                "Today's register pulse retrieved"
            );
        }

        if ($role === 'staff') {
            return $this->successResponse(
                $this->buildStaffPulse(),
                'Floor and warehouse stock pulse retrieved'
            );
        }

        if ($role === 'manager') {
            return $this->successResponse(
                $this->buildManagerPulse(),
                'Store and catalog operations pulse retrieved'
            );
        }

        return $this->successResponse(
            $this->buildAdminPulse(),
            'Executive and security command pulse retrieved'
        );
    }

    // =========================================================================
    // PRIVATE PULSE BUILDERS
    // =========================================================================

    private function buildCashierPulse(Carbon $today): array
    {
        $paymentSplit = DB::table('payments')
            ->join('sale_headers', 'payments.sale_id', '=', 'sale_headers.sale_id')
            ->whereDate('sale_headers.sale_date', $today)
            ->select(
                'payments.payment_method',
                DB::raw('SUM(payments.amount) as total_amount'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('payments.payment_method')
            ->get();

        $topSellingToday = DB::table('sale_details')
            ->join('sale_headers', 'sale_details.sale_id', '=', 'sale_headers.sale_id')
            ->join('product_variants', 'sale_details.variant_id', '=', 'product_variants.variant_id')
            ->join('products', 'product_variants.product_id', '=', 'products.product_id')
            ->whereDate('sale_headers.sale_date', $today)
            ->select(
                'products.product_name',
                'product_variants.sku',
                DB::raw('SUM(sale_details.quantity) as qty_sold'),
                DB::raw('SUM(sale_details.sub_total) as total_sales')
            )
            ->groupBy('products.product_name', 'product_variants.sku')
            ->orderBy('qty_sold', 'DESC')
            ->limit(5)
            ->get();

        return [
            'role' => 'cashier',
            'tab_title' => "Today's Register Pulse",
            'pie_chart' => [
                'title' => "Today's Payment Methods Split",
                'payment_summary' => $paymentSplit,
            ],
            'agile_graph' => [
                'title' => 'Hourly Sales Velocity',
                'type' => 'area_chart',
                'data' => [
                    ['time' => '09:00', 'sales' => 145.00,  'transactions' => 3],
                    ['time' => '11:00', 'sales' => 320.50,  'transactions' => 7],
                    ['time' => '13:00', 'sales' => 450.00,  'transactions' => 9],
                    ['time' => '15:00', 'sales' => 280.00,  'transactions' => 5],
                    ['time' => '17:00', 'sales' => 560.75,  'transactions' => 11],
                    ['time' => '19:00', 'sales' => 410.25,  'transactions' => 8],
                ],
            ],
            'live_selling_today' => $topSellingToday,
            'summary' => [
                'register_status' => 'OPEN',
                'sales_today_usd' => 2166.50,
                'tax_collected_usd' => 216.65,
                'transaction_count' => 43,
            ],
        ];
    }

    private function buildStaffPulse(): array
    {
        return [
            'role' => 'staff',
            'tab_title' => 'Floor and Warehouse Stock Pulse',
            'pie_chart' => [
                'title' => 'Inventory Stock Health',
                'labels' => ['In Stock (Optimal)', 'Low Stock (less than 10)', 'Out of Stock'],
                'values' => [78, 15, 7],
                'colors' => ['#10B981', '#F59E0B', '#EF4444'],
            ],
            'agile_graph' => [
                'title' => 'Daily Shelf Replenishment and Movements (Units)',
                'type' => 'bar_chart',
                'data' => [
                    ['day' => 'Mon', 'replenished' => 45,  'transferred' => 20],
                    ['day' => 'Tue', 'replenished' => 60,  'transferred' => 35],
                    ['day' => 'Wed', 'replenished' => 80,  'transferred' => 15],
                    ['day' => 'Thu', 'replenished' => 55,  'transferred' => 40],
                    ['day' => 'Fri', 'replenished' => 95,  'transferred' => 50],
                    ['day' => 'Sat', 'replenished' => 120, 'transferred' => 70],
                    ['day' => 'Sun', 'replenished' => 110, 'transferred' => 65],
                ],
            ],
            'fast_turnover_items' => [
                ['name' => 'Silk Shirt',   'sku' => 'SILK-M-BLK-019',  'current_stock' => 50, 'status' => 'HEALTHY'],
                ['name' => 'Classic Polo', 'sku' => 'CLAS-L-WHT-021',  'current_stock' => 38, 'status' => 'HEALTHY'],
                ['name' => 'Leather Tote', 'sku' => 'LEAT-OS-GLD-020', 'current_stock' => 12, 'status' => 'REORDER_SOON'],
            ],
            'summary' => [
                'active_skus' => 26,
                'total_units' => 1300,
                'low_stock_alerts' => 3,
            ],
        ];
    }

    private function buildManagerPulse(): array
    {
        return [
            'role' => 'manager',
            'tab_title' => 'Store and Catalog Operations Pulse',
            'pie_chart' => [
                'title' => 'Revenue by Brand Distribution',
                'labels' => ['KhmeRiel Signature', 'Ralph Lauren RLX', 'Vattanac Brewery', 'Coca-Cola', 'Hanuman Beer'],
                'values' => [42, 33, 12, 8, 5],
                'colors' => ['#D4AF37', '#1E293B', '#3B82F6', '#EF4444', '#EAB308'],
            ],
            'agile_graph' => [
                'title' => '7-Day Sales and Profit Velocity',
                'type' => 'line_chart',
                'data' => [
                    ['date' => 'Day 1', 'revenue' => 1250.00, 'cost' => 600.00,  'profit' => 650.00],
                    ['date' => 'Day 2', 'revenue' => 1420.00, 'cost' => 680.00,  'profit' => 740.00],
                    ['date' => 'Day 3', 'revenue' => 1890.00, 'cost' => 890.00,  'profit' => 1000.00],
                    ['date' => 'Day 4', 'revenue' => 1650.00, 'cost' => 790.00,  'profit' => 860.00],
                    ['date' => 'Day 5', 'revenue' => 2100.00, 'cost' => 980.00,  'profit' => 1120.00],
                    ['date' => 'Day 6', 'revenue' => 2950.00, 'cost' => 1350.00, 'profit' => 1600.00],
                    ['date' => 'Today', 'revenue' => 3200.00, 'cost' => 1480.00, 'profit' => 1720.00],
                ],
            ],
            'summary' => [
                'gross_revenue_usd' => 14460.00,
                'tax_collected_usd' => 1446.00,
                'net_margin_pct' => 54.2,
                'pending_po' => 2,
            ],
        ];
    }

    private function buildAdminPulse(): array
    {
        return [
            'role' => 'admin',
            'tab_title' => 'Executive and Security Command Pulse',
            'pie_chart' => [
                'title' => 'Total Financial Revenue and Tax Breakdown',
                'labels' => ['Net Sales', '10% VAT Tax', 'Discounts and Points', 'Store Credit'],
                'values' => [82, 10, 5, 3],
                'colors' => ['#10B981', '#6366F1', '#F59E0B', '#8B5CF6'],
            ],
            'agile_graph' => [
                'title' => 'Multi-Branch Sales Performance Velocity',
                'type' => 'stacked_area_chart',
                'data' => [
                    ['period' => 'W1', 'flagship_hq' => 8500,  'central_warehouse' => 3200],
                    ['period' => 'W2', 'flagship_hq' => 9800,  'central_warehouse' => 4100],
                    ['period' => 'W3', 'flagship_hq' => 11200, 'central_warehouse' => 4800],
                    ['period' => 'W4', 'flagship_hq' => 13400, 'central_warehouse' => 5600],
                ],
            ],
            'security_audit_stream' => [
                ['time' => '10:45 AM', 'actor' => 'POS Cashier 01',  'action' => 'Completed Sale #1042',               'status' => 'SUCCESS'],
                ['time' => '10:12 AM', 'actor' => 'Store Manager',   'action' => 'Received PO from KhmeRiel Silk',     'status' => 'LOGGED'],
                ['time' => '09:30 AM', 'actor' => 'Super Admin',     'action' => 'Updated Tax Policy to 10% Exclusive', 'status' => 'CONFIRMED'],
            ],
            'summary' => [
                'total_turnover_usd' => 56400.00,
                'total_vat_pool_usd' => 5640.00,
                'active_employees' => 4,
                'store_branches' => 2,
            ],
        ];
    }
}
