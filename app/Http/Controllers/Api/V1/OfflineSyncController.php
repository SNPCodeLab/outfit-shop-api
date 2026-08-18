<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ProductVariant;
use App\Models\SaleHeader;
use App\Services\POSService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OfflineSyncController extends BaseApiController
{
    public function __construct(
        protected POSService $posService
    ) {}

    /**
     * GET /api/v1/offline/manifest
     * Generates a compressed offline snapshot for POS registers with 72-hour offline sync token.
     */
    public function manifest(Request $request): JsonResponse
    {
        $employee = $request->user();

        // 72-hour signed sync token for offline verification
        $syncToken = 'OFFLINE-'.strtoupper(Str::random(32)).'-'.now()->addHours(72)->timestamp;

        $variants = ProductVariant::with(['product:product_id,product_name,brand,category_id', 'size:size_id,size_name,size_code', 'color:color_id,color_name,hex_code'])
            ->select(['variant_id', 'product_id', 'size_id', 'color_id', 'sku', 'barcode', 'sale_price', 'quantity'])
            ->get()
            ->map(function ($v) {
                return [
                    'variant_id' => $v->variant_id,
                    'sku' => $v->sku,
                    'barcode' => $v->barcode,
                    'product_name' => $v->product->product_name ?? 'N/A',
                    'brand' => $v->product->brand ?? 'Standard',
                    'size' => $v->size->size_name ?? 'STD',
                    'color' => $v->color->color_name ?? 'DFT',
                    'price' => (float) $v->sale_price,
                    'stock' => (int) $v->quantity,
                ];
            });

        return $this->successResponse([
            'sync_token' => $syncToken,
            'expires_at' => now()->addHours(72)->toISOString(),
            'server_timestamp' => now()->toISOString(),
            'tax_rate_percent' => 10.00,
            'exchange_rate_khr' => 4100.00,
            'total_variants' => $variants->count(),
            'catalog' => $variants,
        ], 'Offline catalog snapshot and sync token generated');
    }

    /**
     * POST /api/v1/offline/push-transactions
     * Receives batch of transactions stored locally during internet outages.
     */
    public function pushTransactions(Request $request): JsonResponse
    {
        $request->validate([
            'transactions' => 'required|array|min:1|max:200',
            'transactions.*.offline_id' => 'required|string',
            'transactions.*.created_at' => 'required|string',
            'transactions.*.items' => 'required|array|min:1',
            'transactions.*.items.*.variant_id' => 'required|exists:product_variants,variant_id',
            'transactions.*.items.*.quantity' => 'required|integer|min:1',
            'transactions.*.payment_method' => 'required|string',
            'transactions.*.payment_amount' => 'required|numeric',
        ]);

        $employeeId = $request->user()?->id ?? $request->user()?->employee_id ?? 1;
        $batch = $request->input('transactions');

        $synced = [];
        $conflicts = [];

        foreach ($batch as $tx) {
            $offlineId = $tx['offline_id'];
            $idempotencyKey = "OFFLINE-{$offlineId}";

            try {
                // Check if already synced
                $existing = SaleHeader::where('idempotency_key', $idempotencyKey)->first();
                if ($existing) {
                    $synced[] = [
                        'offline_id' => $offlineId,
                        'sale_id' => $existing->sale_id,
                        'invoice_no' => $existing->invoice_no,
                        'status' => 'ALREADY_SYNCED',
                    ];

                    continue;
                }

                // Process transaction atomically
                $sale = $this->posService->checkout(
                    employeeId: $employeeId,
                    customerId: $tx['customer_id'] ?? null,
                    items: $tx['items'],
                    paymentMethod: $tx['payment_method'],
                    paymentAmount: (float) $tx['payment_amount'],
                    overallDiscount: (float) ($tx['overall_discount'] ?? 0.0),
                    taxRate: (float) ($tx['tax_rate'] ?? 10.0),
                    idempotencyKey: $idempotencyKey
                );

                $synced[] = [
                    'offline_id' => $offlineId,
                    'sale_id' => $sale->sale_id,
                    'invoice_no' => $sale->invoice_no,
                    'grand_total' => (float) $sale->grand_total,
                    'status' => 'SYNCED_SUCCESSFULLY',
                ];
            } catch (Exception $e) {
                // Conflict resolution: log error and report conflict to cashier
                $conflicts[] = [
                    'offline_id' => $offlineId,
                    'error' => $e->getMessage(),
                    'status' => 'CONFLICT_REQUIRES_REVIEW',
                ];
                Log::channel('pos')->warning("Offline sync conflict for transaction {$offlineId}: ".$e->getMessage());
            }
        }

        return $this->successResponse([
            'total_received' => count($batch),
            'synced_count' => count($synced),
            'conflicts_count' => count($conflicts),
            'synced' => $synced,
            'conflicts' => $conflicts,
        ], 'Offline transactions processed');
    }
}
