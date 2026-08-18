<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\PurchaseDetail;
use App\Models\PurchaseHeader;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Valid movement types for manual stock adjustment.
     * Strict enum validation prevents garbage movement_type values in the ledger.
     */
    public const ADJUSTMENT_MOVEMENT_TYPES = [
        'ADJUSTMENT',  // General correction after a physical count
        'RETURN_IN',   // Customer return — stock back in
        'RETURN_OUT',  // Supplier return — stock going out
        'WRITE_OFF',   // Damaged / expired goods removed
        'STOCKTAKE',   // Reconciliation after a full physical stocktake
    ];

    /**
     * Receive a purchase order, increment variant stock, and write ledger entries.
     *
     * Industry patterns applied:
     *  - DB transaction + pessimistic locking (lockForUpdate) — prevents race conditions
     *  - stock_before / stock_after audit trail on every movement
     *  - Purchase status = RECEIVED on physical stock increment
     *  - reference_no auto-generated as PO-YYYYMMDD-{purchase_id}
     *
     * @param  array  $items  ['variant_id', 'quantity', 'cost_price']
     *
     * @throws Exception
     */
    public function receivePurchase(int $supplierId, int $employeeId, array $items): PurchaseHeader
    {
        return DB::transaction(function () use ($supplierId, $employeeId, $items) {
            $totalAmount = 0.0;
            $taxRate = 10.00;
            $detailsToInsert = [];

            foreach ($items as $item) {
                $variantId = $item['variant_id'];
                $qty = (int) $item['quantity'];
                $costPrice = (float) $item['cost_price'];

                if ($qty <= 0) {
                    throw new Exception("Purchase quantity must be > 0. Got: {$qty}.");
                }

                if ($costPrice < 0) {
                    throw new Exception("Cost price cannot be negative. Got: {$costPrice}.");
                }

                $variant = ProductVariant::where('variant_id', $variantId)
                    ->lockForUpdate()
                    ->first();

                if (! $variant) {
                    throw new Exception("Variant ID {$variantId} not found.");
                }

                $subTotal = round($qty * $costPrice, 2);
                $totalAmount += $subTotal;

                $detailsToInsert[] = [
                    'variant' => $variant,
                    'quantity' => $qty,
                    'cost_price' => $costPrice,
                    'sub_total' => $subTotal,
                    'stock_before' => $variant->quantity,
                ];
            }

            // Tax-exclusive calculation for purchase order financials
            $taxAmount = round($totalAmount * ($taxRate / 100), 2);
            $grandTotal = round($totalAmount + $taxAmount, 2);

            // Create purchase header with status RECEIVED
            $purchaseHeader = PurchaseHeader::create([
                'supplier_id' => $supplierId,
                'employee_id' => $employeeId,
                'purchase_date' => now(),
                'total_amount' => $totalAmount,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'status' => 'RECEIVED',
            ]);

            // Auto-generate reference_no after we have the PK
            $referenceNo = 'PO-'.now()->format('Ymd').'-'.str_pad($purchaseHeader->purchase_id, 5, '0', STR_PAD_LEFT);
            $purchaseHeader->update(['reference_no' => $referenceNo]);

            foreach ($detailsToInsert as $detail) {
                /** @var ProductVariant $variant */
                $variant = $detail['variant'];
                $qty = $detail['quantity'];
                $costPrice = $detail['cost_price'];
                $stockBefore = $detail['stock_before'];

                PurchaseDetail::create([
                    'purchase_id' => $purchaseHeader->purchase_id,
                    'variant_id' => $variant->variant_id,
                    'quantity' => $qty,
                    'cost_price' => $costPrice,
                    'sub_total' => $detail['sub_total'],
                ]);

                // Increment physical stock & update cost price on variant
                $variant->increment('quantity', $qty);
                $stockAfter = $stockBefore + $qty;

                // Update variant cost_price to latest purchase price (FIFO costing)
                $variant->update(['cost_price' => $costPrice]);

                // Stock Movement Audit Ledger with full before/after trail
                StockMovement::create([
                    'variant_id' => $variant->variant_id,
                    'movement_type' => 'PURCHASE',
                    'quantity' => $qty,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'movement_date' => now(),
                    'reference_type' => 'PurchaseHeader',
                    'reference_id' => $purchaseHeader->purchase_id,
                    'note' => "Purchase Receiving {$referenceNo}",
                    'employee_id' => $employeeId,
                    'created_by' => $employeeId,
                ]);
            }

            AuditLogService::log(
                action: 'PURCHASE',
                entity: 'PurchaseHeader',
                entityId: $purchaseHeader->purchase_id,
                newValues: [
                    'reference_no' => $referenceNo,
                    'total_amount' => $totalAmount,
                    'grand_total' => $grandTotal,
                    'items_count' => count($items),
                ],
                userId: $employeeId
            );

            Log::channel('purchasing')->info('Purchase order received and inventory updated', [
                'purchase_id' => $purchaseHeader->purchase_id,
                'reference_no' => $referenceNo,
                'supplier_id' => $supplierId,
                'employee_id' => $employeeId,
                'items_count' => count($items),
                'grand_total' => (float) $grandTotal,
            ]);

            return $purchaseHeader->load(['details.variant.product', 'supplier', 'employee']);
        });
    }

    /**
     * Perform a manual stock adjustment with strict movement type validation.
     *
     * @param  int  $quantity  Positive = IN, Negative = OUT
     * @param  string  $movementType  Must be in ADJUSTMENT_MOVEMENT_TYPES
     *
     * @throws Exception
     */
    public function adjustStock(
        int $variantId,
        int $quantity,
        string $movementType,
        int $employeeId,
        ?string $note = null
    ): StockMovement {
        // Strict enum validation
        $movementType = strtoupper($movementType);
        if (! in_array($movementType, self::ADJUSTMENT_MOVEMENT_TYPES)) {
            throw new Exception(
                "Invalid movement_type '{$movementType}'. ".
                'Allowed: '.implode(', ', self::ADJUSTMENT_MOVEMENT_TYPES)
            );
        }

        return DB::transaction(function () use ($variantId, $quantity, $movementType, $employeeId, $note) {
            $variant = ProductVariant::where('variant_id', $variantId)
                ->lockForUpdate()
                ->firstOrFail();

            // Capture stock_before BEFORE any change
            $stockBefore = (int) $variant->quantity;

            if ($quantity < 0 && $stockBefore < abs($quantity)) {
                throw new Exception(
                    'Cannot reduce stock below 0. '.
                    "Current stock: {$stockBefore}, Reduction requested: ".abs($quantity)
                );
            }

            // Apply the adjustment
            $stockAfter = $stockBefore + $quantity;
            $variant->update(['quantity' => $stockAfter]);

            $movement = StockMovement::create([
                'variant_id' => $variant->variant_id,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'movement_date' => now(),
                'reference_type' => 'MANUAL_ADJUSTMENT',
                'reference_id' => null,
                'note' => $note ?? 'Manual Inventory Adjustment',
                'created_by' => $employeeId,
            ]);

            AuditLogService::log(
                action: 'ADJUSTMENT',
                entity: 'StockMovement',
                entityId: $movement->movement_id,
                newValues: [
                    'variant_id' => $variantId,
                    'quantity' => $quantity,
                    'movement_type' => $movementType,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                ],
                userId: $employeeId
            );

            Log::channel('inventory')->info('Stock adjusted manually', [
                'movement_id' => $movement->movement_id,
                'variant_id' => $variantId,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'employee_id' => $employeeId,
            ]);

            return $movement;
        });
    }
}
