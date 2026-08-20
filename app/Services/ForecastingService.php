<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProductVariant;
use App\Models\SaleDetail;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ForecastingService
{
    /**
     * Calculate sales velocity for all variants over a specific lookback period.
     * Returns a collection keyed by variant_id with 'total_sold' and 'daily_velocity'.
     */
    public function calculateVelocity(int $lookbackDays = 14): Collection
    {
        $sinceDate = Carbon::now()->subDays($lookbackDays);

        $sales = SaleDetail::where('created_at', '>=', $sinceDate)
            ->select('variant_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('variant_id')
            ->get()
            ->keyBy('variant_id');

        return ProductVariant::all(['variant_id', 'sku', 'quantity', 'reorder_level', 'cost_price'])->map(function ($variant) use ($sales, $lookbackDays) {
            $sold = $sales->get($variant->variant_id)->total_sold ?? 0;
            $velocity = round((float) $sold / $lookbackDays, 2);

            return [
                'variant_id' => $variant->variant_id,
                'sku' => $variant->sku,
                'current_stock' => $variant->quantity,
                'reorder_level' => $variant->reorder_level ?? 10,
                'cost_price' => (float) $variant->cost_price,
                'total_sold' => (int) $sold,
                'daily_velocity' => $velocity,
                'days_remaining' => $velocity > 0 ? round($variant->quantity / $velocity, 1) : 999,
            ];
        })->keyBy('variant_id');
    }

    /**
     * Identify variants that are at risk of stockout or below their reorder level.
     */
    public function getStockoutRisks(int $lookbackDays = 14, int $criticalDaysThreshold = 7): Collection
    {
        $velocityMap = $this->calculateVelocity($lookbackDays);

        return $velocityMap->filter(function ($item) use ($criticalDaysThreshold) {
            return ($item['current_stock'] <= $item['reorder_level']) ||
                   ($item['daily_velocity'] > 0 && $item['days_remaining'] <= $criticalDaysThreshold);
        })->map(function ($item) {
            $item['urgency'] = $this->determineUrgency($item);
            $item['suggested_reorder_qty'] = $this->calculateSuggestedOrder($item);
            $item['estimated_cost'] = round($item['suggested_reorder_qty'] * $item['cost_price'], 2);

            return $item;
        });
    }

    /**
     * Determine urgency level for a stockout risk.
     */
    private function determineUrgency(array $item): string
    {
        if ($item['current_stock'] <= 0) {
            return 'OUT_OF_STOCK';
        }
        if ($item['days_remaining'] <= 3 && $item['daily_velocity'] > 0) {
            return 'CRITICAL';
        }
        if ($item['current_stock'] <= $item['reorder_level']) {
            return 'RESTOCK_REQUIRED';
        }

        return 'WARNING';
    }

    /**
     * Calculate suggested reorder quantity based on velocity and a 30-day buffer.
     */
    private function calculateSuggestedOrder(array $item): int
    {
        $bufferDays = 30;
        $needed = (int) ceil($item['daily_velocity'] * $bufferDays);
        $toOrder = $needed - $item['current_stock'];

        // Minimum order of 20 or twice the reorder level to ensure efficiency
        $minOrder = max(20, ($item['reorder_level'] * 2));

        return (int) max($minOrder, $toOrder);
    }
}
