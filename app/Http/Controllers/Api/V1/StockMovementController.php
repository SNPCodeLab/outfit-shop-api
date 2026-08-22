<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Jobs\BulkStockOpnameJob;
use App\Models\StockMovement;
use App\Services\AuditLogService;
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

        $perPage = $this->perPage($request, 50);
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

    /**
     * POST /api/v1/inventory/stock-opname
     *
     * Queue a full physical stock count (opname) for background execution:
     * each variant's system quantity is reconciled against the counted
     * physical quantity. Returns 202 immediately; large counts never block
     * the POS terminal.
     */
    public function stockOpname(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_reference' => 'required|string|max:100',
            'audit_items' => 'required|array|min:1|max:1000',
            'audit_items.*.variant_id' => 'required|integer|exists:product_variants,variant_id',
            'audit_items.*.physical_count' => 'required|integer|min:0',
        ]);

        $employeeId = $request->user()->employee_id ?? $request->user()->id;

        BulkStockOpnameJob::dispatch(
            $validated['audit_items'],
            $employeeId,
            $validated['session_reference']
        );

        if (class_exists(AuditLogService::class)) {
            AuditLogService::log(
                'STOCK_OPNAME_QUEUED',
                'InventorySession',
                $validated['session_reference'],
                null,
                ['items' => count($validated['audit_items'])],
                $employeeId
            );
        }

        return $this->acceptedResponse([
            'session_reference' => $validated['session_reference'],
            'items_submitted' => count($validated['audit_items']),
            'status' => 'QUEUED',
        ], 'Stock opname accepted and queued for background reconciliation');
    }
}
