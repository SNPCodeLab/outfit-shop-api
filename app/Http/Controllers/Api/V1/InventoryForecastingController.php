<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ProductVariant;
use App\Models\PurchaseDetail;
use App\Models\PurchaseHeader;
use App\Models\SaleDetail;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryForecastingController extends BaseApiController
{
    /**
     * Calculate 14-day sales velocity and generate restock recommendations.
     * Restricted to MANAGER or ADMIN.
     */
    public function restockRecommendations(): JsonResponse
    {
        $lookbackDays = 14;
        $sinceDate    = Carbon::now()->subDays($lookbackDays);

        $salesVelocity = SaleDetail::where('created_at', '>=', $sinceDate)
            ->select('variant_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('variant_id')
            ->pluck('total_sold', 'variant_id')
            ->toArray();

        $variants       = ProductVariant::with(['product.category', 'size', 'color'])->get();
        $recommendations = [];

        foreach ($variants as $v) {
            $sold14      = $salesVelocity[$v->variant_id] ?? 0;
            $dailyRunRate = round($sold14 / $lookbackDays, 2);
            $daysLeft    = $dailyRunRate > 0 ? round($v->quantity / $dailyRunRate, 1) : 999;
            $reorderThreshold = $v->reorder_level ?? 10;
            $isUrgent    = ($v->quantity <= $reorderThreshold) || ($daysLeft <= 7 && $dailyRunRate > 0);
            $recommendedQty = max(20, (int) round(($dailyRunRate * 30) - $v->quantity));

            if ($isUrgent || $v->quantity <= 5) {
                $recommendations[] = [
                    'variant_id'              => $v->variant_id,
                    'sku'                     => $v->sku,
                    'product_name'            => $v->product->product_name ?? 'Unknown',
                    'size_name'               => $v->size->size_name ?? 'STD',
                    'color_name'              => $v->color->color_name ?? 'Default',
                    'current_stock'           => $v->quantity,
                    'reorder_level'           => $reorderThreshold,
                    'units_sold_last_14_days' => $sold14,
                    'daily_run_rate'          => $dailyRunRate,
                    'days_of_stock_left'      => $daysLeft,
                    'cost_price'              => (float) $v->cost_price,
                    'recommended_order_qty'   => $recommendedQty,
                    'estimated_restock_cost'  => round($recommendedQty * (float) $v->cost_price, 2),
                    'urgency'                 => $v->quantity === 0
                        ? 'OUT_OF_STOCK'
                        : ($daysLeft <= 3 ? 'CRITICAL' : 'RESTOCK_NEEDED'),
                ];
            }
        }

        return $this->successResponse([
            'lookback_days'          => $lookbackDays,
            'total_items_to_restock' => count($recommendations),
            'total_estimated_budget' => round(array_sum(array_column($recommendations, 'estimated_restock_cost')), 2),
            'recommendations'        => $recommendations,
        ], 'Restock recommendations generated based on 14-day sales velocity');
    }

    /**
     * Auto-draft a Purchase Order for recommended restock items.
     * Restricted to MANAGER or ADMIN.
     */
    public function autoGeneratePurchaseOrder(Request $request): JsonResponse
    {
        $items = $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.variant_id'   => 'required|exists:product_variants,variant_id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.cost_price'   => 'nullable|numeric|min:0',
        ])['items'];

        $supplier = Supplier::first() ?? Supplier::create([
            'supplier_name' => 'Khmer Garment Central Supply',
            'contact_name'  => 'Vendor Relations',
            'phone'         => '+855 23 777 888',
            'email'         => 'orders@garmentsupply.kh',
            'status'        => 'ACTIVE',
        ]);

        $po = DB::transaction(function () use ($supplier, $items, $request) {
            $employeeId  = $request->user()->employee_id ?? $request->user()->id ?? 1;
            $totalAmount = 0.0;
            $poDetails   = [];

            foreach ($items as $item) {
                $variant  = ProductVariant::findOrFail($item['variant_id']);
                $qty      = (int) $item['quantity'];
                $cost     = (float) ($item['cost_price'] ?? $variant->cost_price);
                $subtotal = $qty * $cost;
                $totalAmount += $subtotal;
                $poDetails[] = [
                    'variant_id' => $variant->variant_id,
                    'quantity'   => $qty,
                    'unit_cost'  => $cost,
                    'sub_total'  => $subtotal,
                ];
            }

            $purchase = PurchaseHeader::create([
                'reference_no'  => 'PO-AUTO-' . time(),
                'supplier_id'   => $supplier->supplier_id,
                'employee_id'   => $employeeId,
                'purchase_date' => now(),
                'total_amount'  => $totalAmount,
                'tax_amount'    => 0.00,
                'grand_total'   => $totalAmount,
                'status'        => 'ORDERED',
                'notes'         => 'Auto-generated restock PO based on sales velocity algorithm',
            ]);

            foreach ($poDetails as $d) {
                PurchaseDetail::create(array_merge($d, ['purchase_id' => $purchase->purchase_id]));
            }

            return $purchase->load(['supplier', 'details.variant.product']);
        });

        return $this->createdResponse($po, 'Purchase order auto-drafted successfully', '/api/v1/purchases/' . $po->purchase_id);
    }
}
