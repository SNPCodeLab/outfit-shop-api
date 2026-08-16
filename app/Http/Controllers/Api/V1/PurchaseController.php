<?php

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

    public function index(): JsonResponse
    {
        $purchases = PurchaseHeader::with(['supplier', 'employee', 'details.variant.product'])->get();
        return $this->successResponse($purchases, 'Purchases history');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id'          => 'required|exists:suppliers,supplier_id',
            'items'                => 'required|array|min:1',
            'items.*.variant_id'   => 'required|exists:product_variants,variant_id',
            'items.*.quantity'     => 'required|integer|min:1',
            'items.*.cost_price'   => 'required|numeric|min:0',
        ]);

        try {
            $employeeId = $request->user()->employee_id ?? $request->user()->id;
            $purchase = $this->inventoryService->receivePurchase(
                supplierId: $validated['supplier_id'],
                employeeId: $employeeId,
                items: $validated['items']
            );

            return $this->successResponse($purchase, 'Purchase order received successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        $purchase = PurchaseHeader::with(['supplier', 'employee', 'details.variant.product', 'details.variant.size', 'details.variant.color'])
            ->findOrFail($id);

        return $this->successResponse($purchase, 'Purchase order details');
    }
}
