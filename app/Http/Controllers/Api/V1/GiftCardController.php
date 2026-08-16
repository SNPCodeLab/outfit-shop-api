<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GiftCard;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GiftCardController extends Controller
{
    /**
     * Check gift card balance and validity
     */
    public function check(Request $request): JsonResponse
    {
        $request->validate([
            'card_code' => 'required|string',
        ]);

        $card = GiftCard::where('card_code', strtoupper($request->card_code))
            ->where('is_active', true)
            ->first();

        if (!$card) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid, inactive, or expired gift card code.',
            ], 404);
        }

        if ($card->expiry_date && $card->expiry_date->isPast()) {
            return response()->json([
                'success' => false,
                'message' => 'This gift card has expired.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'card_code'       => $card->card_code,
                'current_balance' => (float) $card->current_balance,
                'initial_balance' => (float) $card->initial_balance,
                'expiry_date'     => $card->expiry_date ? $card->expiry_date->format('Y-m-d') : 'No Expiry',
            ],
            'message' => 'Gift card is valid and ready to use',
        ]);
    }

    /**
     * Issue a new digital gift card
     */
    public function issue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount'                => 'required|numeric|min:5|max:1000',
            'purchaser_customer_id' => 'nullable|exists:customers,customer_id',
            'expiry_months'         => 'nullable|integer|min:1|max:36',
        ]);

        // Generate 16-digit card code format: KHMER-XXXX-XXXX-XXXX
        $code = 'KM-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4)) . '-' . strtoupper(Str::random(4));

        $months = (int) ($validated['expiry_months'] ?? 12);
        $expiry = Carbon::now()->addMonths($months);

        $card = GiftCard::create([
            'card_code'             => $code,
            'initial_balance'       => $validated['amount'],
            'current_balance'       => $validated['amount'],
            'purchaser_customer_id' => $validated['purchaser_customer_id'] ?? null,
            'expiry_date'           => $expiry,
            'is_active'             => true,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $card,
            'message' => "Digital Gift Card of \${$validated['amount']} issued successfully",
        ], 201);
    }
}
