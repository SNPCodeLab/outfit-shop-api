<?php

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\PurchaseDetail;
use App\Models\PurchaseHeader;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Create a purchase order and increment variant stock inside a transaction.
     *
     * @param int $supplierId
     * @param int $employeeId
     * @param array $items Array of ['variant_id' => int, 'quantity' => int, 'cost_price' => float]
     * @return PurchaseHeader
     * @throws Exception
     */
    public function receivePurchase(int $supplierId, int $employeeId, array $items): PurchaseHeader
    {
        return DB::transaction(function () use ($supplierId, $employeeId, $items) {
            $totalAmount = 0.0;
            $detailsToInsert = [];

            foreach ($items as $item) {
                $variantId = $item['variant_id'];
                $qty = (int) $item['quantity'];
                $costPrice = (float) $item['cost_price'];

                if ($qty <= 0) {
                    throw new Exception("Purchase quantity must be > 0.");
                }

                $variant = ProductVariant::where('variant_id', $variantId)->lockForUpdate()->first();
                if (!$variant) {
                    throw new Exception("Variant ID {$variantId} not found.");
                }

                $subTotal = $qty * $costPrice;
                $totalAmount += $subTotal;

                $detailsToInsert[] = [
                    'variant'    => $variant,
                    'quantity'   => $qty,
                    'cost_price' => $costPrice,
                    'sub_total'  => $subTotal,
                ];
            }

            $purchaseHeader = PurchaseHeader::create([
                'supplier_id'   => $supplierId,
                'employee_id'   => $employeeId,
                'purchase_date' => now(),
                'total_amount'  => $totalAmount,
                'status'        => 'COMPLETED',
            ]);

            foreach ($detailsToInsert as $detail) {
                /** @var ProductVariant $variant */
                $variant = $detail['variant'];
                $qty = $detail['quantity'];
                $costPrice = $detail['cost_price'];

                PurchaseDetail::create([
                    'purchase_id' => $purchaseHeader->purchase_id,
                    'variant_id'  => $variant->variant_id,
                    'quantity'    => $qty,
                    'cost_price'  => $costPrice,
                    'sub_total'   => $detail['sub_total'],
                ]);

                // Increment stock quantity & update cost price on variant
                $variant->increment('quantity', $qty);
                $variant->update(['cost_price' => $costPrice]);

                StockMovement::create([
                    'variant_id'     => $variant->variant_id,
                    'movement_type'  => 'PURCHASE',
                    'quantity'       => $qty,
                    'movement_date'  => now(),
                    'reference_type' => 'PurchaseHeader',
                    'reference_id'   => $purchaseHeader->purchase_id,
                    'note'           => "Purchase Receiving #{$purchaseHeader->purchase_id}",
                    'employee_id'    => $employeeId,
                ]);
            }

            AuditLogService::log(
                action: 'PURCHASE',
                entity: 'PurchaseHeader',
                entityId: $purchaseHeader->purchase_id,
                newValues: [
                    'purchase_id'  => $purchaseHeader->purchase_id,
                    'total_amount' => $totalAmount,
                ],
                userId: $employeeId
            );

            return $purchaseHeader->load(['details.variant.product', 'supplier', 'employee']);
        });
    }

    /**
     * Perform manual stock adjustment.
     *
     * @param int $variantId
     * @param int $quantity (positive for addition, negative for reduction)
     * @param string $movementType ADJUSTMENT, RETURN_IN, RETURN_OUT
     * @param int $employeeId
     * @param string|null $note
     * @return StockMovement
     * @throws Exception
     */
    public function adjustStock(
        int $variantId,
        int $quantity,
        string $movementType,
        int $employeeId,
        ?string $note = null
    ): StockMovement {
        return DB::transaction(function () use ($variantId, $quantity, $movementType, $employeeId, $note) {
            $variant = ProductVariant::where('variant_id', $variantId)->lockForUpdate()->firstOrFail();

            if ($quantity < 0 && $variant->quantity < abs($quantity)) {
                throw new Exception("Cannot reduce stock below 0. Current stock: {$variant->quantity}, Reduction: " . abs($quantity));
            }

            $variant->quantity += $quantity;
            $variant->save();

            $movement = StockMovement::create([
                'variant_id'     => $variant->variant_id,
                'movement_type'  => strtoupper($movementType),
                'quantity'       => $quantity,
                'movement_date'  => now(),
                'reference_type' => 'MANUAL_ADJUSTMENT',
                'reference_id'   => null,
                'note'           => $note ?? 'Manual Inventory Adjustment',
                'employee_id'    => $employeeId,
            ]);

            AuditLogService::log(
                action: 'ADJUSTMENT',
                entity: 'ProductVariant',
                entityId: $variant->variant_id,
                oldValues: ['quantity' => $variant->quantity - $quantity],
                newValues: ['quantity' => $variant->quantity, 'adjustment' => $quantity, 'reason' => $note],
                userId: $employeeId
            );

            return $movement;
        });
    }
}
