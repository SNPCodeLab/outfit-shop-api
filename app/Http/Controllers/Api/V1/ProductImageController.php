<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\CloudinaryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductImageController extends BaseApiController
{
    /**
     * List all gallery images for a specific product.
     * Public - no authentication required.
     */
    public function index(Request $request, int $productId): JsonResponse
    {
        Product::findOrFail($productId);

        $images = ProductImage::where('product_id', $productId)
            ->orderBy('sort_order', 'asc')
            ->get();

        return $this->successResponse($images, 'Product images retrieved successfully');
    }

    /**
     * Attach a new image or shooting angle to a product.
     * Supports both direct image_url and file upload.
     * Restricted to MANAGER or ADMIN.
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $validated = $request->validate([
            'image' => 'required_without:image_url|file|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'image_url' => 'required_without:image|nullable|url|max:500',
            'image_public_id' => 'nullable|string|max:255',
            'variant_id' => 'nullable|exists:product_variants,variant_id',
            'shot_type' => 'nullable|string|in:LOOK,FLAT,DETAIL,BANNER,COVER,THUMBNAIL,FRONT,BACK,SIDE',
            'alt_text' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_primary' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            try {
                $cloudinary = app(CloudinaryService::class);
                $uploadResult = $cloudinary->upload($request->file('image'), 'khmeriel/gallery');
                $validated['image_url'] = $uploadResult['secure_url'];
                $validated['image_public_id'] = $uploadResult['public_id'];
            } catch (\Exception $e) {
                return $this->serverErrorResponse('Failed to upload image to Cloudinary: '.$e->getMessage());
            }
        }

        $validated['product_id'] = $productId;
        $validated['shot_type'] = strtoupper($validated['shot_type'] ?? 'LOOK');

        if (! empty($validated['is_primary'])) {
            ProductImage::where('product_id', $productId)->update(['is_primary' => false]);
            $product->update([
                'image_url' => $validated['image_url'],
                'image_public_id' => $validated['image_public_id'] ?? $product->image_public_id,
            ]);
        }

        $image = ProductImage::create($validated);

        return $this->createdResponse(
            $image,
            'Product image attached successfully',
            '/api/v1/products/'.$productId.'/images'
        );
    }

    /**
     * Delete a gallery image from a product.
     * Restricted to MANAGER or ADMIN.
     */
    public function destroy(int $productId, int $imageId): JsonResponse
    {
        $image = ProductImage::where('product_id', $productId)
            ->where('image_id', $imageId)
            ->firstOrFail();

        $image->delete();

        return $this->deletedResponse('Product image deleted successfully');
    }
}
