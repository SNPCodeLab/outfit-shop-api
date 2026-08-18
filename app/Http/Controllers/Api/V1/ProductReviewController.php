<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductReviewController extends BaseApiController
{
    /**
     * Get reviews and rating summary for a product.
     * Public - no authentication required.
     */
    public function index(int $productId): JsonResponse
    {
        Product::findOrFail($productId);

        $reviews = ProductReview::where('product_id', $productId)
            ->where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $totalReviews = $reviews->count();
        $averageRating = $totalReviews > 0 ? round($reviews->avg('rating'), 1) : 5.0;

        return $this->successResponse([
            'product_id' => $productId,
            'average_rating' => $averageRating,
            'total_reviews' => $totalReviews,
            'rating_breakdown' => [
                '5_star' => $reviews->where('rating', 5)->count(),
                '4_star' => $reviews->where('rating', 4)->count(),
                '3_star' => $reviews->where('rating', 3)->count(),
                '2_star' => $reviews->where('rating', 2)->count(),
                '1_star' => $reviews->where('rating', 1)->count(),
            ],
            'reviews' => $reviews,
        ], 'Product reviews retrieved successfully');
    }

    /**
     * Submit a new customer product review.
     * Public - no authentication required (guest review support).
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        Product::findOrFail($productId);

        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,customer_id',
            'reviewer_name' => 'required|string|max:100',
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:150',
            'comment' => 'required|string|max:2000',
        ]);

        $validated['product_id'] = $productId;
        $validated['is_verified_purchase'] = $request->has('customer_id');
        $validated['is_approved'] = true;

        $review = ProductReview::create($validated);

        return $this->createdResponse(
            $review,
            'Your review has been submitted successfully',
            '/api/v1/products/'.$productId.'/reviews'
        );
    }
}
