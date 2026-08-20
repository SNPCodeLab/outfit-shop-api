<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Services\AuditLogService;
use App\Services\WebhookDispatcherService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockTransferController extends BaseApiController
{
    /**
     * GET /api/v1/stock-transfers
     * List all stock transfers with optional status and branch filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = StockTransfer::with(['items.variant.product', 'requester', 'approver', 'shipper', 'receiver'])
            ->orderBy('transfer_id', 'desc');

        if ($status = $request->input('status')) {
            $query->where('status', strtoupper($status));
        }

        if ($fromBranch = $request->input('from_branch_id')) {
            $query->where('from_branch_id', $fromBranch);
        }

        if ($toBranch = $request->input('to_branch_id')) {
            $query->where('to_branch_id', $toBranch);
        }

        $perPage = (int) $request->input('per_page', 20);
        $transfers = $query->paginate($perPage);

        return $this->successResponse($transfers, 'Stock transfers retrieved');
    }

    /**
     * GET /api/v1/stock-transfers/{id}
     */
    public function show(int $id): JsonResponse
    {
        $transfer = StockTransfer::with(['items.variant.product', 'requester', 'approver'])
            ->findOrFail($id);

        return $this->successResponse($transfer, 'Stock transfer details');
    }

    /**
     * STAGE 1: Request Transfer
     * POST /api/v1/stock-transfers
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_branch_id' => 'required|integer',
            'to_branch_id' => 'required|integer|different:from_branch_id',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,variant_id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $employeeId = $request->user()?->id ?? $request->user()?->employee_id ?? 1;

        $transfer = DB::transaction(function () use ($validated, $employeeId) {
            $transferNo = 'TRF-'.now()->format('Ymd').'-'.strtoupper(uniqid());

            $transfer = StockTransfer::create([
                'transfer_no' => $transferNo,
                'from_branch_id' => $validated['from_branch_id'],
                'to_branch_id' => $validated['to_branch_id'],
                'status' => 'REQUESTED',
                'requested_by' => $employeeId,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                StockTransferItem::create([
                    'transfer_id' => $transfer->transfer_id,
                    'variant_id' => $item['variant_id'],
                    'quantity_requested' => $item['quantity'],
                    'quantity_shipped' => 0,
                    'quantity_received' => 0,
                ]);
            }

            AuditLogService::log('CREATE', 'StockTransfer', $transfer->transfer_id, null, [
                'transfer_no' => $transferNo,
                'items_count' => count($validated['items']),
            ]);

            return $transfer->load('items.variant.product');
        });

        return $this->createdResponse($transfer, 'Stock transfer requested successfully', '/api/v1/stock-transfers/'.$transfer->transfer_id);
    }

    /**
     * STAGE 2: Approve Transfer
     * POST /api/v1/stock-transfers/{id}/approve
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $transfer = StockTransfer::findOrFail($id);

        if ($transfer->status !== 'REQUESTED') {
            return $this->conflictResponse(
                "Transfer cannot be approved from current status [{$transfer->status}]",
                'INVALID_TRANSFER_STATUS',
                ['current_status' => $transfer->status, 'required_status' => 'REQUESTED']
            );
        }

        $employeeId = $request->user()?->id ?? $request->user()?->employee_id ?? 1;

        $transfer->update([
            'status' => 'APPROVED',
            'approved_by' => $employeeId,
        ]);

        return $this->successResponse($transfer->fresh(), 'Stock transfer approved');
    }

    /**
     * STAGE 3: Pick Transfer
     * POST /api/v1/stock-transfers/{id}/pick
     */
    public function pick(Request $request, int $id): JsonResponse
    {
        $transfer = StockTransfer::findOrFail($id);

        if ($transfer->status !== 'APPROVED') {
            return $this->conflictResponse(
                "Transfer cannot be picked from status [{$transfer->status}]",
                'INVALID_TRANSFER_STATUS',
                ['current_status' => $transfer->status, 'required_status' => 'APPROVED']
            );
        }

        $transfer->update(['status' => 'PICKED']);

        return $this->successResponse($transfer->fresh(), 'Stock transfer marked as PICKED in warehouse');
    }

    /**
     * STAGE 4: Ship Transfer
     * POST /api/v1/stock-transfers/{id}/ship
     * Deducts origin stock atomically with pessimistic locking.
     */
    public function ship(Request $request, int $id): JsonResponse
    {
        $employeeId = $request->user()?->id ?? $request->user()?->employee_id ?? 1;

        try {
            $transfer = DB::transaction(function () use ($id, $employeeId) {
                $transfer = StockTransfer::with('items.variant')->lockForUpdate()->findOrFail($id);

                if (! in_array($transfer->status, ['APPROVED', 'PICKED'])) {
                    throw new Exception("Transfer cannot be shipped from status [{$transfer->status}]");
                }

                foreach ($transfer->items as $item) {
                    $variant = ProductVariant::where('variant_id', $item->variant_id)->lockForUpdate()->firstOrFail();
                    $qty = $item->quantity_requested;

                    if ($variant->quantity < $qty) {
                        throw new Exception("Insufficient stock for SKU [{$variant->sku}] at origin branch. Available: {$variant->quantity}, Requested: {$qty}");
                    }

                    $stockBefore = (int) $variant->quantity;
                    $variant->decrement('quantity', $qty);
                    $stockAfter = $stockBefore - $qty;

                    // Update shipped quantity on item
                    $item->update(['quantity_shipped' => $qty]);

                    // Write Outgoing Stock Movement
                    StockMovement::create([
                        'variant_id' => $variant->variant_id,
                        'movement_type' => 'RETURN_OUT',
                        'quantity' => -$qty,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'movement_date' => now(),
                        'reference_type' => 'StockTransferOut',
                        'reference_id' => $transfer->transfer_id,
                        'note' => "Inter-Store Transfer Out: {$transfer->transfer_no} to Branch #{$transfer->to_branch_id}",
                        'employee_id' => $employeeId,
                        'created_by' => $employeeId,
                    ]);
                }

                $transfer->update([
                    'status' => 'SHIPPED',
                    'shipped_by' => $employeeId,
                    'shipped_at' => now(),
                ]);

                return $transfer->fresh(['items.variant']);
            });

            Log::channel('inventory')->info("Stock transfer shipped: {$transfer->transfer_no}");

            return $this->successResponse($transfer, 'Stock transfer shipped and origin inventory deducted');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * STAGE 5: Receive Transfer
     * POST /api/v1/stock-transfers/{id}/receive
     * Increments destination stock and triggers Webhook event.
     */
    public function receive(Request $request, int $id): JsonResponse
    {
        $employeeId = $request->user()?->id ?? $request->user()?->employee_id ?? 1;

        try {
            $transfer = DB::transaction(function () use ($id, $employeeId) {
                $transfer = StockTransfer::with('items.variant')->lockForUpdate()->findOrFail($id);

                if ($transfer->status !== 'SHIPPED') {
                    throw new Exception("Transfer cannot be received from status [{$transfer->status}]");
                }

                foreach ($transfer->items as $item) {
                    $variant = ProductVariant::where('variant_id', $item->variant_id)->lockForUpdate()->firstOrFail();
                    $qty = $item->quantity_shipped ?: $item->quantity_requested;

                    $stockBefore = (int) $variant->quantity;
                    $variant->increment('quantity', $qty);
                    $stockAfter = $stockBefore + $qty;

                    $item->update(['quantity_received' => $qty]);

                    // Write Incoming Stock Movement
                    StockMovement::create([
                        'variant_id' => $variant->variant_id,
                        'movement_type' => 'RETURN_IN',
                        'quantity' => $qty,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'movement_date' => now(),
                        'reference_type' => 'StockTransferIn',
                        'reference_id' => $transfer->transfer_id,
                        'note' => "Inter-Store Transfer In: {$transfer->transfer_no} from Branch #{$transfer->from_branch_id}",
                        'employee_id' => $employeeId,
                        'created_by' => $employeeId,
                    ]);
                }

                $transfer->update([
                    'status' => 'RECEIVED',
                    'received_by' => $employeeId,
                    'received_at' => now(),
                ]);

                $transfer = $transfer->fresh(['items.variant']);

                // Trigger Webhook Event (Async dispatch)
                WebhookDispatcherService::dispatch('STOCK_TRANSFER_COMPLETED', [
                    'transfer_id' => $transfer->transfer_id,
                    'transfer_no' => $transfer->transfer_no,
                    'from_branch_id' => $transfer->from_branch_id,
                    'to_branch_id' => $transfer->to_branch_id,
                    'items_count' => $transfer->items->count(),
                    'received_at' => $transfer->received_at ? $transfer->received_at->toISOString() : now()->toISOString(),
                ]);

                return $transfer;
            });

            Log::channel('inventory')->info("Stock transfer received and completed: {$transfer->transfer_no}");

            return $this->successResponse($transfer, 'Stock transfer received and destination inventory updated');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Cancel Transfer
     * POST /api/v1/stock-transfers/{id}/cancel
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $transfer = StockTransfer::findOrFail($id);

        if (in_array($transfer->status, ['RECEIVED', 'CANCELLED'])) {
            return $this->conflictResponse(
                "Transfer cannot be cancelled from status [{$transfer->status}]",
                'INVALID_TRANSFER_STATUS',
                ['current_status' => $transfer->status, 'terminal_states' => ['RECEIVED', 'CANCELLED']]
            );
        }

        $transfer->update(['status' => 'CANCELLED']);

        return $this->successResponse($transfer->fresh(), 'Stock transfer cancelled successfully');
    }
}
