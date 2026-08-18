<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\SaleDetail;
use App\Models\SaleHeader;
use App\Models\StockMovement;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class POSService
{
    /**
     * Execute a POS sale checkout inside an atomic database transaction.
     *
     * Industry patterns applied:
     *  - Idempotency Key: prevents duplicate checkouts on network retry
     *  - pessimistic locking (lockForUpdate) to prevent race-condition overselling
     *  - Historical Price Preservation: unit_price stamped at checkout time
     *  - stock_before / stock_after audit trail on every movement
     *  - 10% Tax-Exclusive formula: Tax = round(Net × 0.10, 2)
     *  - invoice_no auto-generated as INV-YYYYMMDD-{sale_id}
     *
     * @param int         $employeeId
     * @param int|null    $customerId
     * @param array       $items          [['variant_id' => 1, 'quantity' => 2, 'discount' => 0.00], ...]
     * @param string      $paymentMethod  CASH, CARD, QR, ABA, BAKONG, GIFT_CARD
     * @param float       $paymentAmount
     * @param float       $overallDiscount
     * @param float       $taxRate        Default 10.00%
     * @param string|null $idempotencyKey
     * @return SaleHeader
     * @throws Exception
     */
    public function checkout(
        int $employeeId,
        ?int $customerId,
        array $items,
        string $paymentMethod = 'CASH',
        float $paymentAmount = 0.0,
        float $overallDiscount = 0.0,
        float $taxRate = 10.00,
        ?string $idempotencyKey = null
    ): SaleHeader {
        $startTime = microtime(true);
        // ── Idempotency Guard ─────────────────────────────────────────────────
        // If the same idempotency key was already processed, return the original
        // sale without executing the transaction again (safe retry for frontend).
        if ($idempotencyKey) {
            $existing = SaleHeader::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing->load(['details.variant.product', 'customer', 'employee', 'payments']);
            }
        }

        return DB::transaction(function () use (
            $employeeId,
            $customerId,
            $items,
            $paymentMethod,
            $paymentAmount,
            $overallDiscount,
            $taxRate,
            $idempotencyKey,
            $startTime
        ) {
            $totalAmount    = 0.0;
            $saleDetailsData = [];

            // ── 1. Validate items & lock variants (pessimistic locking) ──────
            foreach ($items as $item) {
                $variantId   = $item['variant_id'];
                $qty         = (int) $item['quantity'];
                $itemDiscount = (float) ($item['discount'] ?? 0.0);

                if ($qty <= 0) {
                    throw new Exception(
                        "Invalid quantity {$qty} for variant ID {$variantId}. Quantity must be > 0."
                    );
                }

                $variant = ProductVariant::where('variant_id', $variantId)
                    ->lockForUpdate()
                    ->first();

                if (!$variant) {
                    throw new Exception("Product variant ID {$variantId} not found.");
                }

                $isDigital = ($variant->product->product_type ?? 'PHYSICAL_APPAREL') === 'DIGITAL_DOWNLOAD';

                if (!$isDigital && $variant->quantity < $qty) {
                    throw new Exception(
                        "Insufficient stock for SKU [{$variant->sku}]. " .
                        "Requested: {$qty}, Available: {$variant->quantity}."
                    );
                }

                // Historical Price Preservation: stamp sale_price at checkout time.
                // Future catalog price edits will never alter historical receipts.
                $unitPrice = (float) $variant->sale_price;
                $subTotal  = max(0, ($unitPrice * $qty) - $itemDiscount);
                $totalAmount += $subTotal;

                $saleDetailsData[] = [
                    'variant'      => $variant,
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'discount'     => $itemDiscount,
                    'sub_total'    => $subTotal,
                    'is_digital'   => $isDigital,
                    'stock_before' => $variant->quantity,
                ];
            }

            // ── 2. Tax-Exclusive Calculation ─────────────────────────────────
            // Net Amount  = Total Line Items − Overall Discount
            // Tax (10%)   = round(Net × 0.10, 2)
            // Grand Total = Net + Tax
            $netAmount  = max(0, $totalAmount - $overallDiscount);
            $taxAmount  = round($netAmount * ($taxRate / 100), 2);
            $grandTotal = round($netAmount + $taxAmount, 2);

            // ── 3. Create Sale Header ─────────────────────────────────────────
            $saleHeader = SaleHeader::create([
                'customer_id'     => $customerId,
                'employee_id'     => $employeeId,
                'sale_date'       => now(),
                'total_amount'    => $totalAmount,
                'discount'        => $overallDiscount,
                'tax_rate'        => $taxRate,
                'tax_amount'      => $taxAmount,
                'grand_total'     => $grandTotal,
                'payment_status'  => 'PAID',
                'status'          => 'COMPLETED',
                'idempotency_key' => $idempotencyKey,
            ]);

            // Auto-generate invoice_no after we have the sale_id PK.
            $invoiceNo = 'INV-' . now()->format('Ymd') . '-' . str_pad($saleHeader->sale_id, 5, '0', STR_PAD_LEFT);
            $saleHeader->update(['invoice_no' => $invoiceNo]);

            // ── 4. Create Sale Details, deduct stock & write movement ledger ─
            foreach ($saleDetailsData as $detail) {
                /** @var ProductVariant $variant */
                $variant     = $detail['variant'];
                $qty         = $detail['quantity'];
                $isDigital   = $detail['is_digital'];
                $stockBefore = $detail['stock_before'];

                SaleDetail::create([
                    'sale_id'    => $saleHeader->sale_id,
                    'variant_id' => $variant->variant_id,
                    'quantity'   => $qty,
                    'unit_price' => $detail['unit_price'],
                    'discount'   => $detail['discount'],
                    'sub_total'  => $detail['sub_total'],
                ]);

                if (!$isDigital) {
                    // Atomically decrement stock on variant
                    $variant->decrement('quantity', $qty);
                    $stockAfter = $stockBefore - $qty;

                    // Stock Movement Audit Ledger (stock_before + stock_after required)
                    StockMovement::create([
                        'variant_id'     => $variant->variant_id,
                        'movement_type'  => 'SALE',
                        'quantity'       => -$qty,
                        'stock_before'   => $stockBefore,
                        'stock_after'    => $stockAfter,
                        'movement_date'  => now(),
                        'reference_type' => 'SaleHeader',
                        'reference_id'   => $saleHeader->sale_id,
                        'note'           => "POS Sale {$invoiceNo}",
                        'employee_id'    => $employeeId,
                        'created_by'     => $employeeId,
                    ]);
                }
            }

            // ── 5. Create Payment Record ──────────────────────────────────────
            $amountTendered = $paymentAmount > 0 ? $paymentAmount : $grandTotal;
            $changeDue      = max(0, $amountTendered - $grandTotal);
            $refNum         = 'POS-' . strtoupper(Str::random(8));

            Payment::create([
                'sale_id'          => $saleHeader->sale_id,
                'payment_date'     => now(),
                'amount'           => $grandTotal,
                'amount_tendered'  => $amountTendered,
                'change_due'       => $changeDue,
                'payment_method'   => strtoupper($paymentMethod),
                'payment_status'   => 'PAID',
                'transaction_ref'  => $refNum,
                'reference_number' => $refNum,
            ]);

            // ── 6. System Audit Log & Dedicated POS Channel Logging ──────────
            AuditLogService::log(
                action:    'SALE',
                entity:    'SaleHeader',
                entityId:  $saleHeader->sale_id,
                newValues: [
                    'invoice_no'  => $invoiceNo,
                    'grand_total' => $grandTotal,
                    'items_count' => count($items),
                    'method'      => $paymentMethod,
                ],
                userId: $employeeId
            );

            Log::channel('pos')->info('POS Checkout completed', [
                'sale_id'     => $saleHeader->sale_id,
                'invoice_no'  => $invoiceNo,
                'grand_total' => (float) $grandTotal,
                'cashier_id'  => $employeeId,
                'items_count' => count($items),
                'method'      => $paymentMethod,
                'tendered'    => (float) $amountTendered,
                'change'      => (float) $changeDue,
                'duration_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ]);

            return $saleHeader->load(['details.variant.product', 'customer', 'employee', 'payments']);
        });
    }

    /**
     * Void a completed sale and restore physical stock inside an atomic transaction.
     *
     * @param int         $saleId
     * @param int         $employeeId
     * @param string|null $reason
     * @return SaleHeader
     * @throws Exception
     */
    public function voidSale(int $saleId, int $employeeId, ?string $reason = null): SaleHeader
    {
        return DB::transaction(function () use ($saleId, $employeeId, $reason) {
            $saleHeader = SaleHeader::with('details.variant')
                ->where('sale_id', $saleId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($saleHeader->status === 'VOIDED') {
                throw new Exception("Sale #{$saleId} is already voided.");
            }

            if ($saleHeader->status === 'ESTIMATE') {
                throw new Exception("Estimates cannot be voided. Use the estimate deletion endpoint.");
            }

            $saleHeader->update([
                'status'         => 'VOIDED',
                'payment_status' => 'REFUNDED',
            ]);

            // Restore physical stock for each line item
            foreach ($saleHeader->details as $detail) {
                $variant = ProductVariant::where('variant_id', $detail->variant_id)
                    ->lockForUpdate()
                    ->first();

                if ($variant) {
                    $stockBefore = $variant->quantity;
                    $variant->increment('quantity', $detail->quantity);
                    $stockAfter = $stockBefore + $detail->quantity;

                    StockMovement::create([
                        'variant_id'     => $variant->variant_id,
                        'movement_type'  => 'RETURN_IN',
                        'quantity'       => $detail->quantity,
                        'stock_before'   => $stockBefore,
                        'stock_after'    => $stockAfter,
                        'movement_date'  => now(),
                        'reference_type' => 'SaleHeader',
                        'reference_id'   => $saleHeader->sale_id,
                        'note'           => "Voided Sale #{$saleHeader->sale_id}. Reason: " . ($reason ?? 'N/A'),
                        'created_by'     => $employeeId,
                    ]);
                }
            }

            AuditLogService::log(
                action:    'VOID_SALE',
                entity:    'SaleHeader',
                entityId:  $saleHeader->sale_id,
                oldValues: ['status' => 'COMPLETED', 'payment_status' => 'PAID'],
                newValues: ['status' => 'VOIDED', 'payment_status' => 'REFUNDED', 'reason' => $reason],
                userId:    $employeeId
            );

            return $saleHeader->fresh(['details.variant.product', 'customer', 'employee', 'payments']);
        });
    }
}
