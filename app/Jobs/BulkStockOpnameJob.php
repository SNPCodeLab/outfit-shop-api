<?php

namespace App\Jobs;

use App\Models\ProductVariant;
use App\Services\InventoryService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BulkStockOpnameJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    /**
     * Create a new job instance.
     *
     * @param  array  $auditItems  Array of ['variant_id' => 1, 'physical_count' => 50]
     */
    public function __construct(
        public array $auditItems,
        public int $employeeId,
        public string $sessionReference
    ) {}

    /**
     * Execute batch physical stock opname reconciliations in background.
     */
    public function handle(InventoryService $inventoryService): void
    {
        Log::info("Starting batch Stock Opname reconciliation [{$this->sessionReference}] with ".count($this->auditItems).' items');

        $reconciledCount = 0;

        foreach ($this->auditItems as $item) {
            try {
                $variantId = $item['variant_id'];
                $physicalCount = (int) $item['physical_count'];

                $variant = ProductVariant::find($variantId);
                if (! $variant) {
                    continue;
                }

                $currentQty = (int) $variant->quantity;
                $diff = $physicalCount - $currentQty;

                if ($diff !== 0) {
                    $inventoryService->adjustStock(
                        variantId: $variantId,
                        quantity: $diff,
                        movementType: 'STOCKTAKE',
                        employeeId: $this->employeeId,
                        note: "Stock Opname {$this->sessionReference}: system ({$currentQty}) -> physical ({$physicalCount})"
                    );
                    $reconciledCount++;
                }
            } catch (\Throwable $e) {
                Log::error("Failed to reconcile variant ID {$item['variant_id']}: ".$e->getMessage());
            }
        }

        Log::info("Stock Opname [{$this->sessionReference}] completed: {$reconciledCount} variance adjustments recorded.");
    }
}
