<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\PosRuleException;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\V1\OrderResource;
use App\Models\SaleHeader;
use App\Services\POSService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'details.variant.size',
            'details.variant.color',
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

        $perPage = $this->perPage($request);
        $orders = $query->orderBy('sale_id', 'desc')->paginate($perPage);
        $orders->through(fn ($order) => new OrderResource($order));

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

            return $this->successResponse(
                new OrderResource($order->loadMissing([
                    'customer',
                    'employee',
                    'details.variant.product',
                    'details.variant.size',
                    'details.variant.color',
                    'payments',
                ])),
                $msg,
                $httpCode
            );
        } catch (PosRuleException $e) {
            return $this->errorResponse($e->getMessage(), 422, 'ERR_CHECKOUT_RULE_VIOLATION');
        } catch (\Throwable $e) {
            Log::error('Checkout failed unexpectedly', [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return $this->serverErrorResponse('Checkout failed due to an unexpected server error.');
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

        return $this->successResponse(new OrderResource($order), 'Order invoice receipt details');
    }

    /**
     * Cancel / void an existing completed order and restore inventory.
     * Restricted to MANAGER / ADMIN via routes.
     */
    public function void(Request $request, int $id): JsonResponse
    {
        // Token-ability defense in depth on top of the role gate: the token
        // itself must carry sales.void (legacy '*' tokens still satisfy this).
        if (! $request->user()?->tokenCan('sales.void')) {
            return $this->forbiddenResponse('This token is not authorized to void sales.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        try {
            $employeeId = $request->user()->employee_id ?? $request->user()->id;
            $order = $this->posService->voidSale($id, $employeeId, $request->reason);

            return $this->successResponse(
                new OrderResource($order->loadMissing([
                    'customer',
                    'employee',
                    'details.variant.product',
                    'details.variant.size',
                    'details.variant.color',
                    'payments',
                ])),
                "Order #{$id} voided successfully and inventory restored"
            );
        } catch (PosRuleException $e) {
            return $this->errorResponse($e->getMessage(), 422, 'ERR_ORDER_VOID_RULE_VIOLATION');
        } catch (ModelNotFoundException $e) {
            throw $e; // rendered as the standard 404 envelope by the exception handler
        } catch (\Throwable $e) {
            Log::error('Order void failed unexpectedly', [
                'order_id' => $id,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
            ]);

            return $this->serverErrorResponse("Voiding order #{$id} failed due to an unexpected server error.");
        }
    }
}
