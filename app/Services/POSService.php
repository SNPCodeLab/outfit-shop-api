<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\SaleDetail;
use App\Models\SaleHeader;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class POSService
{
    /**
     * Execute a POS sale checkout inside a database transaction.
     *
     * @param int $employeeId
     * @param int|null $customerId
     * @param array $items Array of ['variant_id' => int, 'quantity' => int, 'discount' => float]
     * @param string $paymentMethod CASH, CARD, QR, ABA
     * @param float $paymentAmount
     * @param float $overallDiscount
     * @return SaleHeader
     * @throws Exception
     */
    public function checkout(
        int $employeeId,
        ?int $customerId,
        array $items,
        string $paymentMethod = 'CASH',
        float $paymentAmount = 0.0,
        float $overallDiscount = 0.0
    ): SaleHeader {
        return DB::transaction(function () use (
            $employeeId,
            $customerId,
            $items,
            $paymentMethod,
            $paymentAmount,
            $overallDiscount
        ) {
            $totalAmount = 0.0;
            $saleDetailsData = [];

            // 1. Validate items & lock variants for update to prevent negative stock race conditions
            foreach ($items as $item) {
                $variantId = $item['variant_id'];
                $qty = (int) $item['quantity'];
                $itemDiscount = (float) ($item['discount'] ?? 0.0);

                if ($qty <= 0) {
                    throw new Exception("Invalid quantity {$qty} for variant ID {$variantId}. Quantity must be > 0.");
                }

                $variant = ProductVariant::where('variant_id', $variantId)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw new Exception("Product variant ID {$variantId} not found.");
                }

                $isDigital = ($variant->product->product_type ?? 'PHYSICAL_APPAREL') === 'DIGITAL_DOWNLOAD';

                if (!$isDigital && $variant->quantity < $qty) {
                    throw new Exception("Insufficient stock for SKU [{$variant->sku}]. Requested: {$qty}, Available: {$variant->quantity}.");
                }

                // Historical Price Preservation: use variant's current sale_price
                $unitPrice = (float) $variant->sale_price;
                $subTotal = max(0, ($unitPrice * $qty) - $itemDiscount);
                $totalAmount += $subTotal;

                $saleDetailsData[] = [
                    'variant'     => $variant,
                    'quantity'    => $qty,
                    'unit_price'  => $unitPrice,
                    'discount'    => $itemDiscount,
                    'sub_total'   => $subTotal,
                    'is_digital'  => $isDigital,
                ];
            }

            $grandTotal = max(0, $totalAmount - $overallDiscount);

            // 2. Create Sale Header
            $saleHeader = SaleHeader::create([
                'customer_id'  => $customerId,
                'employee_id'  => $employeeId,
                'sale_date'    => now(),
                'total_amount' => $totalAmount,
                'discount'     => $overallDiscount,
                'grand_total'  => $grandTotal,
                'status'       => 'COMPLETED',
            ]);

            // 3. Create Details, deduct inventory & log stock movement
            foreach ($saleDetailsData as $detail) {
                /** @var ProductVariant $variant */
                $variant = $detail['variant'];
                $qty = $detail['quantity'];
                $isDigital = $detail['is_digital'];

                SaleDetail::create([
                    'sale_id'    => $saleHeader->sale_id,
                    'variant_id' => $variant->variant_id,
                    'quantity'   => $qty,
                    'unit_price' => $detail['unit_price'],
                    'discount'   => $detail['discount'],
                    'sub_total'  => $detail['sub_total'],
                ]);

                // Atomically update stock only for physical products
                if (!$isDigital) {
                    $variant->decrement('quantity', $qty);

                    // Stock Movement Audit
                    StockMovement::create([
                        'variant_id'     => $variant->variant_id,
                        'movement_type'  => 'SALE',
                        'quantity'       => -$qty,
                        'movement_date'  => now(),
                        'reference_type' => 'SaleHeader',
                        'reference_id'   => $saleHeader->sale_id,
                        'note'           => "POS Sale #{$saleHeader->sale_id}",
                        'employee_id'    => $employeeId,
                    ]);
                }
            }

            // 4. Create Payment Record
            Payment::create([
                'sale_id'          => $saleHeader->sale_id,
                'payment_date'     => now(),
                'amount'           => $paymentAmount > 0 ? $paymentAmount : $grandTotal,
                'payment_method'   => strtoupper($paymentMethod),
                'payment_status'   => 'PAID',
                'reference_number' => 'POS-' . $saleHeader->sale_id . '-' . time(),
            ]);

            // 5. System Audit Log
            AuditLogService::log(
                action: 'SALE',
                entity: 'SaleHeader',
                entityId: $saleHeader->sale_id,
                newValues: [
                    'sale_id'     => $saleHeader->sale_id,
                    'grand_total' => $grandTotal,
                    'items_count' => count($items),
                ],
                userId: $employeeId
            );

            return $saleHeader->load(['details.variant.product', 'customer', 'employee', 'payments']);
        });
    }

    /**
     * Void a sale and restore stock inside a transaction.
     *
     * @param int $saleId
     * @param int $employeeId
     * @param string|null $reason
     * @return SaleHeader
     * @throws Exception
     */
    public function voidSale(int $saleId, int $employeeId, ?string $reason = null): SaleHeader
    {
        return DB::transaction(function () use ($saleId, $employeeId, $reason) {
            $saleHeader = SaleHeader::with('details')->where('sale_id', $saleId)->lockForUpdate()->firstOrFail();

            if ($saleHeader->status === 'VOIDED') {
                throw new Exception("Sale #{$saleId} is already voided.");
            }

            $saleHeader->status = 'VOIDED';
            $saleHeader->save();

            // Revert stock for each variant
            foreach ($saleHeader->details as $detail) {
                $variant = ProductVariant::where('variant_id', $detail->variant_id)->lockForUpdate()->first();
                if ($variant) {
                    $variant->increment('quantity', $detail->quantity);

                    StockMovement::create([
                        'variant_id'     => $variant->variant_id,
                        'movement_type'  => 'RETURN_IN',
                        'quantity'       => $detail->quantity,
                        'movement_date'  => now(),
                        'reference_type' => 'SaleHeader',
                        'reference_id'   => $saleHeader->sale_id,
                        'note'           => "Voided Sale #{$saleHeader->sale_id}. Reason: " . ($reason ?? 'N/A'),
                        'employee_id'    => $employeeId,
                    ]);
                }
            }

            AuditLogService::log(
                action: 'VOID_SALE',
                entity: 'SaleHeader',
                entityId: $saleHeader->sale_id,
                oldValues: ['status' => 'COMPLETED'],
                newValues: ['status' => 'VOIDED', 'reason' => $reason],
                userId: $employeeId
            );

            return $saleHeader;
        });
    }
}
