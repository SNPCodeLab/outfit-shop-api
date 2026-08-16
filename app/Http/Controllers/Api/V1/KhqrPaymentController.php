<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SaleHeader;
use App\Services\KhqrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KhqrPaymentController extends Controller
{
    /**
     * Generate dynamic Bakong KHQR for an existing POS sale invoice
     */
    public function generateForSale(int $saleId): JsonResponse
    {
        $sale = SaleHeader::findOrFail($saleId);

        $khqr = KhqrService::generateDynamicKhqr(
            amount: (float) $sale->grand_total,
            currency: 'USD',
            billNumber: 'INV-' . str_pad((string)$sale->sale_id, 6, '0', STR_PAD_LEFT)
        );

        return response()->json([
            'success' => true,
            'data'    => $khqr,
            'message' => 'Bakong dynamic KHQR generated successfully',
        ]);
    }

    /**
     * Generate dynamic KHQR on-the-fly for any amount & currency
     */
    public function generateCustom(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount'      => 'required|numeric|min:0.01',
            'currency'    => 'nullable|string|in:USD,KHR',
            'bill_number' => 'nullable|string|max:50',
        ]);

        $khqr = KhqrService::generateDynamicKhqr(
            amount: (float) $validated['amount'],
            currency: $validated['currency'] ?? 'USD',
            billNumber: $validated['bill_number'] ?? ('POS-' . time())
        );

        return response()->json([
            'success' => true,
            'data'    => $khqr,
            'message' => 'Custom Bakong KHQR generated successfully',
        ]);
    }
}
