<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SaleHeader;
use App\Services\POSService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends BaseApiController
{
    public function __construct(protected POSService $posService) {}

    public function index(): JsonResponse
    {
        $sales = SaleHeader::with(['customer', 'employee', 'details.variant.product', 'payments'])->get();
        return $this->successResponse($sales, 'Sales history');
    }

    /**
     * Process a transactional POS checkout receipt.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id'        => 'nullable|exists:customers,customer_id',
            'items'              => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,variant_id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.discount'   => 'nullable|numeric|min:0',
            'payment_method'     => 'nullable|string|in:CASH,CARD,QR,ABA',
            'payment_amount'     => 'nullable|numeric|min:0',
            'overall_discount'   => 'nullable|numeric|min:0',
            'tax_rate'           => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $employeeId = $request->user()->employee_id ?? $request->user()->id;

            $sale = $this->posService->checkout(
                employeeId: $employeeId,
                customerId: $validated['customer_id'] ?? null,
                items: $validated['items'],
                paymentMethod: $validated['payment_method'] ?? 'CASH',
                paymentAmount: (float) ($validated['payment_amount'] ?? 0.0),
                overallDiscount: (float) ($validated['overall_discount'] ?? 0.0),
                taxRate: (float) ($validated['tax_rate'] ?? 10.00)
            );

            return $this->successResponse($sale, 'POS Checkout completed successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show(int $id): JsonResponse
    {
        $sale = SaleHeader::with(['customer', 'employee', 'details.variant.product', 'details.variant.size', 'details.variant.color', 'payments'])
            ->findOrFail($id);

        return $this->successResponse($sale, 'Sale invoice receipt details');
    }

    /**
     * Void an existing sale (ADMIN/MANAGER restricted).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function void(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $employeeId = $request->user()->employee_id ?? $request->user()->id;
            $sale = $this->posService->voidSale($id, $employeeId, $request->reason);

            return $this->successResponse($sale, 'Sale #'.$id.' voided successfully and inventory restored');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
