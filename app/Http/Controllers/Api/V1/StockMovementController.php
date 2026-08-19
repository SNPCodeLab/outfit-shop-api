<?php

declare(strict_types=1);

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

    /**
     * List the stock movement ledger with pagination and filtering.
     *
     * Query params:
     *   ?variant_id=1            — filter by specific variant (from API spec)
     *   ?movement_type=SALE      — filter by type (SALE, PURCHASE, ADJUSTMENT, RETURN_IN, RETURN_OUT, WRITE_OFF, STOCKTAKE)
     *   ?from_date=YYYY-MM-DD    — date range start
     *   ?to_date=YYYY-MM-DD      — date range end
     *   ?per_page=50             — page size (default: 50)
     */
    public function index(Request $request): JsonResponse
    {
        $query = StockMovement::with([
            'variant.product',
            'variant.size',
            'variant.color',
        ]);

        // Filter by variant
        if ($variantId = $request->input('variant_id')) {
            $query->where('variant_id', $variantId);
        }

        // Filter by movement type
        if ($movementType = $request->input('movement_type')) {
            $query->where('movement_type', strtoupper($movementType));
        }

        // Date range filtering
        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('movement_date', '>=', $fromDate);
        }
        if ($toDate = $request->input('to_date')) {
            $query->whereDate('movement_date', '<=', $toDate);
        }

        $perPage = (int) $request->input('per_page', 50);
        $movements = $query->orderBy('movement_id', 'desc')->paginate($perPage);

        return $this->successResponse($movements, 'Stock movement audit ledger retrieved');
    }

    /**
     * Perform a manual stock adjustment.
     *
     * Accepted movement_type values (strict enum):
     *   ADJUSTMENT  — general count correction
     *   RETURN_IN   — customer return (stock back in)
     *   WRITE_OFF   — damaged / expired goods removed
     *   STOCKTAKE   — reconciliation after physical stocktake
     */
    public function adjust(Request $request): JsonResponse
    {
        $allowed = array_merge(
            InventoryService::ADJUSTMENT_MOVEMENT_TYPES,
            array_map('strtolower', InventoryService::ADJUSTMENT_MOVEMENT_TYPES)
        );
        $allowedList = implode(',', $allowed);

        $validated = $request->validate([
            'variant_id' => 'required|exists:product_variants,variant_id',
            'quantity' => 'required|integer|not_in:0',
            'movement_type' => "required|string|in:{$allowedList}",
            'note' => 'nullable|string|max:500',
        ]);

        try {
            $employeeId = $request->user()->employee_id ?? $request->user()->id;
            $movement = $this->inventoryService->adjustStock(
                variantId: $validated['variant_id'],
                quantity: $validated['quantity'],
                movementType: $validated['movement_type'],
                employeeId: $employeeId,
                note: $validated['note'] ?? null
            );

            return $this->successResponse(
                $movement->load(['variant.product', 'variant.size', 'variant.color']),
                'Inventory stock adjusted successfully'
            );
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400, 'STOCK_ADJUSTMENT_FAILED');
        }
    }
}
