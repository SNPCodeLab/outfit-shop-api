<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductImageController extends Controller
{
    /**
     * List all gallery images for a specific product
     */
    public function index(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);
        $images = ProductImage::where('product_id', $productId)
            ->orderBy('sort_order', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $images,
            'message' => 'Product images retrieved successfully',
        ]);
    }

    /**
     * Attach a new image/angle to a product
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'image_url'        => 'required|url|max:500',
            'image_public_id'  => 'nullable|string|max:255',
            'variant_id'       => 'nullable|exists:product_variants,variant_id',
            'shot_type'        => 'nullable|string|in:LOOK,FLAT,DETAIL,BANNER,COVER,THUMBNAIL',
            'alt_text'         => 'nullable|string|max:255',
            'sort_order'       => 'nullable|integer',
            'is_primary'       => 'nullable|boolean',
        ]);

        $validated['product_id'] = $productId;
        $validated['shot_type']  = $validated['shot_type'] ?? 'LOOK';

        if (!empty($validated['is_primary'])) {
            // Remove previous primary status
            ProductImage::where('product_id', $productId)->update(['is_primary' => false]);
            // Also update product's main image_url
            $product->update([
                'image_url'       => $validated['image_url'],
                'image_public_id' => $validated['image_public_id'] ?? $product->image_public_id,
            ]);
        }

        $image = ProductImage::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $image,
            'message' => 'Product image attached successfully',
        ], 201);
    }

    /**
     * Delete a gallery image
     */
    public function destroy(int $productId, int $imageId): JsonResponse
    {
        $image = ProductImage::where('product_id', $productId)
            ->where('image_id', $imageId)
            ->firstOrFail();

        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product image deleted successfully',
        ]);
    }
}
