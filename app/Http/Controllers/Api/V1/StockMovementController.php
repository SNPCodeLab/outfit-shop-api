<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends BaseApiController
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function index(): JsonResponse
    {
        $movements = StockMovement::with(['variant.product', 'variant.size', 'variant.color', 'employee'])->get();
        return $this->successResponse($movements, 'Stock movement audit history');
    }

    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'variant_id'    => 'required|exists:product_variants,variant_id',
            'quantity'      => 'required|integer',
            'movement_type' => 'required|string|in:ADJUSTMENT,RETURN_IN,RETURN_OUT',
            'note'          => 'nullable|string|max:255',
        ]);

        try {
            $employeeId = $request->user()->employee_id;
            $movement = $this->inventoryService->adjustStock(
                variantId: $validated['variant_id'],
                quantity: $validated['quantity'],
                movementType: $validated['movement_type'],
                employeeId: $employeeId,
                note: $validated['note'] ?? null
            );

            return $this->successResponse($movement->load('variant.product'), 'Inventory stock adjusted successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
