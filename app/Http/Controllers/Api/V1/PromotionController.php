<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromotionController extends Controller
{
    /**
     * List all promotions
     */
    public function index(Request $request): JsonResponse
    {
        $promotions = Promotion::orderBy('start_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'data'    => $promotions,
            'message' => 'Promotions retrieved successfully',
        ]);
    }

    /**
     * List currently active promotions for storefront & POS
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

        return response()->json([
            'success' => true,
            'data'    => $activePromos,
            'message' => 'Active promotions retrieved successfully',
        ]);
    }

    /**
     * Verify coupon voucher code and calculate discount
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
            return response()->json([
                'success' => false,
                'message' => 'Invalid, expired, or inactive coupon code.',
            ], 404);
        }

        if ($request->subtotal < $promo->min_spend) {
            return response()->json([
                'success' => false,
                'message' => "Minimum spend of \${$promo->min_spend} required for this coupon.",
            ], 400);
        }

        $discountAmount = 0.0;
        if ($promo->discount_type === 'PERCENTAGE') {
            $discountAmount = round(($request->subtotal * ($promo->discount_value / 100)), 2);
        } else {
            $discountAmount = min((float)$promo->discount_value, (float)$request->subtotal);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'promotion_id'    => $promo->promotion_id,
                'title'           => $promo->title,
                'promo_code'      => $promo->promo_code,
                'discount_type'   => $promo->discount_type,
                'discount_value'  => $promo->discount_value,
                'discount_amount' => $discountAmount,
                'final_total'     => max(0, $request->subtotal - $discountAmount),
            ],
            'message' => 'Coupon applied successfully',
        ]);
    }

    /**
     * Create a new promotion (Manager / Admin)
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

        return response()->json([
            'success' => true,
            'data'    => $promo,
            'message' => 'Promotion campaign created successfully',
        ], 201);
    }

    /**
     * Delete a promotion
     */
    public function destroy(int $id): JsonResponse
    {
        $promo = Promotion::findOrFail($id);
        $promo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Promotion deleted successfully',
        ]);
    }
}
