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
        $statusColor = $balanceDue <= 0 ? '#16a34a' : '#d97706';
        $statusText = $balanceDue <= 0 ? 'PAID IN FULL' : ($totalPaid > 0 ? 'PARTIALLY PAID' : 'UNPAID');

        $html = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Invoice #INV-{$sale->sale_id} | KhmeRiel</title>
    <style>
        @page { size: A4; margin: 20mm; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; line-height: 1.5; font-size: 13px; margin: 0; padding: 20px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #e2e8f0; border-radius: 3px; background: #ffffff; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #0f172a; padding-bottom: 20px; margin-bottom: 20px; }
        .brand-logo { height: 48px; object-fit: contain; }
        .invoice-title { font-size: 24px; font-weight: 800; text-transform: uppercase; color: #0f172a; margin: 0; }
        .meta-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .meta-box h4 { margin: 0 0 6px 0; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th { background: #f8fafc; border-bottom: 1px solid #cbd5e1; padding: 10px; text-align: left; font-size: 11px; text-transform: uppercase; color: #475569; }
        .table td { padding: 12px 10px; border-bottom: 1px solid #f1f5f9; }
        .text-right { text-align: right; }
        .summary-box { margin-left: auto; width: 300px; border-top: 1px solid #e2e8f0; padding-top: 10px; }
        .summary-row { display: flex; justify-content: space-between; padding: 4px 0; }
        .grand-total { font-size: 16px; font-weight: 800; border-top: 2px solid #0f172a; padding-top: 8px; margin-top: 8px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 3px; font-weight: 700; font-size: 11px; text-transform: uppercase; color: #fff; background: {$statusColor}; }
        .footer { margin-top: 30px; padding-top: 15px; border-top: 1px solid #e2e8f0; font-size: 11px; color: #94a3b8; text-align: center; }
        @media print { body { padding: 0; } .invoice-box { border: none; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class='no-print' style='text-align: right; max-width: 800px; margin: 0 auto 15px auto;'>
        <button onclick='window.print()' style='background: #0f172a; color: white; border: none; padding: 8px 16px; border-radius: 3px; font-weight: 600; cursor: pointer;'>Print / Save as PDF</button>
    </div>
    <div class='invoice-box'>
        <div class='header'>
            <div>
                <img class='brand-logo' src='https://res.cloudinary.com/od8t271n/image/upload/v1786898754/KhmerRiel.png' alt='KhmeRiel Logo'>
                <div style='font-size: 11px; color: #64748b; margin-top: 5px;'>KhmeRiel Clothing MIS & POS • Phnom Penh, Cambodia</div>
            </div>
            <div style='text-align: right;'>
                <h1 class='invoice-title'>" . ($sale->status === 'ESTIMATE' ? 'ESTIMATE / QUOTE' : 'TAX INVOICE') . "</h1>
                <div style='font-family: monospace; font-size: 14px; font-weight: 700;'>#INV-" . str_pad($sale->sale_id, 6, '0', STR_PAD_LEFT) . "</div>
                <div style='margin-top: 6px;'><span class='badge'>{$statusText}</span></div>
            </div>
        </div>

        <div class='meta-grid'>
            <div class='meta-box'>
                <h4>Billed To (Customer)</h4>
                <div style='font-weight: 700; font-size: 14px;'>" . htmlspecialchars($customer->customer_name ?? 'Walk-in Client') . "</div>
                <div>Phone: " . htmlspecialchars($customer->phone ?? 'N/A') . "</div>
                <div>Address: " . htmlspecialchars($customer->address ?? 'Phnom Penh, Cambodia') . "</div>
            </div>
            <div class='meta-box' style='text-align: right;'>
                <h4>Invoice Details</h4>
                <div><strong>Date Issued:</strong> " . $sale->created_at->format('M d, Y H:i') . "</div>
                <div><strong>Cashier / Staff:</strong> " . htmlspecialchars(($employee->first_name ?? 'Admin') . ' ' . ($employee->last_name ?? '')) . "</div>
                <div><strong>Payment Terms:</strong> Due Upon Receipt</div>
            </div>
        </div>

        <table class='table'>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>SKU / Barcode</th>
                    <th class='text-right'>Qty</th>
                    <th class='text-right'>Unit Price</th>
                    <th class='text-right'>Line Total</th>
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
                        <strong>{$productName}</strong>
                        <div style='font-size: 11px; color: #64748b;'>Size: {$size} | Color: {$color}</div>
                    </td>
                    <td style='font-family: monospace; font-size: 11px;'>{$sku}</td>
                    <td class='text-right'>{$qty}</td>
                    <td class='text-right'>\${$price}</td>
                    <td class='text-right'>\${$sub}</td>
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

        <div class='summary-box'>
            <div class='summary-row'><span>Subtotal:</span><span>\${$subtotal}</span></div>
            <div class='summary-row'><span>Discount:</span><span>-\${$discount}</span></div>
            <div class='summary-row'><span>Tax (10% VAT Exclusive):</span><span>+\${$tax}</span></div>
            <div class='summary-row grand-total'><span>Grand Total:</span><span>\${$grand}</span></div>
            <div class='summary-row' style='color: #16a34a; font-weight: 600;'><span>Amount Paid:</span><span>\${$paid}</span></div>
            <div class='summary-row' style='color: #d97706; font-weight: 700; border-top: 1px dashed #cbd5e1; padding-top: 6px;'><span>Balance Due:</span><span>\${$due}</span></div>
        </div>

        <div class='footer'>
            Thank you for your business! Generated by KhmeRiel MIS & POS System • support@kesararamwithdigital.tech
        </div>
    </div>
</body>
</html>";

        return response($html, 200)->header('Content-Type', 'text/html');
    }
}
