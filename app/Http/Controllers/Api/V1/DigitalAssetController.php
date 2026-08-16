<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DigitalAssetController extends Controller
{
    /**
     * Get digital publication details and download link
     */
    public function download(Request $request, int $productId): JsonResponse
    {
        $product = Product::with(['category', 'variants'])->findOrFail($productId);

        if ($product->product_type !== 'DIGITAL_DOWNLOAD') {
            return response()->json([
                'success' => false,
                'message' => 'This product is a physical item, not a digital publication.',
            ], 400);
        }

        $variant = $product->variants->first();
        $fileUrl = $variant->download_file_url ?? $product->image_url;

        return response()->json([
            'success' => true,
            'data'    => [
                'product_id'        => $product->product_id,
                'title'             => $product->product_name,
                'author'            => $product->author_artist ?? 'KhmeRiel Press',
                'isbn'              => $product->isbn_code ?? 'N/A',
                'file_format'       => 'PDF',
                'download_url'      => $fileUrl,
                'cover_image_url'   => $product->image_url,
                'download_expires'  => now()->addHours(24)->toIso8601String(),
            ],
            'message' => 'Digital publication ready for download',
        ]);
    }
}
