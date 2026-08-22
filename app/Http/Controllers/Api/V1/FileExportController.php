<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Jobs\GenerateReportExportJob;
use App\Models\PosShift;
use App\Models\ProductVariant;
use App\Models\SaleHeader;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileExportController extends BaseApiController
{
    /**
     * GET /api/v1/exports/inventory/excel?async=1
     *
     * Default: synchronous stream (backward compatible).
     * ?async=1: accepted for background generation (202 + export_id) so a
     * full-catalog export never holds an HTTP worker for minutes.
     */
    public function exportInventory(Request $request): StreamedResponse|JsonResponse
    {
        if ($request->boolean('async')) {
            return $this->dispatchAsyncExport('inventory_valuation', $request);
        }

        $fileName = 'inventory_valuation_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Variant ID', 'Product Name', 'SKU', 'Barcode', 'Size', 'Color', 'Quantity', 'Cost Price ($)', 'Sale Price ($)', 'Total Asset Valuation ($)']);

            ProductVariant::with(['product', 'size', 'color'])
                ->chunk(200, function ($variants) use ($handle) {
                    foreach ($variants as $v) {
                        $qty = (int) $v->quantity;
                        $cost = (float) $v->cost_price;
                        $valuation = round($qty * $cost, 2);

                        fputcsv($handle, [
                            $v->variant_id,
                            $v->product->product_name ?? 'N/A',
                            $v->sku,
                            $v->barcode ?? '',
                            $v->size->size_name ?? 'N/A',
                            $v->color->color_name ?? 'N/A',
                            $qty,
                            number_format($cost, 2),
                            number_format((float) $v->sale_price, 2),
                            number_format($valuation, 2),
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * GET /api/v1/exports/stock-movements/csv
     * Stream stock audit ledger history.
     */
    public function exportStockMovements(Request $request): StreamedResponse
    {
        $fileName = 'stock_movements_ledger_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Movement ID', 'Date', 'SKU', 'Type', 'Quantity Change', 'Stock Before', 'Stock After', 'Reference Type', 'Note']);

            StockMovement::with(['variant'])
                ->orderBy('movement_id', 'desc')
                ->chunk(200, function ($movements) use ($handle) {
                    foreach ($movements as $m) {
                        fputcsv($handle, [
                            $m->movement_id,
                            $m->movement_date ? $m->movement_date->toISOString() : '',
                            $m->variant->sku ?? 'N/A',
                            $m->movement_type,
                            $m->quantity,
                            $m->stock_before,
                            $m->stock_after,
                            $m->reference_type ?? '',
                            $m->note ?? '',
                        ]);
                    }
                });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * GET /api/v1/exports/sales-report/pdf
     * Render printable A4 Sales Report HTML format.
     */
    public function exportSalesReport(Request $request): Response
    {
        $sales = SaleHeader::with(['customer', 'employee'])
            ->orderBy('sale_id', 'desc')
            ->limit(100)
            ->get();

        $totalRevenue = $sales->sum('grand_total');

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>Executive Sales Summary Report</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; padding: 30px; color: #1e293b; }
                h1 { margin-bottom: 5px; font-size: 24px; }
                .subtitle { color: #64748b; font-size: 13px; margin-bottom: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                th { background: #f8fafc; border-bottom: 2px solid #cbd5e1; text-align: left; padding: 10px; font-size: 12px; }
                td { border-bottom: 1px solid #e2e8f0; padding: 8px 10px; font-size: 12px; }
                .total-card { background: #f1f5f9; padding: 15px; border-radius: 6px; margin-top: 20px; font-weight: bold; }
            </style>
        </head>
        <body>
            <h1>Executive Sales Summary Report</h1>
            <div class='subtitle'>Generated on: ".now()->format('Y-m-d H:i:s')." | SS-MIS Retail Gateway</div>
            <div class='total-card'>Total Recorded Revenue: $".number_format($totalRevenue, 2).' ('.count($sales)." Transactions)</div>
            <table>
                <thead>
                    <tr>
                        <th>Invoice No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Cashier</th>
                        <th>Payment Status</th>
                        <th style='text-align:right;'>Grand Total</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($sales as $s) {
            $html .= '
                    <tr>
                        <td><strong>'.($s->invoice_no ?? "INV-{$s->sale_id}").'</strong></td>
                        <td>'.($s->sale_date ? $s->sale_date->format('Y-m-d H:i') : '').'</td>
                        <td>'.($s->customer->customer_name ?? 'Guest').'</td>
                        <td>'.($s->employee->employee_name ?? 'Staff').'</td>
                        <td>'.$s->payment_status."</td>
                        <td style='text-align:right;'>$".number_format($s->grand_total, 2).'</td>
                    </tr>';
        }

        $html .= '
                </tbody>
            </table>
        </body>
        </html>';

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
        ]);
    }

    /**
     * GET /api/v1/exports/z-report/{shift_id}/thermal
     * Render raw 58mm/80mm ESC/POS thermal text receipt format.
     */
    public function exportZReportThermal(int $shiftId): Response
    {
        $shift = PosShift::with(['employee'])->findOrFail($shiftId);

        // Fetch sales for this shift window manually since no direct shift_id exists in sale_headers
        $sales = SaleHeader::with('payments')
            ->where('employee_id', $shift->employee_id)
            ->where('created_at', '>=', $shift->opened_at)
            ->when($shift->closed_at, fn ($q) => $q->where('created_at', '<=', $shift->closed_at))
            ->where('status', 'COMPLETED')
            ->get();

        $totalSales = $sales->sum('grand_total');
        $cashSales = 0.0;
        $cardSales = 0.0;
        $qrSales = 0.0;

        foreach ($sales as $s) {
            foreach ($s->payments as $p) {
                $method = strtoupper($p->payment_method);
                if ($method === 'CASH') {
                    $cashSales += (float) $p->amount;
                } elseif ($method === 'CARD') {
                    $cardSales += (float) $p->amount;
                } else {
                    $qrSales += (float) $p->amount;
                }
            }
        }

        $receipt = "================================\n";
        $receipt .= "      END-OF-DAY Z-REPORT      \n";
        $receipt .= "   RETAIL CLOTHING STORE POS    \n";
        $receipt .= "================================\n";
        $receipt .= "Shift ID:     #{$shift->shift_id}\n";
        $receipt .= 'Cashier:      '.($shift->employee->employee_name ?? 'Staff')."\n";
        $receipt .= 'Opened:       '.($shift->opened_at ? $shift->opened_at->format('Y-m-d H:i') : 'N/A')."\n";
        $receipt .= 'Closed:       '.($shift->closed_at ? $shift->closed_at->format('Y-m-d H:i') : 'ACTIVE')."\n";
        $receipt .= "--------------------------------\n";
        $receipt .= 'Opening Float:   $'.number_format($shift->opening_float_usd, 2)."\n";
        $receipt .= 'Cash Sales:      $'.number_format($cashSales, 2)."\n";
        $receipt .= 'Card Sales:      $'.number_format($cardSales, 2)."\n";
        $receipt .= 'KHQR/ABA Sales:  $'.number_format($qrSales, 2)."\n";
        $receipt .= "--------------------------------\n";
        $receipt .= 'TOTAL GROSS:     $'.number_format($totalSales, 2)."\n";
        $receipt .= 'Expected Drawer: $'.number_format($shift->expected_cash_usd, 2)."\n";
        $receipt .= 'Closing Cash:    $'.number_format($shift->closing_cash_usd, 2)."\n";
        $receipt .= 'Variance:        $'.number_format($shift->discrepancy_usd, 2).' ('.$shift->reconciliation_status.")\n";
        $receipt .= "================================\n";
        $receipt .= "   PRINTED FOR AUDIT & SAFE     \n";
        $receipt .= "================================\n\n\n";

        return response($receipt, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    /**
     * Queue an export job and return 202 with a tracking id (H5 wiring).
     */
    private function dispatchAsyncExport(string $reportType, Request $request): JsonResponse
    {
        $exportId = 'exp-'.now()->format('YmdHis').'-'.strtoupper(Str::random(6));

        GenerateReportExportJob::dispatch(
            $reportType,
            $request->only(['date_from', 'date_to', 'from_date', 'to_date', 'status', 'category_id', 'brand_id']),
            $request->user()->employee_id ?? $request->user()->id ?? 0,
            $exportId
        );

        return $this->acceptedResponse([
            'export_id' => $exportId,
            'report_type' => $reportType,
            'status' => 'QUEUED',
        ], 'Export accepted and queued for background generation');
    }
}
