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
                    'variant_id'      => $variant->variant_id,
                    'quantity'        => $qty,
                    'unit_price'      => $unitPrice,
                    'discount_amount' => $itemDiscount,
                    'line_total'      => $lineTotal,
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

        $html = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Tax Invoice #INV-{$sale->sale_id} | Store Stock & POS MIS</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css'>
    <style>
        @page { size: A4; margin: 15mm; }
        :root {
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-700: #334155;
            --slate-900: #0f172a;
            --radius: 3px;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: var(--slate-900);
            background-color: var(--slate-100);
            line-height: 1.5;
            font-size: 13px;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }
        .page-wrapper {
            max-width: 840px;
            margin: 0 auto;
        }
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 600;
            color: var(--slate-500);
            text-decoration: none;
            padding: 6px 12px;
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            transition: all 0.15s ease;
        }
        .back-link:hover { color: var(--slate-900); border-color: var(--slate-300); }
        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--slate-900);
            color: #ffffff;
            border: 1px solid var(--slate-900);
            padding: 8px 18px;
            border-radius: var(--radius);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.1s ease;
        }
        .print-btn:hover { background: var(--slate-700); }
        .print-btn:active { transform: scale(0.98); }

        .invoice-card {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            padding: 36px;
            position: relative;
            overflow: hidden;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid var(--slate-200);
            padding-bottom: 24px;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }
        .brand-text-block {
            display: flex;
            flex-direction: column;
        }
        .brand-company {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--slate-900);
            text-transform: uppercase;
        }
        .brand-sub {
            font-size: 12px;
            color: var(--slate-500);
            margin-top: 2px;
        }
        .invoice-title-block {
            text-align: right;
        }
        .doc-type {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--slate-900);
            text-transform: uppercase;
        }
        .doc-number {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 13px;
            font-weight: 600;
            color: var(--slate-500);
            margin-top: 2px;
        }
        .status-badge {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 10px;
            border-radius: var(--radius);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            background: {$statusBg};
            border: 1px solid {$statusBorder};
            color: {$statusColor};
            text-transform: uppercase;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 28px;
        }
        .meta-card {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            padding: 16px;
        }
        .meta-card h4 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--slate-500);
            margin-bottom: 8px;
        }
        .meta-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--slate-900);
            margin-bottom: 4px;
        }
        .meta-text {
            font-size: 12px;
            color: var(--slate-500);
            line-height: 1.4;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 24px;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .table th {
            background: var(--slate-50);
            border-bottom: 1px solid var(--slate-200);
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--slate-500);
        }
        .table td {
            padding: 12px 14px;
            border-bottom: 1px solid var(--slate-100);
            font-size: 13px;
        }
        .table tr:last-child td {
            border-bottom: none;
        }
        .text-right { text-align: right; }
        .item-sku {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 11px;
            color: var(--slate-500);
        }

        .calc-grid {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 24px;
            margin-top: 16px;
        }
        .notes-card {
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            padding: 14px 16px;
            background: var(--slate-50);
            font-size: 12px;
            color: var(--slate-500);
        }
        .summary-card {
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            padding: 16px;
            background: var(--slate-50);
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 5px 0;
            font-size: 12px;
            color: var(--slate-700);
        }
        .grand-row {
            border-top: 1px solid var(--slate-300);
            padding-top: 8px;
            margin-top: 8px;
            font-size: 15px;
            font-weight: 800;
            color: var(--slate-900);
        }

        .footer-note {
            margin-top: 36px;
            padding-top: 16px;
            border-top: 1px solid var(--slate-200);
            text-align: center;
            font-size: 11px;
            color: var(--slate-400);
        }

        /* ── Printing Animation Overlay ── */
        .print-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.2s ease-out;
        }
        .print-modal {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: var(--radius);
            padding: 30px;
            width: 360px;
            text-align: center;
            position: relative;
        }
        .printer-icon-wrap {
            width: 60px;
            height: 60px;
            background: var(--slate-100);
            border-radius: var(--radius);
            margin: 0 auto 16px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--slate-900);
            position: relative;
            overflow: hidden;
        }
        .printer-laser {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: #3b82f6;
            box-shadow: 0 0 8px #3b82f6;
            animation: scanLaser 1.2s infinite ease-in-out;
        }
        .print-progress-bar {
            width: 100%;
            height: 4px;
            background: var(--slate-200);
            border-radius: 2px;
            margin: 16px 0 8px 0;
            overflow: hidden;
        }
        .print-progress-fill {
            height: 100%;
            width: 0%;
            background: var(--slate-900);
            transition: width 0.8s ease-in-out;
        }

        @keyframes scanLaser {
            0% { top: 10%; opacity: 0.2; }
            50% { top: 90%; opacity: 1; }
            100% { top: 10%; opacity: 0.2; }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @media (max-width: 640px) {
            body { padding: 12px 8px; }
            .invoice-card { padding: 20px 16px; }
            .meta-grid { grid-template-columns: 1fr; gap: 12px; }
            .calc-grid { grid-template-columns: 1fr; }
            .invoice-title-block { text-align: left; margin-top: 8px; }
        }

        @media print {
            body { padding: 0; background: #ffffff; }
            .page-wrapper { max-width: 100%; }
            .invoice-card { border: none; padding: 0; }
            .action-bar, .print-overlay { display: none !important; }
        }
    </style>
</head>
<body>
    <div class='page-wrapper'>
        <div class='action-bar'>
            <a href='/guide' class='back-link'><i class='fa-solid fa-arrow-left'></i> Help Centre Guide</a>
            <button class='print-btn' onclick='startPrintAnimation()'>
                <i class='fa-solid fa-print'></i> Print / Save as PDF
            </button>
        </div>

        <div class='invoice-card' id='invoice-document'>
            <div class='invoice-header'>
                <div class='brand-text-block'>
                    <div class='brand-company'>STORE STOCK &amp; POS MIS</div>
                    <div class='brand-sub'>Enterprise Retail Inventory &amp; Point-of-Sale Billing</div>
                </div>
                <div class='invoice-title-block'>
                    <div class='doc-type'>" . ($sale->status === 'ESTIMATE' ? 'ESTIMATE / QUOTE' : 'TAX INVOICE') . "</div>
                    <div class='doc-number'>#INV-" . str_pad($sale->sale_id, 6, '0', STR_PAD_LEFT) . "</div>
                    <div><span class='status-badge'>{$statusText}</span></div>
                </div>
            </div>

            <div class='meta-grid'>
                <div class='meta-card'>
                    <h4>Billed To (Customer)</h4>
                    <div class='meta-name'>" . htmlspecialchars($customer->customer_name ?? 'Walk-in Client') . "</div>
                    <div class='meta-text'>Telephone: " . htmlspecialchars($customer->phone ?? 'N/A') . "</div>
                    <div class='meta-text'>Address: " . htmlspecialchars($customer->address ?? 'Phnom Penh, Cambodia') . "</div>
                </div>
                <div class='meta-card'>
                    <h4>Document Details</h4>
                    <div class='meta-text'><strong>Issue Date:</strong> " . $sale->created_at->format('M d, Y • H:i') . "</div>
                    <div class='meta-text'><strong>Staff Operator:</strong> " . htmlspecialchars(($employee->first_name ?? 'Admin') . ' ' . ($employee->last_name ?? '')) . "</div>
                    <div class='meta-text'><strong>Payment Terms:</strong> Due Upon Receipt</div>
                </div>
            </div>

            <div class='table-responsive'>
                <table class='table'>
                    <thead>
                        <tr>
                            <th>Item Description</th>
                            <th>SKU</th>
                            <th class='text-right'>Qty</th>
                            <th class='text-right'>Unit Price</th>
                            <th class='text-right'>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>";

        foreach ($details as $d) {
            $productName = htmlspecialchars($d->variant->product->product_name ?? 'Apparel Item');
            $sku = htmlspecialchars($d->variant->sku ?? '-');
            $size = htmlspecialchars($d->variant->size->size_name ?? '');
            $color = htmlspecialchars($d->variant->color->color_name ?? '');
            $qty = $d->quantity;
            $price = number_format($d->unit_price, 2);
            $sub = number_format($d->line_total ?? ($d->quantity * $d->unit_price), 2);

            $html .= "
                        <tr>
                            <td>
                                <div style='font-weight: 600; color: var(--slate-900);'>{$productName}</div>
                                <div style='font-size: 11px; color: var(--slate-500);'>Size: {$size} • Color: {$color}</div>
                            </td>
                            <td class='item-sku'>{$sku}</td>
                            <td class='text-right' style='font-weight: 600;'>{$qty}</td>
                            <td class='text-right'>\${$price}</td>
                            <td class='text-right' style='font-weight: 600;'>\${$sub}</td>
                        </tr>";
        }

        $subtotal = number_format($sale->total_amount, 2);
        $discount = number_format($sale->discount, 2);
        $tax = number_format($sale->tax_amount, 2);
        $grand = number_format($sale->grand_total, 2);
        $paid = number_format($totalPaid, 2);
        $due = number_format($balanceDue, 2);

        $html .= "
                    </tbody>
                </table>
            </div>

            <div class='calc-grid'>
                <div class='notes-card'>
                    <div style='font-weight: 700; color: var(--slate-700); margin-bottom: 4px; text-transform: uppercase; font-size: 11px;'>Terms &amp; Instructions</div>
                    <div>• Payment is due upon receipt via Cash, Debit/Credit Card, or Bakong KHQR.</div>
                    <div>• All goods purchased are recorded under the Store Stock ledger.</div>
                </div>

                <div class='summary-card'>
                    <div class='summary-row'><span>Subtotal:</span><span style='font-family: monospace;'>\${$subtotal}</span></div>
                    <div class='summary-row'><span>Discount:</span><span style='font-family: monospace;'>-\${$discount}</span></div>
                    <div class='summary-row'><span>Tax (10% VAT Exclusive):</span><span style='font-family: monospace;'>+\${$tax}</span></div>
                    <div class='summary-row grand-row'><span>Grand Total:</span><span style='font-family: monospace;'>\${$grand}</span></div>
                    <div class='summary-row' style='color: #166534; font-weight: 600; margin-top: 4px;'><span>Paid Amount:</span><span style='font-family: monospace;'>\${$paid}</span></div>
                    <div class='summary-row' style='color: #92400e; font-weight: 700; border-top: 1px dashed var(--slate-300); padding-top: 6px; margin-top: 4px;'><span>Balance Due:</span><span style='font-family: monospace;'>\${$due}</span></div>
                </div>
            </div>

            <div class='footer-note'>
                Generated by Store Stock & Point-of-Sale Information System • A4 Document Standard
            </div>
        </div>
    </div>

    <!-- Print Simulation Animation Modal -->
    <div class='print-overlay' id='print-modal'>
        <div class='print-modal'>
            <div class='printer-icon-wrap'>
                <i class='fa-solid fa-print'></i>
                <div class='printer-laser'></div>
            </div>
            <h3 style='font-size: 15px; font-weight: 700; color: var(--slate-900);'>Preparing Document</h3>
            <p style='font-size: 12px; color: var(--slate-500); margin-top: 4px;'>Formatting A4 vector layout &amp; tax tables...</p>
            <div class='print-progress-bar'>
                <div class='print-progress-fill' id='print-fill'></div>
            </div>
            <span style='font-size: 11px; color: var(--slate-400); font-family: monospace;' id='print-status-text'>Rendering page 1 of 1...</span>
        </div>
    </div>

    <script>
        function startPrintAnimation() {
            const overlay = document.getElementById('print-modal');
            const fill = document.getElementById('print-fill');
            const statusText = document.getElementById('print-status-text');

            overlay.style.display = 'flex';
            fill.style.width = '0%';
            statusText.innerText = 'Formatting A4 vector layout...';

            setTimeout(() => {
                fill.style.width = '70%';
                statusText.innerText = 'Spooling document to printer...';
            }, 300);

            setTimeout(() => {
                fill.style.width = '100%';
                statusText.innerText = 'Ready! Launching print dialog...';
            }, 750);

            setTimeout(() => {
                overlay.style.display = 'none';
                window.print();
            }, 1050);
        }
    </script>
</body>
</html>";

        return response($html, 200)->header('Content-Type', 'text/html');
    }
}

