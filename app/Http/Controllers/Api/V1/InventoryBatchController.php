<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductBatch;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryBatchController extends Controller
{
    /**
     * List batches expiring soon (30, 60, 90 days) for FMCG/Beverage FIFO management
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
                    'batch_id'           => $batch->batch_id,
                    'batch_number'       => $batch->batch_number,
                    'product_name'       => $batch->variant->product->product_name ?? 'N/A',
                    'brand'              => $batch->variant->product->brand ?? 'N/A',
                    'sku'                => $batch->variant->sku ?? 'N/A',
                    'barcode'            => $batch->variant->barcode ?? 'N/A',
                    'unit_of_measure'    => $batch->variant->unit_of_measure ?? 'PIECE',
                    'expiry_date'        => $batch->expiry_date->format('Y-m-d'),
                    'days_remaining'     => (int) $daysRemaining,
                    'is_expired'         => $daysRemaining < 0,
                    'quantity_remaining' => $batch->quantity_remaining,
                    'status'             => $daysRemaining < 0 ? 'EXPIRED' : ($daysRemaining <= 15 ? 'CRITICAL' : 'EXPIRING_SOON'),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => [
                'days_threshold' => $days,
                'total_batches'  => $batches->count(),
                'batches'        => $batches,
            ],
            'message' => "Expiring batches within {$days} days retrieved successfully",
        ]);
    }

    /**
     * List all batches for a specific product variant
     */
    public function listBatches(int $variantId): JsonResponse
    {
        $variant = ProductVariant::findOrFail($variantId);
        $batches = ProductBatch::where('variant_id', $variantId)
            ->orderBy('expiry_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $batches,
            'message' => 'Variant batches retrieved successfully',
        ]);
    }

    /**
     * Record a new batch received for a variant
     */
    public function storeBatch(Request $request, int $variantId): JsonResponse
    {
        $variant = ProductVariant::findOrFail($variantId);

        $validated = $request->validate([
            'batch_number'       => 'required|string|max:100',
            'manufacturing_date' => 'nullable|date',
            'expiry_date'        => 'required|date',
            'quantity_received'  => 'required|integer|min:1',
            'quantity_remaining' => 'nullable|integer|min:0',
        ]);

        $validated['variant_id'] = $variantId;
        $validated['quantity_remaining'] = $validated['quantity_remaining'] ?? $validated['quantity_received'];

        $batch = ProductBatch::create($validated);

        // Update variant's total quantity
        $variant->increment('quantity', $validated['quantity_received']);

        return response()->json([
            'success' => true,
            'data'    => $batch,
            'message' => 'Batch received and added to inventory',
        ], 201);
    }
}
