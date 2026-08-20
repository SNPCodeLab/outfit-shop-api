<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\PurchaseHeader;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends BaseApiController
{
    public function __construct(protected InventoryService $inventoryService) {}

    /**
     * List purchase orders with pagination, supplier, status, and date range filtering.
     *
     * Query params:
     *   ?supplier_id=1        - Filter by supplier ID
     *   ?status=RECEIVED      - Filter by status (RECEIVED, PENDING, CANCELLED)
     *   ?from_date=YYYY-MM-DD - Filter by purchase date start
     *   ?to_date=YYYY-MM-DD   - Filter by purchase date end
     *   ?per_page=20          - Pagination items per page (default: 20)
     */
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseHeader::with([
            'supplier',
            'employee',
            'details.variant.product',
            'details.variant.size',
            'details.variant.color',
        ]);

        if ($supplierId = $request->input('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', strtoupper($status));
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('purchase_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('purchase_date', '<=', $toDate);
        }

        $perPage = (int) $request->input('per_page', 20);
        $purchases = $query->orderBy('purchase_id', 'desc')->paginate($perPage);

        return $this->successResponse($purchases, 'Purchases history retrieved');
    }

    /**
     * Receive and record a new purchase order with atomic stock increment & ledger movements.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,variant_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required_without:items.*.unit_cost|numeric|min:0',
            'items.*.unit_cost' => 'required_without:items.*.cost_price|numeric|min:0',
        ]);

        // Map Postman's unit_cost to cost_price if needed
        $items = array_map(function ($item) {
            if (! isset($item['cost_price']) && isset($item['unit_cost'])) {
                $item['cost_price'] = $item['unit_cost'];
            }

            return $item;
        }, $validated['items']);

        try {
            $employeeId = $request->user()->employee_id ?? $request->user()->id ?? 1;
            $purchase = $this->inventoryService->receivePurchase(
                supplierId: $validated['supplier_id'],
                employeeId: $employeeId,
                items: $items
            );

            return $this->createdResponse($purchase, 'Purchase order received successfully', '/api/v1/purchases/'.$purchase->purchase_id);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400, 'PURCHASE_RECEIVE_FAILED');
        }
    }

    /**
     * Show single purchase order breakdown.
     */
    public function show(int $id): JsonResponse
    {
        $purchase = PurchaseHeader::with([
            'supplier',
            'employee',
            'details.variant.product',
            'details.variant.size',
            'details.variant.color',
        ])->findOrFail($id);

        return $this->successResponse($purchase, 'Purchase order details retrieved');
    }
}
