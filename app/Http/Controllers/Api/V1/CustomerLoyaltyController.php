<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Customer;
use App\Models\CustomerLoyaltyLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerLoyaltyController extends BaseApiController
{
    /**
     * Get VIP tier, points balance, and loyalty history for a customer.
     * Requires authentication (any role).
     */
    public function show(int $customerId): JsonResponse
    {
        $customer = Customer::findOrFail($customerId);

        $logs = CustomerLoyaltyLog::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        $tierPerks = match ($customer->vip_tier) {
            'PLATINUM' => '15% automatic discount on all apparel plus free home delivery',
            'GOLD'     => '10% automatic discount on all apparel plus priority checkout',
            'SILVER'   => '5% automatic discount on all apparel',
            default    => 'Standard points earning: 1 point per $1 spent',
        };

        return $this->successResponse([
            'customer_id'          => $customer->customer_id,
            'customer_name'        => $customer->customer_name,
            'vip_tier'             => $customer->vip_tier ?? 'BRONZE',
            'loyalty_points'       => $customer->loyalty_points ?? 0,
            'total_spent_lifetime' => (float) ($customer->total_spent_lifetime ?? 0.0),
            'store_credit_balance' => (float) ($customer->store_credit_balance ?? 0.0),
            'tier_perks'           => $tierPerks,
            'conversion_rate'      => '100 points = $5.00 discount voucher',
            'history'              => $logs,
        ], 'Customer loyalty profile retrieved successfully');
    }

    /**
     * Redeem customer loyalty points for a discount voucher.
     * Requires authentication (any role).
     */
    public function redeem(Request $request, int $customerId): JsonResponse
    {
        $customer = Customer::findOrFail($customerId);

        $validated = $request->validate([
            'points_to_redeem' => 'required|integer|min:100',
        ]);

        $pts = $validated['points_to_redeem'];

        if (($customer->loyalty_points ?? 0) < $pts) {
            return $this->errorResponse(
                "Insufficient loyalty points. Customer has {$customer->loyalty_points} points available.",
                422,
                'INSUFFICIENT_LOYALTY_POINTS'
            );
        }

        $discountValue = round(($pts / 100) * 5.0, 2);
        $newBalance    = $customer->loyalty_points - $pts;

        $customer->update(['loyalty_points' => $newBalance]);

        $log = CustomerLoyaltyLog::create([
            'customer_id'      => $customerId,
            'transaction_type' => 'REDEEM',
            'points'           => -$pts,
            'balance_after'    => $newBalance,
            'description'      => "Redeemed {$pts} points for \${$discountValue} discount voucher",
        ]);

        return $this->successResponse([
            'points_redeemed'  => $pts,
            'discount_value'   => $discountValue,
            'remaining_points' => $newBalance,
            'log'              => $log,
        ], "Successfully redeemed {$pts} points for a \${$discountValue} voucher");
    }
}
