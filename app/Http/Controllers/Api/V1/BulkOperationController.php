<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Services\AuditLogService;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BulkOperationController extends BaseApiController
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * POST /api/v1/inventory/bulk-adjust
     * Adjust stock across multiple variants in a single atomic transaction.
     */
    public function bulkAdjust(Request $request): JsonResponse
    {
        $request->validate([
            'adjustments'                 => 'required|array|min:1|max:500',
            'adjustments.*.variant_id'    => 'required|exists:product_variants,variant_id',
            'adjustments.*.quantity'      => 'required|integer',
            'adjustments.*.movement_type' => 'required|string|in:ADJUSTMENT,RETURN_IN,RETURN_OUT,WRITE_OFF,STOCKTAKE',
            'adjustments.*.note'          => 'nullable|string|max:255',
        ]);

        $employeeId = $request->user()?->id ?? $request->user()?->employee_id ?? 1;
        $items = $request->input('adjustments');

        $movements = DB::transaction(function () use ($items, $employeeId) {
            $created = [];
            foreach ($items as $item) {
                $created[] = $this->inventoryService->adjustStock(
                    variantId:    $item['variant_id'],
                    quantity:     $item['quantity'],
                    movementType: $item['movement_type'],
                    employeeId:   $employeeId,
                    note:         $item['note'] ?? 'Bulk Stock Adjustment'
                );
            }
            return $created;
        });

        Log::channel('inventory')->info('Bulk stock adjustment processed', [
            'count'       => count($movements),
            'employee_id' => $employeeId,
        ]);

        return $this->successResponse([
            'processed_count' => count($movements),
            'movements'       => $movements,
        ], 'Bulk stock adjustment completed successfully');
    }

    /**
     * POST /api/v1/variants/bulk-price-update
     * Batch update retail, cost, and wholesale pricing for up to 500 variants.
     */
    public function bulkPriceUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'prices'                    => 'required|array|min:1|max:500',
            'prices.*.variant_id'       => 'required|exists:product_variants,variant_id',
            'prices.*.sale_price'       => 'nullable|numeric|min:0',
            'prices.*.cost_price'       => 'nullable|numeric|min:0',
            'prices.*.wholesale_price'  => 'nullable|numeric|min:0',
        ]);

        $prices = $request->input('prices');
        $updatedCount = 0;

        DB::transaction(function () use ($prices, &$updatedCount) {
            foreach ($prices as $p) {
                $variant = ProductVariant::find($p['variant_id']);
                if (!$variant) continue;

                $updateData = [];
                if (isset($p['sale_price']))      $updateData['sale_price']      = $p['sale_price'];
                if (isset($p['cost_price']))      $updateData['cost_price']      = $p['cost_price'];
                if (isset($p['wholesale_price'])) $updateData['wholesale_price'] = $p['wholesale_price'];

                if (!empty($updateData)) {
                    $variant->update($updateData);
                    Cache::forget("product:{$variant->product_id}");
                    Cache::forget("product_matrix:{$variant->product_id}");
                    $updatedCount++;
                }
            }
        });

        return $this->successResponse([
            'updated_count' => $updatedCount,
        ], "Successfully updated pricing for {$updatedCount} variants");
    }

    /**
     * POST /api/v1/products/bulk-import
     * Batch import new product catalog items with nested size/color variants.
     */
    public function bulkImport(Request $request): JsonResponse
    {
        $request->validate([
            'products'                  => 'required|array|min:1|max:200',
            'products.*.category_id'    => 'required|exists:categories,category_id',
            'products.*.product_name'   => 'required|string|max:150',
            'products.*.brand'          => 'nullable|string',
            'products.*.variants'       => 'nullable|array',
        ]);

        $productsData = $request->input('products');
        $createdProducts = [];

        DB::transaction(function () use ($productsData, &$createdProducts) {
            foreach ($productsData as $data) {
                $product = Product::create([
                    'category_id'  => $data['category_id'],
                    'product_name' => $data['product_name'],
                    'brand'        => $data['brand'] ?? null,
                    'status'       => 'ACTIVE',
                ]);

                if (!empty($data['variants'])) {
                    foreach ($data['variants'] as $v) {
                        ProductVariant::create([
                            'product_id' => $product->product_id,
                            'size_id'    => $v['size_id'] ?? null,
                            'color_id'   => $v['color_id'] ?? null,
                            'sku'        => $v['sku'] ?? ('SKU-' . $product->product_id . '-' . uniqid()),
                            'barcode'    => $v['barcode'] ?? null,
                            'cost_price' => $v['cost_price'] ?? 0.0,
                            'sale_price' => $v['sale_price'] ?? 0.0,
                            'quantity'   => $v['quantity'] ?? 0,
                        ]);
                    }
                }

                $createdProducts[] = $product->load('variants');
            }
            Cache::forget('categories:all');
        });

        return $this->successResponse([
            'imported_count' => count($createdProducts),
            'products'       => $createdProducts,
        ], "Successfully imported " . count($createdProducts) . " products with variants", 201);
    }

    /**
     * POST /api/v1/purchases/bulk-receive
     * Batch receive multiple purchase orders simultaneously.
     */
    public function bulkReceive(Request $request): JsonResponse
    {
        $request->validate([
            'orders'                       => 'required|array|min:1|max:50',
            'orders.*.supplier_id'         => 'required|exists:suppliers,supplier_id',
            'orders.*.items'               => 'required|array|min:1',
            'orders.*.items.*.variant_id'  => 'required|exists:product_variants,variant_id',
            'orders.*.items.*.quantity'    => 'required|integer|min:1',
            'orders.*.items.*.cost_price'  => 'required|numeric|min:0',
        ]);

        $employeeId = $request->user()?->id ?? $request->user()?->employee_id ?? 1;
        $ordersData = $request->input('orders');
        $receivedOrders = [];

        foreach ($ordersData as $order) {
            $receivedOrders[] = $this->inventoryService->receivePurchase(
                supplierId: $order['supplier_id'],
                employeeId: $employeeId,
                items:      $order['items']
            );
        }

        return $this->successResponse([
            'received_orders_count' => count($receivedOrders),
            'orders'                => $receivedOrders,
        ], "Successfully received " . count($receivedOrders) . " purchase orders in batch", 201);
    }
}
