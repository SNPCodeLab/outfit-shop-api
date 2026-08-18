<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Promotion;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionController extends BaseApiController
{
    /**
     * List all promotions (active and inactive).
     * Restricted to MANAGER or ADMIN.
     */
    public function index(Request $request): JsonResponse
    {
        $promotions = Promotion::orderBy('start_date', 'desc')->get();

        return $this->successResponse($promotions, 'Promotions retrieved successfully');
    }

    /**
     * List currently active promotions for storefront and POS.
     * Public - no authentication required.
     */
    public function active(Request $request): JsonResponse
    {
        $query = Promotion::active();

        if ($dept = $request->input('department')) {
            $query->where(function ($q) use ($dept) {
                $q->where('target_department', strtoupper($dept))
                  ->orWhereNull('target_department')
                  ->orWhere('target_department', 'ALL');
            });
        }

        $activePromos = $query->get();

        return $this->successResponse($activePromos, 'Active promotions retrieved successfully');
    }

    /**
     * Verify a coupon code and calculate the applicable discount.
     * Public - no authentication required.
     */
    public function verifyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'promo_code' => 'required|string',
            'subtotal'   => 'required|numeric|min:0',
        ]);

        $promo = Promotion::active()
            ->where('promo_code', strtoupper($request->promo_code))
            ->first();

        if (!$promo) {
            return $this->notFoundResponse('Promotion', $request->promo_code, 'Invalid, expired, or inactive coupon code.');
        }

        if ($request->subtotal < $promo->min_spend) {
            return $this->errorResponse(
                "Minimum spend of \${$promo->min_spend} required for this coupon.",
                422,
                'MINIMUM_SPEND_NOT_MET',
                ['min_spend' => (float) $promo->min_spend, 'current_subtotal' => (float) $request->subtotal]
            );
        }

        $discountAmount = $promo->discount_type === 'PERCENTAGE'
            ? round(($request->subtotal * ($promo->discount_value / 100)), 2)
            : min((float) $promo->discount_value, (float) $request->subtotal);

        return $this->successResponse([
            'promotion_id'    => $promo->promotion_id,
            'title'           => $promo->title,
            'promo_code'      => $promo->promo_code,
            'discount_type'   => $promo->discount_type,
            'discount_value'  => $promo->discount_value,
            'discount_amount' => $discountAmount,
            'final_total'     => max(0, round((float) $request->subtotal - $discountAmount, 2)),
        ], 'Coupon applied successfully');
    }

    /**
     * Create a new promotion campaign.
     * Restricted to MANAGER or ADMIN.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:150',
            'promo_code'        => 'nullable|string|max:50|unique:promotions,promo_code',
            'discount_type'     => 'required|string|in:PERCENTAGE,FIXED_AMOUNT,BUY_X_GET_Y',
            'discount_value'    => 'required|numeric|min:0',
            'min_spend'         => 'nullable|numeric|min:0',
            'target_department' => 'nullable|string|max:50',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after:start_date',
            'max_usage_count'   => 'nullable|integer',
            'is_active'         => 'nullable|boolean',
        ]);

        if (isset($validated['promo_code'])) {
            $validated['promo_code'] = strtoupper($validated['promo_code']);
        }

        $promo = Promotion::create($validated);

        AuditLogService::log('CREATE', 'Promotion', $promo->promotion_id, null, $promo->toArray());

        return $this->createdResponse($promo, 'Promotion campaign created successfully', '/api/v1/promotions/' . $promo->promotion_id);
    }

    /**
     * Delete a promotion campaign.
     * Restricted to MANAGER or ADMIN.
     */
    public function destroy(int $id): JsonResponse
    {
        $promo = Promotion::findOrFail($id);

        AuditLogService::log('DELETE', 'Promotion', $id, $promo->toArray(), null);

        $promo->delete();

        return $this->deletedResponse('Promotion deleted successfully');
    }
}
