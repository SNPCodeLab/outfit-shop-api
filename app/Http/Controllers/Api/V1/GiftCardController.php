<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\GiftCard;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GiftCardController extends BaseApiController
{
    /**
     * Check gift card balance and validity.
     * GET /gift-cards/{code} (RESTful) or legacy POST /gift-cards/check.
     */
    public function check(Request $request, ?string $code = null): JsonResponse
    {
        if ($code !== null) {
            $request->merge(['card_code' => $code]);
        }

        $request->validate([
            'card_code' => 'required|string',
        ]);

        $card = GiftCard::where('card_code', strtoupper($request->card_code))
            ->whereRaw('is_active is true')
            ->first();

        if (! $card) {
            return $this->notFoundResponse('GiftCard', $request->card_code, 'Invalid, inactive, or expired gift card code.');
        }

        if ($card->expiry_date && $card->expiry_date->isPast()) {
            return $this->errorResponse(
                'This gift card has expired.',
                422,
                'GIFT_CARD_EXPIRED',
                ['expiry_date' => $card->expiry_date->toISOString()]
            );
        }

        return $this->successResponse([
            'card_code' => $card->card_code,
            'current_balance' => (float) $card->current_balance,
            'initial_balance' => (float) $card->initial_balance,
            'expiry_date' => $card->expiry_date ? $card->expiry_date->toISOString() : null,
            'is_active' => true,
        ], 'Gift card is valid and ready to use');
    }

    /**
     * Issue a new digital gift card.
     * Requires authentication (any role).
     */
    public function issue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required_without:initial_balance|numeric|min:5|max:1000',
            'initial_balance' => 'required_without:amount|numeric|min:5|max:1000',
            'purchaser_customer_id' => 'nullable|exists:customers,customer_id',
            'expiry_months' => 'nullable|integer|min:1|max:36',
            'expiry_date' => 'nullable|date',
        ]);

        $amount = (float) ($validated['amount'] ?? $validated['initial_balance']);
        $code = 'KM-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));

        if (! empty($validated['expiry_date'])) {
            $expiry = Carbon::parse($validated['expiry_date']);
        } else {
            $months = (int) ($validated['expiry_months'] ?? 12);
            $expiry = Carbon::now()->addMonths($months);
        }

        $card = GiftCard::create([
            'card_code' => $code,
            'initial_balance' => $amount,
            'current_balance' => $amount,
            'purchaser_customer_id' => $validated['purchaser_customer_id'] ?? null,
            'expiry_date' => $expiry,
            'is_active' => true,
        ]);

        return $this->createdResponse(
            $card,
            "Digital gift card of \${$amount} issued successfully"
        );
    }
}
