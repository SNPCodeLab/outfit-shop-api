<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SaleHeader;
use App\Services\KhqrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KhqrPaymentController extends BaseApiController
{
    /**
     * Generate dynamic Bakong KHQR for an existing POS sale invoice.
     * Public - no authentication required.
     */
    public function generateForSale(int $saleId): JsonResponse
    {
        $sale = SaleHeader::findOrFail($saleId);

        $khqr = KhqrService::generateDynamicKhqr(
            amount: (float) $sale->grand_total,
            currency: 'USD',
            billNumber: 'INV-'.str_pad((string) $sale->sale_id, 6, '0', STR_PAD_LEFT)
        );

        return $this->successResponse($khqr, 'Bakong dynamic KHQR generated successfully');
    }

    /**
     * Generate dynamic KHQR on-the-fly for any amount and currency.
     * Public - no authentication required.
     */
    public function generateCustom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|in:USD,KHR',
            'bill_number' => 'nullable|string|max:50',
        ]);

        $khqr = KhqrService::generateDynamicKhqr(
            amount: (float) $validated['amount'],
            currency: $validated['currency'] ?? 'USD',
            billNumber: $validated['bill_number'] ?? ('POS-'.time())
        );

        return $this->successResponse($khqr, 'Custom Bakong KHQR generated successfully');
    }
}
