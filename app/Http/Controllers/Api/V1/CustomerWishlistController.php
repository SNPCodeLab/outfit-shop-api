<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\CustomerWishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerWishlistController extends BaseApiController
{
    /**
     * Get customer wishlist items.
     * Requires ?customer_id query parameter or Bearer token.
     * Public / Authenticated.
     */
    public function index(Request $request): JsonResponse
    {
        $customerId = $request->user()?->customer_id
            ?? $request->input('customer_id')
            ?? $request->header('X-Customer-Id');

        if (! $customerId) {
            return $this->validationErrorResponse([
                'customer_id' => ['The customer_id field or X-Customer-Id header is required to retrieve wishlist items.'],
            ]);
        }

        $wishlists = CustomerWishlist::with([
            'product.category',
            'product.images',
            'variant.size',
            'variant.color',
        ])
            ->where('customer_id', $customerId)
            ->get();

        return $this->successResponse($wishlists, 'Customer wishlist retrieved successfully');
    }

    /**
     * Add a product to the wishlist directly.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,customer_id',
            'product_id' => 'required|exists:products,product_id',
            'variant_id' => 'nullable|exists:product_variants,variant_id',
        ]);

        $wishlist = CustomerWishlist::firstOrCreate(
            [
                'customer_id' => $validated['customer_id'],
                'product_id' => $validated['product_id'],
            ],
            [
                'variant_id' => $validated['variant_id'] ?? null,
            ]
        );

        return $this->createdResponse($wishlist, 'Product added to wishlist successfully');
    }

    /**
     * Toggle a product in or out of a customer wishlist.
     * Public - no authentication required (guest wishlist support).
     */
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,customer_id',
            'product_id' => 'required|exists:products,product_id',
            'variant_id' => 'nullable|exists:product_variants,variant_id',
        ]);

        $existing = CustomerWishlist::where('customer_id', $validated['customer_id'])
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            $existing->delete();

            return $this->successResponse(
                ['is_saved' => false, 'product_id' => (int) $validated['product_id']],
                'Product removed from wishlist'
            );
        }

        $wishlist = CustomerWishlist::create($validated);

        return $this->createdResponse(
            ['is_saved' => true, 'wishlist' => $wishlist],
            'Product added to wishlist'
        );
    }

    /**
     * Remove an item from wishlist by ID.
     */
    public function destroy(int $id): JsonResponse
    {
        $item = CustomerWishlist::findOrFail($id);
        $item->delete();

        return $this->successResponse(null, 'Item removed from wishlist');
    }
}
