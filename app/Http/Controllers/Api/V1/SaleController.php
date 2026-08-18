<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Sale\CheckoutRequest;
use App\Models\SaleHeader;
use App\Services\POSService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SaleController extends BaseApiController
{
    public function __construct(protected POSService $posService) {}

    /**
     * List sales history with pagination, date filtering, and status filtering.
     *
     * Query params:
     *   ?date=today               — shorthand for today's transactions
     *   ?from_date=YYYY-MM-DD     — date range start
     *   ?to_date=YYYY-MM-DD       — date range end
     *   ?status=COMPLETED         — filter by status (COMPLETED, VOIDED, ESTIMATE, PAID)
     *   ?customer_id=1            — filter by customer
     *   ?per_page=20              — pagination page size (default: 20)
     */
    public function index(Request $request): JsonResponse
    {
        $query = SaleHeader::with([
            'customer',
            'employee',
            'details.variant.product',
            'payments',
        ]);

        // Shorthand: ?date=today
        if ($request->input('date') === 'today') {
            $query->whereDate('sale_date', today());
        }

        // Date range
        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('sale_date', '>=', $fromDate);
        }
        if ($toDate = $request->input('to_date')) {
            $query->whereDate('sale_date', '<=', $toDate);
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', strtoupper($status));
        }

        // Customer filter
        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }

        $perPage = (int) $request->input('per_page', 20);
        $sales   = $query->orderBy('sale_id', 'desc')->paginate($perPage);

        return $this->successResponse($sales, 'Sales history retrieved');
    }

    /**
     * Process a transactional POS checkout.
     *
     * Supports idempotency_key header/body field to safely retry failed requests
     * without creating duplicate sales.
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $idempotencyKey = $validated['idempotency_key']
            ?? $request->header('X-Idempotency-Key');

        try {
            $employeeId = $request->user()->employee_id ?? $request->user()->id;

            $sale = $this->posService->checkout(
                employeeId:     $employeeId,
                customerId:     $validated['customer_id'] ?? null,
                items:          $validated['items'],
                paymentMethod:  $validated['payment_method'] ?? 'CASH',
                paymentAmount:  (float) ($validated['payment_amount'] ?? 0.0),
                overallDiscount:(float) ($validated['overall_discount'] ?? 0.0),
                taxRate:        (float) ($validated['tax_rate'] ?? 10.00),
                idempotencyKey: $idempotencyKey,
            );

            $httpCode = $sale->wasRecentlyCreated ? 201 : 200;
            $msg = $sale->wasRecentlyCreated
                ? 'POS Checkout completed successfully'
                : 'Idempotent request: Existing transaction returned';

            return $this->successResponse($sale, $msg, $httpCode);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400, 'CHECKOUT_FAILED');
        }
    }

    /**
     * Get detailed invoice / sale receipt by ID.
     */
    public function show(int $id): JsonResponse
    {
        $sale = SaleHeader::with([
            'customer',
            'employee',
            'details.variant.product',
            'details.variant.size',
            'details.variant.color',
            'payments',
        ])->findOrFail($id);

        return $this->successResponse($sale, 'Sale invoice receipt details');
    }

    /**
     * Void an existing completed sale and restore inventory.
     * Restricted to MANAGER / ADMIN via routes.
     */
    public function void(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $employeeId = $request->user()->employee_id ?? $request->user()->id;
            $sale       = $this->posService->voidSale($id, $employeeId, $request->reason);

            return $this->successResponse($sale, "Sale #{$id} voided successfully and inventory restored");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400, 'SALE_VOID_FAILED');
        }
    }
}
