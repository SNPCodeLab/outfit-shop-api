<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerLoyaltyLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerLoyaltyController extends Controller
{
    /**
     * Get VIP Tier, points balance, and loyalty history for a customer
     */
    public function show(int $customerId): JsonResponse
    {
        $customer = Customer::findOrFail($customerId);

        $logs = CustomerLoyaltyLog::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        // Compute Tier perks
        $tierPerks = match($customer->vip_tier) {
            'PLATINUM' => '15% automatic discount on all apparel + Free home delivery',
            'GOLD'     => '10% automatic discount on all apparel + Priority checkout',
            'SILVER'   => '5% automatic discount on all apparel',
            default    => 'Standard points earning: 1 pt per $1 spent',
        };

        return response()->json([
            'success' => true,
            'data'    => [
                'customer_id'          => $customer->customer_id,
                'customer_name'        => $customer->customer_name,
                'vip_tier'             => $customer->vip_tier ?? 'BRONZE',
                'loyalty_points'       => $customer->loyalty_points ?? 0,
                'total_spent_lifetime' => (float) ($customer->total_spent_lifetime ?? 0.0),
                'store_credit_balance' => (float) ($customer->store_credit_balance ?? 0.0),
                'tier_perks'           => $tierPerks,
                'conversion_rate'      => '100 points = $5.00 discount voucher',
                'history'              => $logs,
            ],
            'message' => 'Customer loyalty profile retrieved successfully',
        ]);
    }

    /**
     * Redeem customer loyalty points for discount voucher
     */
    public function redeem(Request $request, int $customerId): JsonResponse
    {
        $customer = Customer::findOrFail($customerId);

        $validated = $request->validate([
            'points_to_redeem' => 'required|integer|min:100',
        ]);

        $pts = $validated['points_to_redeem'];

        if (($customer->loyalty_points ?? 0) < $pts) {
            return response()->json([
                'success' => false,
                'message' => "Insufficient loyalty points. Customer has {$customer->loyalty_points} points.",
            ], 400);
        }

        $discountValue = round(($pts / 100) * 5.0, 2);
        $newBalance = $customer->loyalty_points - $pts;

        $customer->update(['loyalty_points' => $newBalance]);

        $log = CustomerLoyaltyLog::create([
            'customer_id'      => $customerId,
            'transaction_type' => 'REDEEM',
            'points'           => -$pts,
            'balance_after'    => $newBalance,
            'description'      => "Redeemed {$pts} points for \${$discountValue} discount voucher",
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'points_redeemed'  => $pts,
                'discount_value'   => $discountValue,
                'remaining_points' => $newBalance,
                'log'              => $log,
            ],
            'message' => "Successfully redeemed {$pts} points for \${$discountValue} voucher!",
        ]);
    }
}
