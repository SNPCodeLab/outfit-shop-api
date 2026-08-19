<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\SaleHeader;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class GenerateReportExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $reportType,
        public array $filters,
        public int $requestedByUserId,
        public string $exportId
    ) {}

    /**
     * Execute the job to generate async CSV/Excel exports.
     */
    public function handle(): void
    {
        Log::info("Starting async report generation [{$this->reportType}] for export ID: {$this->exportId}");

        $exportDir = storage_path('app/exports');
        if (! File::exists($exportDir)) {
            File::makeDirectory($exportDir, 0755, true);
        }

        $fileName = "report_{$this->reportType}_{$this->exportId}.csv";
        $filePath = "{$exportDir}/{$fileName}";

        $handle = fopen($filePath, 'w');

        if ($this->reportType === 'SALES_SUMMARY') {
            // Write CSV Header
            fputcsv($handle, ['Invoice No', 'Date', 'Customer', 'Subtotal ($)', 'Discount ($)', 'Tax 10% ($)', 'Grand Total ($)', 'Status']);

            SaleHeader::with(['customer'])
                ->orderBy('sale_id', 'desc')
                ->chunk(100, function ($sales) use ($handle) {
                    foreach ($sales as $sale) {
                        fputcsv($handle, [
                            $sale->invoice_no ?? "INV-{$sale->sale_id}",
                            $sale->sale_date ? $sale->sale_date->toIso8601String() : '',
                            $sale->customer->customer_name ?? 'Guest',
                            number_format($sale->total_amount, 2),
                            number_format($sale->discount, 2),
                            number_format($sale->tax_amount, 2),
                            number_format($sale->grand_total, 2),
                            $sale->status,
                        ]);
                    }
                });
        }

        fclose($handle);

        Log::info("Async report export generated successfully: {$fileName}");
    }
}
