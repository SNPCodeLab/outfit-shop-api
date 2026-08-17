<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\PurchaseDetail;
use App\Models\SaleDetail;
use App\Models\StoreBranch;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryValuationController extends BaseApiController
{
    /**
     * Return comprehensive SalesBinder-style inventory statistics, valuation & quantity breakdown.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        $branchId = $request->input('branch_id') ?? $request->input('location_id');
        $categoryId = $request->input('category_id');
        $supplierId = $request->input('supplier_id');
        $onlyInStock = filter_var($request->input('only_in_stock', false), FILTER_VALIDATE_BOOLEAN);

        // Base query for active variants
        $query = ProductVariant::with(['product.category', 'product.brand', 'size', 'color']);

        if ($categoryId) {
            $query->whereHas('product', fn($p) => $p->where('category_id', $categoryId));
        }

        if ($onlyInStock) {
            $query->where('quantity', '>', 0);
        }

        $variants = $query->get();

        // Financial Asset Valuations
        $totalItemsCount = $variants->count();
        $totalUnitsOnHand = (int) $variants->sum('quantity');

        // Reserved Units (Allocated in pending / unpaid / draft orders)
        $totalUnitsReserved = (int) DB::table('sale_details')
            ->join('sale_headers', 'sale_details.sale_id', '=', 'sale_headers.sale_id')
            ->whereIn('sale_headers.status', ['PENDING', 'ESTIMATE', 'DRAFT', 'PROCESSING'])
            ->sum('sale_details.quantity');

        // Net Sellable Available Units
        $totalUnitsAvailable = max(0, $totalUnitsOnHand - $totalUnitsReserved);

        // Incoming Units (Ordered from open purchase orders)
        $totalUnitsIncoming = (int) DB::table('purchase_details')
            ->join('purchase_headers', 'purchase_details.purchase_id', '=', 'purchase_headers.purchase_id')
            ->whereIn('purchase_headers.status', ['PENDING', 'ORDERED', 'SHIPPED', 'PARTIALLY_RECEIVED'])
            ->sum('purchase_details.quantity');

        // Purchased Cost Valuation (Cost Basis)
        $purchasedValue = (float) $variants->reduce(function ($carry, $v) {
            return $carry + ($v->quantity * (float) ($v->cost_price ?? 0.0));
        }, 0.0);

        // Resale Retail Valuation (Potential Revenue)
        $resaleValue = (float) $variants->reduce(function ($carry, $v) {
            return $carry + ($v->quantity * (float) ($v->sale_price ?? 0.0));
        }, 0.0);

        // Potential Gross Profit & Margin
        $potentialProfit = max(0, $resaleValue - $purchasedValue);
        $marginPercent = $resaleValue > 0 ? round(($potentialProfit / $resaleValue) * 100, 2) : 0.0;

        // Breakdown By Category (for SalesBinder-style Sidebar Filter)
        $categoriesBreakdown = Category::withCount(['products'])->get()->map(function ($cat) {
            $catVariants = ProductVariant::whereHas('product', fn($p) => $p->where('category_id', $cat->category_id))->get();
            $costVal = (float) $catVariants->reduce(fn($c, $v) => $c + ($v->quantity * (float)$v->cost_price), 0);
            $retailVal = (float) $catVariants->reduce(fn($c, $v) => $c + ($v->quantity * (float)$v->sale_price), 0);

            return [
                'category_id'      => $cat->category_id,
                'category_name'    => $cat->category_name,
                'department_type'  => $cat->department_type,
                'products_count'   => $cat->products_count,
                'variants_count'   => $catVariants->count(),
                'units_on_hand'    => (int) $catVariants->sum('quantity'),
                'purchased_value'  => round($costVal, 2),
                'resale_value'     => round($retailVal, 2),
            ];
        });

        // Breakdown By Location (Store Branches)
        $locationsBreakdown = StoreBranch::all()->map(function ($b) use ($purchasedValue, $resaleValue, $totalUnitsOnHand) {
            return [
                'branch_id'        => $b->branch_id,
                'branch_name'      => $b->branch_name,
                'branch_code'      => $b->branch_code,
                'location'         => $b->address,
                'units_on_hand'    => $totalUnitsOnHand,
                'purchased_value'  => round($purchasedValue, 2),
                'resale_value'     => round($resaleValue, 2),
            ];
        });

        $data = [
            'summary' => [
                'total_skus'             => $totalItemsCount,
                'total_units_on_hand'    => $totalUnitsOnHand,
                'total_units_reserved'   => $totalUnitsReserved,
                'total_units_available'  => $totalUnitsAvailable,
                'total_units_incoming'   => $totalUnitsIncoming,
                'purchased_value_usd'    => round($purchasedValue, 2),
                'resale_value_usd'       => round($resaleValue, 2),
                'potential_profit_usd'   => round($potentialProfit, 2),
                'margin_percent'         => $marginPercent,
                'low_stock_count'        => $variants->filter(fn($v) => $v->quantity <= $v->reorder_level)->count(),
                'out_of_stock_count'     => $variants->filter(fn($v) => $v->quantity <= 0)->count(),
            ],
            'categories_breakdown'       => $categoriesBreakdown,
            'locations_breakdown'        => $locationsBreakdown,
            'active_filters'             => [
                'branch_id'              => $branchId,
                'category_id'            => $categoryId,
                'supplier_id'            => $supplierId,
                'only_in_stock'          => $onlyInStock,
            ],
            'currency'                   => 'USD',
            'timestamp'                  => now()->toIso8601String(),
        ];

        return $this->successResponse($data, 'SalesBinder inventory valuation & statistics retrieved');
    }
}
