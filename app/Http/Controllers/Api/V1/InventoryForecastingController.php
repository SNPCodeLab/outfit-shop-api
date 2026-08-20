<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ProductVariant;
use App\Models\PurchaseDetail;
use App\Models\PurchaseHeader;
use App\Models\Supplier;
use App\Services\ForecastingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryForecastingController extends BaseApiController
{
    protected ForecastingService $forecastingService;

    public function __construct(ForecastingService $forecastingService)
    {
        $this->forecastingService = $forecastingService;
    }

    /**
     * Calculate sales velocity and generate restock recommendations.
     * Restricted to MANAGER or ADMIN.
     */
    public function restockRecommendations(Request $request): JsonResponse
    {
        $lookbackDays = (int) $request->input('lookback', 14);
        $threshold = (int) $request->input('threshold', 7);

        $risks = $this->forecastingService->getStockoutRisks($lookbackDays, $threshold);

        $recommendations = [];

        foreach ($risks as $vId => $r) {
            $variant = ProductVariant::with(['product.category', 'size', 'color'])->find($vId);

            if (! $variant) {
                continue;
            }

            $recommendations[] = [
                'variant_id' => $variant->variant_id,
                'sku' => $variant->sku,
                'product_name' => $variant->product->product_name ?? 'Unknown',
                'size_name' => $variant->size->size_name ?? 'STD',
                'color_name' => $variant->color->color_name ?? 'Default',
                'current_stock' => $variant->quantity,
                'reorder_level' => $r['reorder_level'],
                'units_sold_last_period' => $r['total_sold'],
                'daily_run_rate' => $r['daily_velocity'],
                'days_of_stock_left' => $r['days_remaining'],
                'cost_price' => $r['cost_price'],
                'recommended_order_qty' => $r['suggested_reorder_qty'],
                'estimated_restock_cost' => $r['estimated_cost'],
                'urgency' => $r['urgency'],
            ];
        }

        return $this->successResponse([
            'lookback_days' => $lookbackDays,
            'threshold_days' => $threshold,
            'total_items_to_restock' => count($recommendations),
            'total_estimated_budget' => round(array_sum(array_column($recommendations, 'estimated_restock_cost')), 2),
            'recommendations' => $recommendations,
        ], 'Restock recommendations generated based on sales velocity algorithm');
    }

    /**
     * Auto-draft a Purchase Order for recommended restock items.
     * Restricted to MANAGER or ADMIN.
     */
    public function autoGeneratePurchaseOrder(Request $request): JsonResponse
    {
        $items = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,variant_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'nullable|numeric|min:0',
        ])['items'];

        $supplier = Supplier::first() ?? Supplier::create([
            'supplier_name' => 'Khmer Garment Central Supply',
            'contact_name' => 'Vendor Relations',
            'phone' => '+855 23 777 888',
            'email' => 'orders@garmentsupply.kh',
            'status' => 'ACTIVE',
        ]);

        $po = DB::transaction(function () use ($supplier, $items, $request) {
            $employeeId = $request->user()->employee_id ?? $request->user()->id ?? 1;
            $totalAmount = 0.0;
            $poDetails = [];

            foreach ($items as $item) {
                $variant = ProductVariant::findOrFail($item['variant_id']);
                $qty = (int) $item['quantity'];
                $cost = (float) ($item['cost_price'] ?? $variant->cost_price);
                $subtotal = $qty * $cost;
                $totalAmount += $subtotal;
                $poDetails[] = [
                    'variant_id' => $variant->variant_id,
                    'quantity' => $qty,
                    'unit_cost' => $cost,
                    'sub_total' => $subtotal,
                ];
            }

            $purchase = PurchaseHeader::create([
                'reference_no' => 'PO-AUTO-'.time(),
                'supplier_id' => $supplier->supplier_id,
                'employee_id' => $employeeId,
                'purchase_date' => now(),
                'total_amount' => $totalAmount,
                'tax_amount' => 0.00,
                'grand_total' => $totalAmount,
                'status' => 'ORDERED',
                'notes' => 'Auto-generated restock PO based on sales velocity algorithm',
            ]);

            foreach ($poDetails as $d) {
                PurchaseDetail::create(array_merge($d, ['purchase_id' => $purchase->purchase_id]));
            }

            return $purchase->load(['supplier', 'details.variant.product']);
        });

        return $this->createdResponse($po, 'Purchase order auto-drafted successfully', '/api/v1/purchases/'.$po->purchase_id);
    }
}
