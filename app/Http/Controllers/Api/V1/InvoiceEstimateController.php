<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\SaleDetail;
use App\Models\SaleHeader;
use App\Services\AuditLogService;
use App\Services\POSService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class InvoiceEstimateController extends BaseApiController
{
    public function __construct(protected POSService $posService) {}

    /**
     * List all Sales Orders, Invoices & Estimates with status filtering.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = SaleHeader::with(['customer', 'employee', 'details.variant.product', 'details.variant.size', 'details.variant.color', 'payments']);

        // Filter by Document Status (ESTIMATE, INVOICE, PAID, PENDING, VOIDED)
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

        $records = $query->orderBy('sale_id', 'desc')->paginate((int)($request->input('per_page', 20)));

        // Financial Totals Summary
        $totalInvoiced = (float) SaleHeader::where('status', '!=', 'VOIDED')->sum('grand_total');
        $totalCollected = (float) Payment::where('status', 'COMPLETED')->sum('amount');
        $outstandingBalance = max(0, $totalInvoiced - $totalCollected);

        $summary = [
            'total_invoiced_usd'     => round($totalInvoiced, 2),
            'total_collected_usd'    => round($totalCollected, 2),
            'outstanding_balance_usd'=> round($outstandingBalance, 2),
            'total_documents_count'  => $records->total(),
        ];

        return $this->successResponse([
            'summary'   => $summary,
            'documents' => $records,
        ], 'SalesBinder Invoices & Estimates retrieved');
    }

    /**
     * Create a new Quotation / Estimate (Does not immediately deduct inventory).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createEstimate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'        => 'required|exists:customers,customer_id',
            'items'              => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,variant_id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.discount'   => 'nullable|numeric|min:0',
            'overall_discount'   => 'nullable|numeric|min:0',
            'tax_rate'           => 'nullable|numeric|min:0|max:100',
            'notes'              => 'nullable|string|max:500',
            'valid_until'        => 'nullable|date',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $employeeId = $request->user()?->employee_id ?? $request->user()?->id ?? 1;
            $taxRate = (float) ($validated['tax_rate'] ?? 10.00);
            $overallDiscount = (float) ($validated['overall_discount'] ?? 0.0);

            $totalAmount = 0.0;
            $detailsData = [];

            foreach ($validated['items'] as $item) {
                $variant = ProductVariant::with(['product', 'size', 'color'])->findOrFail($item['variant_id']);
                $qty = (int) $item['quantity'];
                $itemDiscount = (float) ($item['discount'] ?? 0.0);
                $unitPrice = (float) $variant->sale_price;
                $lineTotal = max(0, ($unitPrice * $qty) - $itemDiscount);

                $totalAmount += $lineTotal;

                $detailsData[] = [
                    'variant_id' => $variant->variant_id,
                    'quantity'   => $qty,
                    'unit_price' => $unitPrice,
                    'discount'   => $itemDiscount,
                    'sub_total'  => $lineTotal,
                ];
            }

            $netAmount = max(0, $totalAmount - $overallDiscount);
            $taxAmount = round($netAmount * ($taxRate / 100), 2);
            $grandTotal = round($netAmount + $taxAmount, 2);

            $estimate = SaleHeader::create([
                'customer_id'  => $validated['customer_id'],
                'employee_id'  => $employeeId,
                'sale_date'    => now(),
                'total_amount' => $totalAmount,
                'discount'     => $overallDiscount,
                'tax_rate'     => $taxRate,
                'tax_amount'   => $taxAmount,
                'grand_total'  => $grandTotal,
                'status'       => 'ESTIMATE',
            ]);

            foreach ($detailsData as $detail) {
                SaleDetail::create(array_merge($detail, ['sale_id' => $estimate->sale_id]));
            }

            AuditLogService::log(
                action: 'CREATE_ESTIMATE',
                entity: 'SaleHeader',
                entityId: $estimate->sale_id,
                newValues: ['status' => 'ESTIMATE', 'grand_total' => $grandTotal]
            );

            return $this->successResponse(
                $estimate->load(['customer', 'employee', 'details.variant.product', 'details.variant.size', 'details.variant.color']),
                'Estimate quote created successfully (Stock reserved, not yet deducted)',
                201
            );
        });
    }

    /**
     * 1-Click Convert an approved Estimate into an Official Invoice & deduct stock.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function convertEstimateToInvoice(Request $request, int $id): JsonResponse
    {
        $estimate = SaleHeader::with(['details.variant'])->findOrFail($id);

        if ($estimate->status === 'COMPLETED' || $estimate->status === 'PAID') {
            return $this->errorResponse('This document is already an active/paid invoice.', 400);
        }

        try {
            return DB::transaction(function () use ($estimate, $request) {
                // Deduct physical inventory and verify stock availability
                foreach ($estimate->details as $detail) {
                    $variant = ProductVariant::lockForUpdate()->find($detail->variant_id);
                    if ($variant->quantity < $detail->quantity) {
                        throw new Exception("Insufficient stock for SKU [{$variant->sku}]. Available: {$variant->quantity}, Required: {$detail->quantity}.");
                    }
                    $variant->decrement('quantity', $detail->quantity);
                }

                $estimate->update(['status' => 'COMPLETED']);

                // Auto-register payment if provided
                if ($paymentMethod = $request->input('payment_method')) {
                    Payment::create([
                        'sale_id'               => $estimate->sale_id,
                        'payment_method'        => strtoupper($paymentMethod),
                        'amount'                => $estimate->grand_total,
                        'payment_date'          => now(),
                        'transaction_reference' => 'CONV-' . strtoupper(uniqid()),
                        'status'                => 'COMPLETED',
                    ]);
                }

                AuditLogService::log(
                    action: 'CONVERT_ESTIMATE_TO_INVOICE',
                    entity: 'SaleHeader',
                    entityId: $estimate->sale_id,
                    oldValues: ['status' => 'ESTIMATE'],
                    newValues: ['status' => 'COMPLETED']
                );

                return $this->successResponse(
                    $estimate->fresh(['customer', 'employee', 'details.variant.product', 'payments']),
                    'Estimate #' . $estimate->sale_id . ' successfully converted to official Invoice!'
                );
            });
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Render a SalesBinder-style professional printable A4/PDF Invoice View.
     *
     * @param int $id
     * @return Response
     */
    public function renderInvoiceHtml(int $id): Response
    {
        $sale = SaleHeader::with([
            'customer',
            'employee',
            'details.variant.product.category',
            'details.variant.size',
            'details.variant.color',
            'payments'
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
            <tr style='border-bottom: 1px solid var(--slate-200);'>
                <td style='padding: 10px 12px;'>
                    <div style='font-weight: 600; color: var(--slate-900);'>{$productName}</div>
                    <div style='font-size: 11px; color: var(--slate-500);'>Size: {$size} | Color: {$color} | SKU: {$variant->sku}</div>
                </td>
                <td style='padding: 10px 12px; text-align: center;'>{$qty}</td>
                <td style='padding: 10px 12px; text-align: right; font-family: monospace;'>\${$unitPrice}</td>
                <td style='padding: 10px 12px; text-align: right; font-family: monospace; font-weight: 600;'>\${$lineTotal}</td>
            </tr>";
        }

        $receiptItemsHtml = '';
        foreach ($details as $detail) {
            $variant = $detail->variant;
            $productName = htmlspecialchars(strtoupper(substr($variant->product->product_name ?? 'ITEM', 0, 18)));
            $qty = $detail->quantity;
            $lineTotal = number_format($detail->sub_total, 2);
            $receiptItemsHtml .= "
            <div style='display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 4px;'>
                <span>{$qty}x {$productName}</span>
                <span style='font-weight: 600;'>\${$lineTotal}</span>
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
    <title>Tax Invoice #INV-{$sale->sale_id} | Store Stock &amp; POS MIS</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'>
    <style>
        :root {
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --radius: 3px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: var(--slate-900);
            background-color: #f4f4f5;
            line-height: 1.5;
            font-size: 13px;
            padding: 32px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .container-wrap {
            max-width: 860px;
            margin: 0 auto;
        }

        /* ── Top Bar Controls ── */
        .controls-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .view-tabs {
            display: inline-flex;
            background: #e4e4e7;
            padding: 3px;
            border-radius: var(--radius);
        }
        .view-tab-btn {
            background: transparent;
            border: none;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #71717a;
            border-radius: var(--radius);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .view-tab-btn.active {
            background: #ffffff;
            color: #18181b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .action-btn {
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
        .btn-primary {
            background: #18181b;
            color: #ffffff;
            border: 1px solid #18181b;
        }
        .btn-primary:hover { background: #27272a; }
        .btn-secondary {
            background: #ffffff;
            color: #18181b;
            border: 1px solid #e4e4e7;
        }
        .btn-secondary:hover { background: #f4f4f5; }

        /* ═══════════════════════════════════════════════
           TACTILE RECEIPT PRINTER MACHINE (dqnamo style)
           ═══════════════════════════════════════════════ */
        .receipt-printer-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 0 40px;
        }
        .printer-machine {
            position: relative;
            width: 100%;
            max-width: 380px;
            border-radius: 1.5rem;
            border: 1px solid #18181b;
            background: #18181b;
            padding: 0.75rem;
            padding-bottom: 2rem;
            box-shadow: 0 20px 36px -20px rgba(0, 0, 0, 0.45), 0 6px 14px -8px rgba(0, 0, 0, 0.24), inset 0 1px 0 rgba(255, 255, 255, 0.14);
            z-index: 10;
        }
        .printer-screen {
            position: relative;
            overflow: hidden;
            border-radius: 1rem;
            border: 1px solid #27272a;
            background: #09090b;
            padding: 1rem;
            color: #fafafa;
            box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.6);
        }
        .screen-header {
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
            width: 16px;
            height: 16px;
            border: 2px solid #52525b;
            border-top-color: #fafafa;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        .status-complete-icon {
            color: #22c55e;
            font-size: 16px;
            display: none;
        }
        .printer-slot {
            position: absolute;
            left: 1.5rem;
            right: 1.5rem;
            bottom: 0.75rem;
            height: 8px;
            border-radius: 4px;
            background: #09090b;
            border: 1px solid #27272a;
            box-shadow: inset 0 2px 4px #000000;
            z-index: 40;
        }

        /* ── Receipt Paper & Stepped Motion ── */
        .receipt-output-container {
            position: relative;
            z-index: 5;
            margin-top: -1rem;
            width: 320px;
            overflow: hidden;
            padding-bottom: 2rem;
        }
        .receipt-paper-wrapper {
            position: relative;
            transform: translateY(calc(-100% + 2px));
            transition: opacity 0.16s ease;
        }
        .receipt-paper-wrapper.stepped-feed {
            animation: steppedPrintingAnimation 1.75s linear forwards;
        }
        .receipt-paper-wrapper.complete {
            transform: translateY(0%);
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
            padding: 24px 20px 32px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
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

        .dashed-line {
            border-top: 1px dashed #d4d4d8;
            margin: 12px 0;
        }

        /* ── Standard A4 Sheet Invoice View ── */
        .invoice-card {
            background: #ffffff;
            border: 1px solid #e4e4e7;
            border-radius: var(--radius);
            padding: 32px 36px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media print {
            body { background: #ffffff; padding: 0; }
            .controls-bar, .receipt-printer-section { display: none !important; }
            .invoice-card { display: block !important; border: none; box-shadow: none; padding: 0; }
        }
    </style>
</head>
<body>
    <div class='container-wrap'>
        <!-- Controls Bar -->
        <div class='controls-bar'>
            <div class='view-tabs'>
                <button class='view-tab-btn active' id='tab-printer' onclick='switchView(\"printer\")'>
                    <i class='fa-solid fa-receipt' style='margin-right: 4px;'></i> Tactile Printer
                </button>
                <button class='view-tab-btn' id='tab-sheet' onclick='switchView(\"sheet\")'>
                    <i class='fa-solid fa-file-lines' style='margin-right: 4px;'></i> A4 Document Sheet
                </button>
            </div>
            <div style='display: flex; gap: 8px;'>
                <button class='action-btn btn-secondary' onclick='replayPrinterAnimation()'>
                    <i class='fa-solid fa-rotate-right'></i> Replay Print
                </button>
                <button class='action-btn btn-primary' onclick='window.print()'>
                    <i class='fa-solid fa-print'></i> Print / Save PDF
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════════════
             1. TACTILE RECEIPT PRINTER MACHINE (dqnamo style)
             ═══════════════════════════════════════════════ -->
        <section class='receipt-printer-section' id='view-printer-mode'>
            <div class='printer-machine'>
                <div class='printer-screen'>
                    <div class='screen-header'>
                        <div style='font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #a1a1aa;'>
                            STORE STOCK &amp; POS
                        </div>
                        <span style='font-size: 10px; background: #27272a; padding: 2px 6px; border-radius: 4px; color: #d4d4d8;'>#INV-{$sale->sale_id}</span>
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
                        <span class='status-spinner' id='status-spinner'></span>
                        <i class='fa-solid fa-circle-check status-complete-icon' id='status-check'></i>
                        <span id='status-text'>Order complete</span>
                    </div>
                </div>
                <div class='printer-slot'></div>
            </div>

            <!-- Receipt Output with Stepped Feeding Motion -->
            <div class='receipt-output-container'>
                <div class='receipt-paper-wrapper complete' id='receipt-paper-wrap'>
                    <article class='receipt-paper'>
                        <div style='text-align: center; margin-bottom: 12px;'>
                            <div style='font-size: 13px; font-weight: 800; letter-spacing: -0.02em;'>STORE STOCK &amp; POS MIS</div>
                            <div style='font-size: 9px; color: #71717a; text-transform: uppercase;'>Official Store Receipt</div>
                        </div>

                        <div style='font-size: 10px; color: #52525b; line-height: 1.4;'>
                            <div>Order #: INV-000{$sale->sale_id}</div>
                            <div>Date: {$saleDate}</div>
                            <div>Status: {$statusText}</div>
                        </div>

                        <div class='dashed-line'></div>

                        <div>
                            {$receiptItemsHtml}
                        </div>

                        <div class='dashed-line'></div>

                        <div style='font-size: 11px; line-height: 1.6;'>
                            <div style='display: flex; justify-content: space-between;'><span>Subtotal:</span><span>\${$subtotal}</span></div>
                            <div style='display: flex; justify-content: space-between;'><span>Tax (10% VAT):</span><span>+\${$tax}</span></div>
                            <div style='display: flex; justify-content: space-between; font-weight: 800; font-size: 13px; margin-top: 4px; border-top: 1px solid #18181b; padding-top: 4px;'>
                                <span>TOTAL PAID</span>
                                <span>\${$grand}</span>
                            </div>
                        </div>

                        <div class='dashed-line'></div>

                        <div style='text-align: center; margin-top: 12px;'>
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
             2. STANDARD A4 SHEET DOCUMENT (Formal Invoice)
             ═══════════════════════════════════════════════ -->
        <div class='invoice-card' id='view-sheet-mode'>
            <div style='display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; border-bottom: 1px solid var(--slate-200); padding-bottom: 16px;'>
                <div>
                    <h1 style='font-size: 18px; font-weight: 800; text-transform: uppercase;'>STORE STOCK &amp; POS MIS</h1>
                    <div style='font-size: 12px; color: var(--slate-500);'>Enterprise Clothing Retail &amp; Logistics System</div>
                </div>
                <div style='text-align: right;'>
                    <div style='font-size: 16px; font-weight: 800; color: #18181b;'>TAX INVOICE</div>
                    <div style='font-size: 12px; font-family: monospace; color: var(--slate-500);'>#INV-000{$sale->sale_id}</div>
                </div>
            </div>

            <div style='display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;'>
                <div style='background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius); padding: 12px 14px;'>
                    <div style='font-size: 11px; font-weight: 700; color: var(--slate-500); text-transform: uppercase; margin-bottom: 6px;'>Customer Info</div>
                    <div style='font-weight: 700;'>{$customerName}</div>
                    <div style='font-size: 12px; color: var(--slate-500);'>Phone: {$customerPhone}</div>
                </div>
                <div style='background: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius); padding: 12px 14px;'>
                    <div style='font-size: 11px; font-weight: 700; color: var(--slate-500); text-transform: uppercase; margin-bottom: 6px;'>Invoice Details</div>
                    <div style='font-size: 12px;'>Date: <strong>{$saleDate}</strong></div>
                    <div style='font-size: 12px;'>Status: <strong style='color: {$statusColor};'>{$statusText}</strong></div>
                </div>
            </div>

            <table style='width: 100%; border-collapse: collapse; margin-bottom: 24px;'>
                <thead>
                    <tr style='background: var(--slate-50); border-bottom: 2px solid var(--slate-200); text-align: left; font-size: 11px; text-transform: uppercase; color: var(--slate-500);'>
                        <th style='padding: 8px 12px;'>Description</th>
                        <th style='padding: 8px 12px; text-align: center;'>Qty</th>
                        <th style='padding: 8px 12px; text-align: right;'>Unit Price</th>
                        <th style='padding: 8px 12px; text-align: right;'>Total</th>
                    </tr>
                </thead>
                <tbody>
                    {$itemsListHtml}
                </tbody>
            </table>

            <div style='display: flex; justify-content: flex-end;'>
                <div style='width: 260px;'>
                    <div style='display: flex; justify-content: space-between; padding: 4px 0;'><span>Subtotal:</span><span style='font-family: monospace;'>\${$subtotal}</span></div>
                    <div style='display: flex; justify-content: space-between; padding: 4px 0;'><span>Tax (10% VAT):</span><span style='font-family: monospace;'>+\${$tax}</span></div>
                    <div style='display: flex; justify-content: space-between; font-weight: 800; font-size: 14px; border-top: 1px solid var(--slate-300); padding-top: 6px; margin-top: 4px;'>
                        <span>Grand Total:</span><span style='font-family: monospace;'>\${$grand}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Init view
        document.getElementById('status-spinner').style.display = 'none';
        document.getElementById('status-check').style.display = 'inline-block';

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
            spinner.style.display = 'inline-block';
            check.style.display = 'none';
            text.innerText = 'Processing your order';

            setTimeout(() => {
                text.innerText = 'Printing your receipt';
                wrap.classList.add('stepped-feed');
            }, 400);

            setTimeout(() => {
                wrap.className = 'receipt-paper-wrapper complete';
                spinner.style.display = 'none';
                check.style.display = 'inline-block';
                text.innerText = 'Order complete';
            }, 2300);
        }
    </script>
</body>
</html>";

        return response($html, 200)->header('Content-Type', 'text/html; charset=utf-8');
    }
}

