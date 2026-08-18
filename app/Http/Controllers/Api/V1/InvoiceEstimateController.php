<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\SaleDetail;
use App\Models\SaleHeader;
use App\Models\StockMovement;
use App\Services\AuditLogService;
use App\Services\POSService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InvoiceEstimateController extends BaseApiController
{
    public function __construct(protected POSService $posService) {}

    /**
     * List all Sales Orders, Invoices & Estimates with status filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = SaleHeader::with(['customer', 'employee', 'details.variant.product', 'details.variant.size', 'details.variant.color', 'payments']);

        // Filter by Document Status (ESTIMATE, INVOICE, PAID, PENDING, VOIDED, COMPLETED)
        if ($status = $request->input('status')) {
            $query->where('status', strtoupper($status));
        }

        // Filter by Customer ID
        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        // Filter by Date Range
        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('sale_date', '>=', $fromDate);
        }
        if ($toDate = $request->input('to_date')) {
            $query->whereDate('sale_date', '<=', $toDate);
        }

        $records = $query->orderBy('sale_id', 'desc')->paginate((int) ($request->input('per_page', 20)));

        // Financial Totals Summary
        $totalInvoiced = (float) SaleHeader::where('status', '!=', 'VOIDED')->sum('grand_total');
        $totalCollected = (float) Payment::whereIn('payment_status', ['PAID', 'COMPLETED'])->sum('amount');
        $outstandingBalance = max(0, $totalInvoiced - $totalCollected);

        $summary = [
            'total_invoiced_usd' => round($totalInvoiced, 2),
            'total_collected_usd' => round($totalCollected, 2),
            'outstanding_balance_usd' => round($outstandingBalance, 2),
            'total_documents_count' => $records->total(),
        ];

        return $this->successResponse([
            'summary' => $summary,
            'documents' => $records,
        ], 'SalesBinder Invoices & Estimates retrieved');
    }

    /**
     * Create a new Quotation / Estimate (Does not immediately deduct inventory).
     */
    public function createEstimate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,customer_id',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,variant_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.discount' => 'nullable|numeric|min:0',
            'overall_discount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:500',
            'valid_until' => 'nullable|date',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $employeeId = $request->user()?->employee_id ?? $request->user()?->id ?? 1;
            $taxRate = (float) ($validated['tax_rate'] ?? 10.00);
            $overallDiscount = (float) ($validated['overall_discount'] ?? 0.0);

            $totalAmount = 0.0;
            $detailsData = [];
            $stockWarnings = [];

            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::with(['product', 'size', 'color'])->findOrFail($item['variant_id']);
                $qty = (int) $item['quantity'];
                $itemDiscount = (float) ($item['discount'] ?? 0.0);
                $unitPrice = (float) $variant->sale_price;
                $lineTotal = max(0, ($unitPrice * $qty) - $itemDiscount);

                if ($variant->quantity < $qty) {
                    $stockWarnings[] = "SKU [{$variant->sku}] has {$variant->quantity} units available (requested {$qty}).";
                }

                $totalAmount += $lineTotal;

                $detailsData[] = [
                    'variant_id' => $variant->variant_id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_amount' => $itemDiscount,
                    'sub_total' => $lineTotal,
                ];
            }

            $netAmount = max(0, $totalAmount - $overallDiscount);
            $taxAmount = round($netAmount * ($taxRate / 100), 2);
            $grandTotal = round($netAmount + $taxAmount, 2);

            $estimate = SaleHeader::create([
                'customer_id' => $validated['customer_id'],
                'employee_id' => $employeeId,
                'sale_date' => now(),
                'total_amount' => $totalAmount,
                'discount' => $overallDiscount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'grand_total' => $grandTotal,
                'payment_status' => 'UNPAID',
                'status' => 'ESTIMATE',
                'notes' => $validated['notes'] ?? null,
            ]);

            $estimateNo = 'EST-'.now()->format('Ymd').'-'.str_pad($estimate->sale_id, 5, '0', STR_PAD_LEFT);
            $estimate->update(['invoice_no' => $estimateNo]);

            foreach ($detailsData as $detail) {
                SaleDetail::create(array_merge($detail, ['sale_id' => $estimate->sale_id]));
            }

            AuditLogService::log(
                action: 'CREATE_ESTIMATE',
                entity: 'SaleHeader',
                entityId: $estimate->sale_id,
                newValues: ['status' => 'ESTIMATE', 'invoice_no' => $estimateNo, 'grand_total' => $grandTotal]
            );

            return $this->createdResponse(
                $estimate->load(['customer', 'employee', 'details.variant.product', 'details.variant.size', 'details.variant.color']),
                'Estimate quote created successfully. Stock is reserved but not yet deducted.',
                '/api/v1/invoices/'.$estimate->sale_id
            );
        });
    }

    /**
     * 1-Click Convert an approved Estimate into an Official Invoice & deduct stock with full movement ledger.
     */
    public function convertEstimateToInvoice(Request $request, int $id): JsonResponse
    {
        $estimate = SaleHeader::with(['details.variant.product'])->findOrFail($id);

        if ($estimate->status === 'COMPLETED' || $estimate->status === 'PAID') {
            return $this->conflictResponse(
                'This document is already an active or paid invoice.',
                'DOCUMENT_ALREADY_CONVERTED',
                ['current_status' => $estimate->status, 'invoice_no' => $estimate->invoice_no]
            );
        }

        try {
            return DB::transaction(function () use ($estimate, $request) {
                $employeeId = $request->user()?->employee_id ?? $request->user()?->id ?? 1;

                // Deduct physical inventory, verify stock availability, and write StockMovement audit trail
                foreach ($estimate->details as $detail) {
                    $variant = ProductVariant::lockForUpdate()->find($detail->variant_id);
                    if (! $variant) {
                        throw new Exception("Product variant ID {$detail->variant_id} not found.");
                    }

                    $isDigital = ($variant->product->product_type ?? 'PHYSICAL_APPAREL') === 'DIGITAL_DOWNLOAD';

                    if (! $isDigital) {
                        if ($variant->quantity < $detail->quantity) {
                            throw new Exception("Insufficient stock for SKU [{$variant->sku}]. Available: {$variant->quantity}, Required: {$detail->quantity}.");
                        }

                        $stockBefore = (int) $variant->quantity;
                        $variant->decrement('quantity', $detail->quantity);
                        $stockAfter = $stockBefore - $detail->quantity;

                        StockMovement::create([
                            'variant_id' => $variant->variant_id,
                            'movement_type' => 'SALE',
                            'quantity' => -$detail->quantity,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                            'movement_date' => now(),
                            'reference_type' => 'SaleHeader',
                            'reference_id' => $estimate->sale_id,
                            'note' => "Converted from Estimate #{$estimate->sale_id}",
                            'created_by' => $employeeId,
                        ]);
                    }
                }

                $invoiceNo = 'INV-'.now()->format('Ymd').'-'.str_pad($estimate->sale_id, 5, '0', STR_PAD_LEFT);
                $paymentMethod = $request->input('payment_method');
                $paymentStatus = $paymentMethod ? 'PAID' : 'UNPAID';

                $estimate->update([
                    'status' => 'COMPLETED',
                    'invoice_no' => $invoiceNo,
                    'payment_status' => $paymentStatus,
                ]);

                // Auto-register payment if provided
                if ($paymentMethod) {
                    Payment::create([
                        'sale_id' => $estimate->sale_id,
                        'payment_method' => strtoupper($paymentMethod),
                        'amount' => $estimate->grand_total,
                        'amount_tendered' => $estimate->grand_total,
                        'change_due' => 0.00,
                        'payment_date' => now(),
                        'transaction_ref' => 'CONV-'.strtoupper(Str::random(8)),
                        'payment_status' => 'PAID',
                    ]);
                }

                AuditLogService::log(
                    action: 'CONVERT_ESTIMATE_TO_INVOICE',
                    entity: 'SaleHeader',
                    entityId: $estimate->sale_id,
                    oldValues: ['status' => 'ESTIMATE'],
                    newValues: ['status' => 'COMPLETED', 'invoice_no' => $invoiceNo, 'payment_status' => $paymentStatus],
                    userId: $employeeId
                );

                return $this->successResponse(
                    $estimate->fresh(['customer', 'employee', 'details.variant.product', 'payments']),
                    'Estimate #'.$estimate->sale_id." successfully converted to official Invoice [{$invoiceNo}]!"
                );
            });
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400, 'ESTIMATE_CONVERSION_FAILED');
        }
    }

    /**
     * Render a SalesBinder-style professional printable A4/PDF Invoice View.
     */
    public function renderInvoiceHtml(int $id): Response
    {
        $sale = SaleHeader::with([
            'customer',
            'employee',
            'details.variant.product.category',
            'details.variant.size',
            'details.variant.color',
            'payments',
        ])->findOrFail($id);

        $customer = $sale->customer;
        $employee = $sale->employee;
        $details = $sale->details;
        $payments = $sale->payments;

        $totalPaid = $payments->sum('amount');
        $balanceDue = max(0, $sale->grand_total - $totalPaid);
        $statusBg = $balanceDue <= 0 ? '#f0fdf4' : '#fffbeb';
        $statusBorder = $balanceDue <= 0 ? '#bbf7d0' : '#fde68a';
        $statusColor = $balanceDue <= 0 ? '#166534' : '#92400e';
        $statusText = $balanceDue <= 0 ? 'PAID IN FULL' : ($totalPaid > 0 ? 'PARTIALLY PAID' : 'UNPAID');

        $customerName = htmlspecialchars($customer->customer_name ?? 'Walk-in Retail Guest');
        $customerPhone = htmlspecialchars($customer->phone ?? 'N/A');
        $customerAddress = htmlspecialchars($customer->address ?? 'Phnom Penh, Cambodia');

        $itemsListHtml = '';
        foreach ($details as $detail) {
            $variant = $detail->variant;
            $productName = htmlspecialchars($variant->product->product_name ?? 'Apparel Item');
            $size = htmlspecialchars($variant->size->size_name ?? 'STD');
            $color = htmlspecialchars($variant->color->color_name ?? 'Standard');
            $qty = $detail->quantity;
            $unitPrice = number_format($detail->unit_price, 2);
            $lineTotal = number_format($detail->sub_total, 2);

            $itemsListHtml .= "
            <tr>
                <td style='padding: 10px 14px; border-bottom: 1px solid #e4e4e7;'>
                    <div style='font-weight: 700; color: #09090b; font-size: 13px;'>{$productName}</div>
                    <div style='font-size: 11px; color: #71717a;'>Size: {$size} • Color: {$color} • SKU: {$variant->sku}</div>
                </td>
                <td style='padding: 10px 14px; text-align: center; border-bottom: 1px solid #e4e4e7; font-weight: 600;'>{$qty}</td>
                <td style='padding: 10px 14px; text-align: right; border-bottom: 1px solid #e4e4e7; font-family: ui-monospace, monospace;'>\${$unitPrice}</td>
                <td style='padding: 10px 14px; text-align: right; border-bottom: 1px solid #e4e4e7; font-family: ui-monospace, monospace; font-weight: 700;'>\${$lineTotal}</td>
            </tr>";
        }

        $receiptItemsHtml = '';
        foreach ($details as $detail) {
            $variant = $detail->variant;
            $productName = htmlspecialchars(strtoupper(substr($variant->product->product_name ?? 'ITEM', 0, 18)));
            $qty = $detail->quantity;
            $lineTotal = number_format($detail->sub_total, 2);
            $receiptItemsHtml .= "
            <div style='display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px;'>
                <span>{$qty}x {$productName}</span>
                <span style='font-weight: 700;'>\${$lineTotal}</span>
            </div>";
        }

        $subtotal = number_format($sale->total_amount, 2);
        $discount = number_format($sale->discount, 2);
        $tax = number_format($sale->tax_amount, 2);
        $grand = number_format($sale->grand_total, 2);
        $paid = number_format($totalPaid, 2);
        $due = number_format($balanceDue, 2);
        $saleDate = date('d M Y - H:i', strtotime($sale->sale_date ?? now()));

        $html = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Tax Invoice #INV-{$sale->sale_id} | Documentation</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'>
    <style>
        :root {
            --background: #f4f4f5;
            --foreground: #09090b;
            --card: #ffffff;
            --card-foreground: #09090b;
            --border: #e4e4e7;
            --muted: #f4f4f5;
            --muted-foreground: #71717a;
            --primary: #18181b;
            --primary-foreground: #fafafa;
            --radius: 3px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: var(--foreground);
            background-color: var(--background);
            line-height: 1.5;
            font-size: 13px;
            padding: 32px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .screen-container {
            max-width: 820px;
            margin: 0 auto;
        }

        /* ── Top Navigation & Mode Switcher ── */
        .controls-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .mode-tabs-group {
            display: inline-flex;
            background: #e4e4e7;
            padding: 3px;
            border-radius: var(--radius);
        }
        .mode-tab-btn {
            background: transparent;
            border: none;
            padding: 7px 16px;
            font-size: 12px;
            font-weight: 600;
            color: #71717a;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .mode-tab-btn.active {
            background: #ffffff;
            color: #18181b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .toolbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
        }
        .btn-black {
            background: #18181b;
            color: #ffffff;
            border: 1px solid #18181b;
        }
        .btn-black:hover { background: #27272a; }
        .btn-white {
            background: #ffffff;
            color: #18181b;
            border: 1px solid #e4e4e7;
        }
        .btn-white:hover { background: #f4f4f5; }

        /* ═══════════════════════════════════════════════
           TACTILE RECEIPT PRINTER MACHINE (dqnamo style)
           ═══════════════════════════════════════════════ */
        .receipt-printer-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 0 40px;
        }
        .printer-machine {
            position: relative;
            width: 100%;
            max-width: 380px;
            border-radius: 1.5rem;
            border: 1px solid #27272a;
            background: linear-gradient(180deg, #1c1c1f 0%, #121214 100%);
            padding: 0.85rem;
            padding-bottom: 2.2rem;
            box-shadow: 0 24px 48px -16px rgba(0, 0, 0, 0.5), 0 8px 16px -6px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.12);
            z-index: 10;
        }
        .printer-screen {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            border: 1px solid #27272a;
            background: #09090b;
            padding: 1.15rem;
            color: #fafafa;
            box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.7);
        }
        .screen-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .printer-status-row {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 500;
            color: #a1a1aa;
        }
        .status-spinner {
            display: inline-block;
            width: 15px;
            height: 15px;
            border: 2px solid #52525b;
            border-top-color: #fafafa;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        .status-complete-icon {
            color: #22c55e;
            font-size: 15px;
            display: inline-block;
        }
        .printer-slot {
            position: absolute;
            left: 1.5rem;
            right: 1.5rem;
            bottom: 0.8rem;
            height: 9px;
            border-radius: 4px;
            background: #000000;
            border: 1px solid #27272a;
            box-shadow: inset 0 3px 6px #000000;
            z-index: 40;
        }

        /* ── Receipt Output with Realistic Paper Styling ── */
        .receipt-output-container {
            position: relative;
            z-index: 5;
            margin-top: -1.2rem;
            width: 320px;
            overflow: hidden;
            padding-bottom: 2rem;
        }
        .receipt-paper-wrapper {
            position: relative;
            transform: translateY(0%);
            transition: opacity 0.16s ease;
        }
        .receipt-paper-wrapper.stepped-feed {
            animation: steppedPrintingAnimation 1.75s linear forwards;
        }

        @keyframes steppedPrintingAnimation {
            0% { transform: translateY(calc(-100% + 2px)); }
            7.5% { transform: translateY(-91%); }
            10.5% { transform: translateY(-91%); }
            18% { transform: translateY(-81%); }
            21% { transform: translateY(-81%); }
            28.5% { transform: translateY(-70%); }
            31.5% { transform: translateY(-70%); }
            39% { transform: translateY(-58%); }
            42% { transform: translateY(-58%); }
            49.5% { transform: translateY(-45%); }
            52.5% { transform: translateY(-45%); }
            60% { transform: translateY(-32%); }
            63% { transform: translateY(-32%); }
            70.5% { transform: translateY(-20%); }
            73.5% { transform: translateY(-20%); }
            81% { transform: translateY(-10%); }
            84% { transform: translateY(-10%); }
            91.5% { transform: translateY(-3%); }
            94.5% { transform: translateY(-3%); }
            100% { transform: translateY(0%); }
        }

        .receipt-paper {
            background: #ffffff;
            color: #18181b;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            padding: 28px 22px 36px;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15), 0 2px 4px rgba(0, 0, 0, 0.05);
            /* Jagged Sawtooth Tear Edge at Bottom (40 teeth) */
            clip-path: polygon(
                0 0, 100% 0, 100% calc(100% - 4px),
                100% calc(100% - 4px), 98.75% 100%, 97.5% calc(100% - 4px), 96.25% 100%, 95% calc(100% - 4px),
                93.75% 100%, 92.5% calc(100% - 4px), 91.25% 100%, 90% calc(100% - 4px), 88.75% 100%, 87.5% calc(100% - 4px),
                86.25% 100%, 85% calc(100% - 4px), 83.75% 100%, 82.5% calc(100% - 4px), 81.25% 100%, 80% calc(100% - 4px),
                78.75% 100%, 77.5% calc(100% - 4px), 76.25% 100%, 75% calc(100% - 4px), 73.75% 100%, 72.5% calc(100% - 4px),
                71.25% 100%, 70% calc(100% - 4px), 68.75% 100%, 67.5% calc(100% - 4px), 66.25% 100%, 65% calc(100% - 4px),
                63.75% 100%, 62.5% calc(100% - 4px), 61.25% 100%, 60% calc(100% - 4px), 58.75% 100%, 57.5% calc(100% - 4px),
                56.25% 100%, 55% calc(100% - 4px), 53.75% 100%, 52.5% calc(100% - 4px), 51.25% 100%, 50% calc(100% - 4px),
                48.75% 100%, 47.5% calc(100% - 4px), 46.25% 100%, 45% calc(100% - 4px), 43.75% 100%, 42.5% calc(100% - 4px),
                41.25% 100%, 40% calc(100% - 4px), 38.75% 100%, 37.5% calc(100% - 4px), 36.25% 100%, 35% calc(100% - 4px),
                33.75% 100%, 32.5% calc(100% - 4px), 31.25% 100%, 30% calc(100% - 4px), 28.75% 100%, 27.5% calc(100% - 4px),
                26.25% 100%, 25% calc(100% - 4px), 23.75% 100%, 22.5% calc(100% - 4px), 21.25% 100%, 20% calc(100% - 4px),
                18.75% 100%, 17.5% calc(100% - 4px), 16.25% 100%, 15% calc(100% - 4px), 13.75% 100%, 12.5% calc(100% - 4px),
                11.25% 100%, 10% calc(100% - 4px), 8.75% 100%, 7.5% calc(100% - 4px), 6.25% 100%, 5% calc(100% - 4px),
                3.75% 100%, 2.5% calc(100% - 4px), 1.25% 100%, 0% calc(100% - 4px)
            );
        }

        .dashed-divider {
            border-top: 1px dashed #d4d4d8;
            margin: 14px 0;
        }

        /* ═══════════════════════════════════════════════
           FORMAL FULL-WIDTH A4 DOCUMENT SHEET (Clean Vector)
           ═══════════════════════════════════════════════ */
        .invoice-document-sheet {
            width: 100%;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 40px 48px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            display: none;
        }

        .doc-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #09090b;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .doc-company-title {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.02em;
            text-transform: uppercase;
        }
        .doc-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }
        .doc-meta-card {
            background: #fafafa;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 14px 18px;
        }
        .doc-meta-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #71717a;
            margin-bottom: 6px;
        }

        .doc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .doc-table th {
            background: #fafafa;
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #71717a;
            text-align: left;
        }

        .doc-calc-grid {
            display: grid;
            grid-template-columns: 1fr 280px;
            gap: 24px;
            margin-top: 16px;
        }
        .doc-summary-box {
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: #fafafa;
            padding: 16px 20px;
        }
        .doc-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 13px;
        }
        .doc-grand-row {
            border-top: 2px solid #09090b;
            padding-top: 8px;
            margin-top: 6px;
            font-size: 15px;
            font-weight: 800;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ═══════════════════════════════════════════════
           STRICT @MEDIA PRINT RULES FOR FLAWLESS 1-PAGE A4
           ═══════════════════════════════════════════════ */
        @page {
            size: A4 portrait;
            margin: 10mm 15mm 10mm 15mm;
        }
        @media print {
            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                font-size: 11pt !important;
            }
            .controls-toolbar, .receipt-printer-section, .no-print {
                display: none !important;
            }
            .screen-container {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .invoice-document-sheet {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                page-break-inside: avoid !important;
            }
            .doc-meta-card, .doc-summary-box {
                background: #ffffff !important;
                border: 1px solid #cccccc !important;
            }
            .doc-table th {
                background: #f4f4f4 !important;
            }
        }
    </style>
</head>
<body>
    <div class='screen-container'>
        <!-- Controls Toolbar -->
        <div class='controls-toolbar'>
            <div class='mode-tabs-group'>
                <button class='mode-tab-btn active' id='tab-printer' onclick='switchView(\"printer\")'>
                    <i class='fa-solid fa-receipt' style='margin-right: 4px;'></i> Tactile Printer
                </button>
                <button class='mode-tab-btn' id='tab-sheet' onclick='switchView(\"sheet\")'>
                    <i class='fa-solid fa-file-lines' style='margin-right: 4px;'></i> A4 Document Sheet
                </button>
            </div>
            <div style='display: flex; gap: 8px;'>
                <button class='toolbar-btn btn-white' onclick='replayPrinterAnimation()'>
                    <i class='fa-solid fa-rotate-right'></i> Replay Print
                </button>
                <button class='toolbar-btn btn-black' onclick='window.print()'>
                    <i class='fa-solid fa-print'></i> Print / Save PDF
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════
             1. TACTILE RECEIPT PRINTER MACHINE
             ═══════════════════════════════════════════════ -->
        <section class='receipt-printer-section' id='view-printer-mode'>
            <div class='printer-machine'>
                <div class='printer-screen'>
                    <div class='screen-top'>
                        <div style='font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #a1a1aa;'>
                            POINT OF SALE
                        </div>
                        <span style='font-size: 10px; background: #27272a; padding: 2px 7px; border-radius: 4px; color: #d4d4d8; font-family: monospace;'>#INV-{$sale->sale_id}</span>
                    </div>
                    <div style='display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 12px;'>
                        <div>
                            <div style='font-size: 13px; font-weight: 700;'>Sale Transaction</div>
                            <div style='font-size: 11px; color: #a1a1aa;'>{$details->count()} item(s) included</div>
                        </div>
                        <div style='text-align: right;'>
                            <div style='font-size: 10px; color: #a1a1aa; text-transform: uppercase;'>Total</div>
                            <div style='font-size: 18px; font-weight: 800; color: #ffffff; font-family: monospace;'>\${$grand}</div>
                        </div>
                    </div>
                    <div class='printer-status-row'>
                        <span class='status-spinner' id='status-spinner' style='display: none;'></span>
                        <i class='fa-solid fa-circle-check status-complete-icon' id='status-check'></i>
                        <span id='status-text'>Order complete</span>
                    </div>
                </div>
                <div class='printer-slot'></div>
            </div>

            <!-- Realistic Receipt Output -->
            <div class='receipt-output-container'>
                <div class='receipt-paper-wrapper' id='receipt-paper-wrap'>
                    <article class='receipt-paper'>
                        <div style='text-align: center; margin-bottom: 14px;'>
                            <div style='font-size: 13px; font-weight: 800; letter-spacing: -0.02em;'>STORE RECEIPT</div>
                            <div style='font-size: 9px; color: #71717a; text-transform: uppercase; margin-top: 2px;'>Official Sales Transaction</div>
                        </div>

                        <div style='font-size: 10px; color: #52525b; line-height: 1.5;'>
                            <div>Order #: INV-000{$sale->sale_id}</div>
                            <div>Date: {$saleDate}</div>
                            <div>Status: {$statusText}</div>
                        </div>

                        <div class='dashed-divider'></div>

                        <div>
                            {$receiptItemsHtml}
                        </div>

                        <div class='dashed-divider'></div>

                        <div style='font-size: 11px; line-height: 1.6;'>
                            <div style='display: flex; justify-content: space-between;'><span>Subtotal:</span><span>\${$subtotal}</span></div>
                            <div style='display: flex; justify-content: space-between;'><span>Tax (10% VAT):</span><span>+\${$tax}</span></div>
                            <div style='display: flex; justify-content: space-between; font-weight: 800; font-size: 13px; margin-top: 4px; border-top: 1px solid #18181b; padding-top: 4px;'>
                                <span>TOTAL PAID</span>
                                <span>\${$grand}</span>
                            </div>
                        </div>

                        <div class='dashed-divider'></div>

                        <div style='text-align: center; margin-top: 14px;'>
                            <div style='display: inline-block; letter-spacing: 2px; font-size: 18px; font-family: monospace; line-height: 1;'>
                                ||| | | |||| || |||| | |||
                            </div>
                            <div style='font-size: 9px; color: #71717a; margin-top: 4px;'>* INV-{$sale->sale_id} *</div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- ═══════════════════════════════════════════════
             2. FORMAL FULL-WIDTH A4 DOCUMENT SHEET
             ═══════════════════════════════════════════════ -->
        <div class='invoice-document-sheet' id='view-sheet-mode'>
            <div class='doc-header-row'>
                <div>
                    <h1 class='doc-company-title'>TAX INVOICE</h1>
                    <div style='font-size: 12px; color: #71717a; margin-top: 2px;'>Official Sales &amp; Billing Document</div>
                </div>
                <div style='text-align: right;'>
                    <div style='font-size: 14px; font-weight: 700;'>#INV-000{$sale->sale_id}</div>
                    <div style='font-size: 12px; color: #71717a;'>Date: {$saleDate}</div>
                </div>
            </div>

            <div class='doc-meta-grid'>
                <div class='doc-meta-card'>
                    <div class='doc-meta-label'>Billed To (Customer)</div>
                    <div style='font-weight: 700; font-size: 14px;'>{$customerName}</div>
                    <div style='font-size: 12px; color: #71717a; margin-top: 2px;'>Phone: {$customerPhone}</div>
                    <div style='font-size: 12px; color: #71717a;'>Address: {$customerAddress}</div>
                </div>
                <div class='doc-meta-card'>
                    <div class='doc-meta-label'>Payment &amp; Status</div>
                    <div style='font-size: 13px;'>Status: <strong style='color: {$statusColor};'>{$statusText}</strong></div>
                    <div style='font-size: 12px; color: #71717a; margin-top: 2px;'>Payment Method: Cash / Card / KHQR</div>
                    <div style='font-size: 12px; color: #71717a;'>Terms: Due Upon Receipt</div>
                </div>
            </div>

            <table class='doc-table'>
                <thead>
                    <tr>
                        <th>Item Description</th>
                        <th style='text-align: center;'>Qty</th>
                        <th style='text-align: right;'>Unit Price</th>
                        <th style='text-align: right;'>Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    {$itemsListHtml}
                </tbody>
            </table>

            <div class='doc-calc-grid'>
                <div class='doc-meta-card' style='background: #fafafa; font-size: 12px; color: #71717a;'>
                    <div style='font-weight: 700; color: #09090b; margin-bottom: 4px; text-transform: uppercase; font-size: 11px;'>Notes &amp; Instructions</div>
                    <div>• All goods purchased are recorded in the inventory stock ledger.</div>
                    <div>• Returns or exchanges accepted within 7 days with this invoice.</div>
                </div>

                <div class='doc-summary-box'>
                    <div class='doc-summary-row'><span>Subtotal:</span><span style='font-family: monospace;'>\${$subtotal}</span></div>
                    <div class='doc-summary-row'><span>Discount:</span><span style='font-family: monospace;'>-\${$discount}</span></div>
                    <div class='doc-summary-row'><span>Tax (10% VAT):</span><span style='font-family: monospace;'>+\${$tax}</span></div>
                    <div class='doc-summary-row doc-grand-row'><span>Grand Total:</span><span style='font-family: monospace;'>\${$grand}</span></div>
                    <div class='doc-summary-row' style='color: #166534; font-weight: 600; margin-top: 4px;'><span>Paid:</span><span style='font-family: monospace;'>\${$paid}</span></div>
                    <div class='doc-summary-row' style='color: #92400e; font-weight: 700; border-top: 1px dashed #d4d4d8; padding-top: 4px; margin-top: 4px;'><span>Balance Due:</span><span style='font-family: monospace;'>\${$due}</span></div>
                </div>
            </div>

            <div style='text-align: center; margin-top: 36px; padding-top: 16px; border-top: 1px solid var(--border); font-size: 11px; color: #a1a1aa;'>
                Thank you for your business.
            </div>
        </div>
    </div>

    <script>
        function switchView(mode) {
            const printerSection = document.getElementById('view-printer-mode');
            const sheetSection = document.getElementById('view-sheet-mode');
            const tabPrinter = document.getElementById('tab-printer');
            const tabSheet = document.getElementById('tab-sheet');

            if (mode === 'printer') {
                printerSection.style.display = 'flex';
                sheetSection.style.display = 'none';
                tabPrinter.classList.add('active');
                tabSheet.classList.remove('active');
            } else {
                printerSection.style.display = 'none';
                sheetSection.style.display = 'block';
                tabPrinter.classList.remove('active');
                tabSheet.classList.add('active');
            }
        }

        function replayPrinterAnimation() {
            switchView('printer');
            const wrap = document.getElementById('receipt-paper-wrap');
            const spinner = document.getElementById('status-spinner');
            const check = document.getElementById('status-check');
            const text = document.getElementById('status-text');

            // Reset
            wrap.className = 'receipt-paper-wrapper';
            wrap.style.transform = 'translateY(calc(-100% + 2px))';
            spinner.style.display = 'inline-block';
            check.style.display = 'none';
            text.innerText = 'Processing your order';

            setTimeout(() => {
                text.innerText = 'Printing your receipt';
                wrap.classList.add('stepped-feed');
            }, 350);

            setTimeout(() => {
                wrap.className = 'receipt-paper-wrapper';
                wrap.style.transform = 'translateY(0%)';
                spinner.style.display = 'none';
                check.style.display = 'inline-block';
                text.innerText = 'Order complete';
            }, 2100);
        }
    </script>
</body>
</html>";

        return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }
}
