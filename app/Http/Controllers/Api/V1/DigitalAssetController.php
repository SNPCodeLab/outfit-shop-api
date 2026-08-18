<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DigitalAssetController extends BaseApiController
{
    /**
     * Get digital publication details and a time-limited download link.
     * Public - no authentication required.
     */
    public function download(Request $request, int $productId): JsonResponse
    {
        $product = Product::with(['category', 'variants'])->findOrFail($productId);

        if ($product->product_type !== 'DIGITAL_DOWNLOAD') {
            return $this->errorResponse(
                'This product is a physical item, not a digital publication.',
                422,
                'NOT_A_DIGITAL_PRODUCT'
            );
        }

        $variant = $product->variants->first();
        $fileUrl = $variant?->download_file_url ?? $product->image_url;

        return $this->successResponse([
            'product_id' => $product->product_id,
            'title' => $product->product_name,
            'author' => $product->author_artist ?? 'KhmeRiel Press',
            'isbn' => $product->isbn_code ?? null,
            'file_format' => 'PDF',
            'download_url' => $fileUrl,
            'cover_image_url' => $product->image_url,
            'download_expires' => now()->addHours(24)->toISOString(),
        ], 'Digital publication ready for download');
    }
}
