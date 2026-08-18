<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\PurchaseHeader;
use App\Models\SaleHeader;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends BaseApiController
{
    /**
     * 1. GET /api/v1/reports/sales
     * Group sales by time (day, week, month, year) or dimension (product, category, brand).
     */
    public function sales(Request $request): JsonResponse
    {
        $groupBy = strtolower($request->input('group_by', 'day'));

        if (in_array($groupBy, ['day', 'week', 'month', 'year'])) {
            $format = match ($groupBy) {
                'day' => 'YYYY-MM-DD',
                'week' => 'IYYY-IW',
                'month' => 'YYYY-MM',
                'year' => 'YYYY',
            };

            $data = SaleHeader::where('status', 'COMPLETED')
                ->select(
                    DB::raw("TO_CHAR(sale_date, '{$format}') as period"),
                    DB::raw('COUNT(sale_id) as total_transactions'),
                    DB::raw('SUM(grand_total) as total_revenue'),
                    DB::raw('SUM(tax_amount) as total_tax'),
                    DB::raw('SUM(discount) as total_discount'),
                    DB::raw('AVG(grand_total) as average_ticket')
                )
                ->groupBy('period')
                ->orderBy('period', 'desc')
                ->limit(50)
                ->get();

            return $this->successResponse($data, "Sales grouped by {$groupBy} retrieved");
        }

        if ($groupBy === 'category') {
            $data = DB::table('sale_details')
                ->join('product_variants', 'sale_details.variant_id', '=', 'product_variants.variant_id')
                ->join('products', 'product_variants.product_id', '=', 'products.product_id')
                ->join('categories', 'products.category_id', '=', 'categories.category_id')
                ->select(
                    'categories.category_id',
                    'categories.category_name',
                    DB::raw('SUM(sale_details.quantity) as units_sold'),
                    DB::raw('SUM(sale_details.sub_total) as revenue')
                )
                ->groupBy('categories.category_id', 'categories.category_name')
                ->orderBy('revenue', 'desc')
                ->get();

            return $this->successResponse($data, 'Sales grouped by category retrieved');
        }

        if ($groupBy === 'brand') {
            $data = DB::table('sale_details')
                ->join('product_variants', 'sale_details.variant_id', '=', 'product_variants.variant_id')
                ->join('products', 'product_variants.product_id', '=', 'products.product_id')
                ->select(
                    DB::raw("COALESCE(products.brand, 'Unbranded') as brand"),
                    DB::raw('SUM(sale_details.quantity) as units_sold'),
                    DB::raw('SUM(sale_details.sub_total) as revenue')
                )
                ->groupBy('products.brand')
                ->orderBy('revenue', 'desc')
                ->get();

            return $this->successResponse($data, 'Sales grouped by brand retrieved');
        }

        // Default: Group by top products
        $data = DB::table('sale_details')
            ->join('product_variants', 'sale_details.variant_id', '=', 'product_variants.variant_id')
            ->join('products', 'product_variants.product_id', '=', 'products.product_id')
            ->select(
                'products.product_id',
                'products.product_name',
                'products.brand',
                DB::raw('SUM(sale_details.quantity) as units_sold'),
                DB::raw('SUM(sale_details.sub_total) as revenue')
            )
            ->groupBy('products.product_id', 'products.product_name', 'products.brand')
            ->orderBy('revenue', 'desc')
            ->limit(50)
            ->get();

        return $this->successResponse($data, 'Sales grouped by product retrieved');
    }

    /**
     * 2. GET /api/v1/reports/inventory-valuation
     * Total Asset Value (Cost) vs Resale Value (Retail) & Gross Margin Potential.
     */
    public function inventoryValuation(): JsonResponse
    {
        $valuation = ProductVariant::select(
            DB::raw('COUNT(variant_id) as total_skus'),
            DB::raw('SUM(quantity) as total_units_on_hand'),
            DB::raw('SUM(quantity * cost_price) as total_purchased_cost_value'),
            DB::raw('SUM(quantity * sale_price) as total_estimated_resale_value')
        )->first();

        $cost = (float) $valuation->total_purchased_cost_value;
        $retail = (float) $valuation->total_estimated_resale_value;
        $potentialProfit = round($retail - $cost, 2);
        $marginPct = $retail > 0 ? round(($potentialProfit / $retail) * 100, 2) : 0.0;

        return $this->successResponse([
            'total_skus' => (int) $valuation->total_skus,
            'total_units_on_hand' => (int) $valuation->total_units_on_hand,
            'total_cost_value_usd' => $cost,
            'total_retail_value_usd' => $retail,
            'potential_gross_profit_usd' => $potentialProfit,
            'projected_gross_margin_pct' => $marginPct,
        ], 'Inventory valuation report retrieved');
    }

    /**
     * 3. GET /api/v1/reports/stock-aging
     * Categorize stock into aging brackets (<30, 30-60, 60-90, >90 days).
     */
    public function stockAging(): JsonResponse
    {
        $variants = ProductVariant::with(['product', 'size', 'color'])
            ->where('quantity', '>', 0)
            ->get();

        $agingSummary = [
            'fresh_under_30_days' => ['count' => 0, 'units' => 0, 'cost_value' => 0.0],
            'normal_30_to_60_days' => ['count' => 0, 'units' => 0, 'cost_value' => 0.0],
            'slow_60_to_90_days' => ['count' => 0, 'units' => 0, 'cost_value' => 0.0],
            'dead_stock_over_90' => ['count' => 0, 'units' => 0, 'cost_value' => 0.0],
        ];

        $now = now();
        foreach ($variants as $v) {
            $days = $v->created_at ? $now->diffInDays($v->created_at) : 0;
            $qty = (int) $v->quantity;
            $val = $qty * (float) $v->cost_price;

            if ($days <= 30) {
                $bucket = 'fresh_under_30_days';
            } elseif ($days <= 60) {
                $bucket = 'normal_30_to_60_days';
            } elseif ($days <= 90) {
                $bucket = 'slow_60_to_90_days';
            } else {
                $bucket = 'dead_stock_over_90';
            }

            $agingSummary[$bucket]['count']++;
            $agingSummary[$bucket]['units'] += $qty;
            $agingSummary[$bucket]['cost_value'] = round($agingSummary[$bucket]['cost_value'] + $val, 2);
        }

        return $this->successResponse($agingSummary, 'Stock aging report retrieved');
    }

    /**
     * 4. GET /api/v1/reports/customer-purchase-history
     * Top VIP customers by Lifetime Value (LTV) and orders count.
     */
    public function customerPurchaseHistory(): JsonResponse
    {
        $customers = Customer::withCount(['sales as orders_count' => fn ($q) => $q->where('status', 'COMPLETED')])
            ->withSum(['sales as total_spent' => fn ($q) => $q->where('status', 'COMPLETED')], 'grand_total')
            ->orderBy('total_spent', 'desc')
            ->limit(50)
            ->get();

        return $this->successResponse($customers, 'Customer purchase history & VIP rankings retrieved');
    }

    /**
     * 5. GET /api/v1/reports/supplier-performance
     * Supplier spend, fulfilled POs, and order metrics.
     */
    public function supplierPerformance(): JsonResponse
    {
        $suppliers = Supplier::withCount('purchases')
            ->withSum(['purchases as total_spend' => fn ($q) => $q->where('status', 'RECEIVED')], 'grand_total')
            ->orderBy('total_spend', 'desc')
            ->get();

        return $this->successResponse($suppliers, 'Supplier performance report retrieved');
    }

    /**
     * 6. GET /api/v1/reports/profit-margin
     * Revenue vs Cost of Goods Sold (COGS) and Realized Gross Profit.
     */
    public function profitMargin(Request $request): JsonResponse
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

        $summary = DB::table('sale_details')
            ->join('sale_headers', 'sale_details.sale_id', '=', 'sale_headers.sale_id')
            ->join('product_variants', 'sale_details.variant_id', '=', 'product_variants.variant_id')
            ->where('sale_headers.status', 'COMPLETED')
            ->whereBetween('sale_headers.sale_date', [$startDate, $endDate])
            ->select(
                DB::raw('SUM(sale_details.sub_total) as gross_revenue'),
                DB::raw('SUM(sale_details.quantity * product_variants.cost_price) as cogs'),
                DB::raw('SUM(sale_details.quantity) as total_units_sold')
            )
            ->first();

        $revenue = (float) ($summary->gross_revenue ?? 0.0);
        $cogs = (float) ($summary->cogs ?? 0.0);
        $grossProfit = round($revenue - $cogs, 2);
        $marginPct = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0.0;

        return $this->successResponse([
            'date_range' => ['start' => $startDate, 'end' => $endDate],
            'gross_revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'gross_margin_pct' => $marginPct,
            'total_units_sold' => (int) ($summary->total_units_sold ?? 0),
        ], 'Realized profit margin report retrieved');
    }

    /**
     * 7. GET /api/v1/reports/cash-flow
     * Inflows (POS Sales by Payment Method) vs Outflows (Supplier POs).
     */
    public function cashFlow(): JsonResponse
    {
        $inflowsByMethod = Payment::where('payment_status', 'PAID')
            ->select('payment_method', DB::raw('SUM(amount) as total_inflow'))
            ->groupBy('payment_method')
            ->get();

        $totalInflow = $inflowsByMethod->sum('total_inflow');

        $totalOutflow = PurchaseHeader::where('status', 'RECEIVED')
            ->sum('grand_total');

        $netCashFlow = round($totalInflow - $totalOutflow, 2);

        return $this->successResponse([
            'total_inflow_usd' => (float) $totalInflow,
            'total_outflow_usd' => (float) $totalOutflow,
            'net_cash_flow_usd' => $netCashFlow,
            'inflows_by_method' => $inflowsByMethod,
        ], 'Store cash flow report retrieved');
    }
}
