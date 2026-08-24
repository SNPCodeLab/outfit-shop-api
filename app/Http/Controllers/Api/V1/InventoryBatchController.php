<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ProductBatch;
use App\Models\ProductVariant;
use App\Services\AuditLogService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryBatchController extends BaseApiController
{
    /**
     * List all inventory batches across all variants.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductBatch::with(['variant.product', 'variant.size', 'variant.color'])
            ->orderBy('batch_id', 'desc');

        if ($variantId = $request->input('variant_id')) {
            $query->where('variant_id', $variantId);
        }

        $perPage = $this->perPage($request);
        $batches = $query->paginate($perPage);

        return $this->successResponse($batches, 'Inventory batches retrieved successfully');
    }

    /**
     * Create / Receive a new inventory batch.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'variant_id' => 'required|exists:product_variants,variant_id',
            'batch_number' => 'required|string|max:100',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'required|date',
            'quantity_received' => 'required|integer|min:1',
            'quantity_remaining' => 'nullable|integer|min:0',
        ]);

        return $this->storeBatch($request, (int) $validated['variant_id']);
    }

    /**
     * List batches expiring within a configurable threshold (default 60 days).
     * Supports FMCG and beverage FIFO management.
     * Restricted to MANAGER or ADMIN.
     */
    public function expiringSoon(Request $request): JsonResponse
    {
        $days = (int) $request->input('days', 60);
        $thresholdDate = Carbon::now()->addDays($days);

        $batches = ProductBatch::with(['variant.product', 'variant.size', 'variant.color'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', $thresholdDate)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($batch) {
                $daysRemaining = Carbon::now()->diffInDays($batch->expiry_date, false);

                return [
                    'batch_id' => $batch->batch_id,
                    'batch_number' => $batch->batch_number,
                    'product_name' => $batch->variant->product->product_name ?? null,
                    'brand' => $batch->variant->product->brand ?? null,
                    'sku' => $batch->variant->sku ?? null,
                    'barcode' => $batch->variant->barcode ?? null,
                    'unit_of_measure' => $batch->variant->unit_of_measure ?? 'PIECE',
                    'expiry_date' => $batch->expiry_date->toISOString(),
                    'days_remaining' => (int) $daysRemaining,
                    'is_expired' => $daysRemaining < 0,
                    'quantity_remaining' => $batch->quantity_remaining,
                    'status' => $daysRemaining < 0
                        ? 'EXPIRED'
                        : ($daysRemaining <= 15 ? 'CRITICAL' : 'EXPIRING_SOON'),
                ];
            });

        return $this->successResponse([
            'days_threshold' => $days,
            'total_batches' => $batches->count(),
            'batches' => $batches,
        ], "Expiring batches within {$days} days retrieved successfully");
    }

    /**
     * List all inventory batches for a specific product variant.
     * Restricted to MANAGER or ADMIN.
     */
    public function listBatches(int $variantId): JsonResponse
    {
        ProductVariant::findOrFail($variantId);

        $batches = ProductBatch::where('variant_id', $variantId)
            ->orderBy('expiry_date', 'asc')
            ->get();

        return $this->successResponse($batches, 'Variant batches retrieved successfully');
    }

    /**
     * Record a new batch received for a variant and increment its total stock.
     * Restricted to MANAGER or ADMIN.
     */
    public function storeBatch(Request $request, int $variantId): JsonResponse
    {
        $variant = ProductVariant::findOrFail($variantId);

        $validated = $request->validate([
            'batch_number' => 'required|string|max:100',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'required|date|after:today',
            'quantity_received' => 'required|integer|min:1',
            'quantity_remaining' => 'nullable|integer|min:0',
        ]);

        $validated['variant_id'] = $variantId;
        $validated['quantity_remaining'] = $validated['quantity_remaining'] ?? $validated['quantity_received'];

        $batch = ProductBatch::create($validated);

        $variant->increment('quantity', $validated['quantity_received']);

        AuditLogService::log('CREATE', 'ProductBatch', $batch->batch_id, null, [
            'variant_id' => $variantId,
            'batch_number' => $batch->batch_number,
            'quantity_received' => $validated['quantity_received'],
        ]);

        return $this->createdResponse(
            $batch,
            'Batch received and added to inventory successfully',
            '/api/v1/variants/'.$variantId.'/batches'
        );
    }
}
