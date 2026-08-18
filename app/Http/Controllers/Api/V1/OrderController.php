<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SaleHeader;
use App\Services\POSService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends BaseApiController
{
    public function __construct(protected POSService $posService) {}

    /**
     * List order history with pagination, date filtering, and status filtering.
     *
     * Query params:
     *   ?date=today               — shorthand for today's orders
     *   ?from_date=YYYY-MM-DD     — date range start
     *   ?to_date=YYYY-MM-DD       — date range end
     *   ?status=COMPLETED         — filter by status (COMPLETED, VOIDED, ESTIMATE, PAID, PENDING)
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
        $orders = $query->orderBy('sale_id', 'desc')->paginate($perPage);

        return $this->successResponse($orders, 'Orders history retrieved successfully');
    }

    /**
     * Process an order / POS checkout transaction.
     *
     * Supports idempotency_key header/body field to safely retry failed requests
     * without creating duplicate orders.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,customer_id',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,variant_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.discount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:CASH,CARD,QR,ABA,BAKONG,GIFT_CARD',
            'payment_amount' => 'nullable|numeric|min:0',
            'overall_discount' => 'nullable|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        // Accept idempotency key from request body OR X-Idempotency-Key header
        $idempotencyKey = $validated['idempotency_key']
            ?? $request->header('X-Idempotency-Key');

        try {
            $employeeId = $request->user()->employee_id ?? $request->user()->id;

            $order = $this->posService->checkout(
                employeeId: $employeeId,
                customerId: $validated['customer_id'] ?? null,
                items: $validated['items'],
                paymentMethod: $validated['payment_method'] ?? 'CASH',
                paymentAmount: (float) ($validated['payment_amount'] ?? 0.0),
                overallDiscount: (float) ($validated['overall_discount'] ?? 0.0),
                taxRate: (float) ($validated['tax_rate'] ?? 10.00),
                idempotencyKey: $idempotencyKey,
            );

            $httpCode = $order->wasRecentlyCreated ? 201 : 200;
            $msg = $order->wasRecentlyCreated
                ? 'Order checkout completed successfully'
                : 'Idempotent request: Existing order returned';

            return $this->successResponse($order, $msg, $httpCode);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400, 'ERR_CHECKOUT_FAILED');
        }
    }

    /**
     * Get detailed order invoice / receipt by ID.
     */
    public function show(int $id): JsonResponse
    {
        $order = SaleHeader::with([
            'customer',
            'employee',
            'details.variant.product',
            'details.variant.size',
            'details.variant.color',
            'payments',
        ])->findOrFail($id);

        return $this->successResponse($order, 'Order invoice receipt details');
    }

    /**
     * Cancel / void an existing completed order and restore inventory.
     * Restricted to MANAGER / ADMIN via routes.
     */
    public function void(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $employeeId = $request->user()->employee_id ?? $request->user()->id;
            $order = $this->posService->voidSale($id, $employeeId, $request->reason);

            return $this->successResponse($order, "Order #{$id} voided successfully and inventory restored");
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400, 'ORDER_VOID_FAILED');
        }
    }
}
