<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CustomerWishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerWishlistController extends Controller
{
    /**
     * Get customer wishlist items
     */
    public function index(Request $request): JsonResponse
    {
        $customerId = $request->input('customer_id');

        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Customer ID parameter required',
            ], 400);
        }

        $wishlists = CustomerWishlist::with(['product.category', 'product.images', 'variant.size', 'variant.color'])
            ->where('customer_id', $customerId)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $wishlists,
            'message' => 'Customer wishlist retrieved successfully',
        ]);
    }

    /**
     * Toggle product in/out of customer wishlist
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,customer_id',
            'product_id'  => 'required|exists:products,product_id',
            'variant_id'  => 'nullable|exists:product_variants,variant_id',
        ]);

        $existing = CustomerWishlist::where('customer_id', $validated['customer_id'])
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'success'    => true,
                'is_saved'   => false,
                'message'    => 'Removed from wishlist',
            ]);
        }

        $wishlist = CustomerWishlist::create($validated);

        return response()->json([
            'success'    => true,
            'is_saved'   => true,
            'data'       => $wishlist,
            'message'    => 'Added to wishlist',
        ], 201);
    }
}
