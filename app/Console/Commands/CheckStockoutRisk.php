<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ForecastingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckStockoutRisk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:check-risks {--lookback=14} {--threshold=7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyze sales velocity and identify variants at risk of stockout';

    /**
     * Execute the console command.
     */
    public function handle(ForecastingService $forecastingService): int
    {
        $lookback = (int) $this->option('lookback');
        $threshold = (int) $this->option('threshold');

        $this->info("Analyzing sales velocity (Lookback: {$lookback} days, Threshold: {$threshold} days)...");

        $risks = $forecastingService->getStockoutRisks($lookback, $threshold);

        if ($risks->isEmpty()) {
            $this->info('No stockout risks identified.');

            return 0;
        }

        $this->warn(count($risks).' items identified with stockout risks!');

        $headers = ['SKU', 'Stock', 'Velocity', 'Days Left', 'Urgency', 'Suggested Order'];
        $rows = $risks->map(function ($r) {
            return [
                $r['sku'],
                $r['current_stock'],
                $r['daily_velocity'],
                $r['days_remaining'],
                $r['urgency'],
                $r['suggested_reorder_qty'],
            ];
        })->toArray();

        $this->table($headers, $rows);

        // Log critical risks to the inventory channel
        $criticalRisks = $risks->whereIn('urgency', ['OUT_OF_STOCK', 'CRITICAL']);
        if ($criticalRisks->isNotEmpty()) {
            Log::channel('inventory')->warning('CRITICAL STOCKOUT RISK IDENTIFIED', [
                'item_count' => count($criticalRisks),
                'skus' => $criticalRisks->pluck('sku')->toArray(),
            ]);
        }

        return 0;
    }
}
