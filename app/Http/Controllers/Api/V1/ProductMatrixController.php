<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ProductMatrixController extends BaseApiController
{
    /**
     * Get the Size x Color inventory matrix for a product.
     * Results are cached for 1 hour.
     * Public - no authentication required.
     */
    public function matrix(int $productId): JsonResponse
    {
        $payload = Cache::remember("product_matrix:{$productId}", 3600, function () use ($productId) {
            $product = Product::with(['category', 'images', 'variants.size', 'variants.color'])->findOrFail($productId);
            $variants = $product->variants;

            $sizes = [];
            $colors = [];
            $matrixGrid = [];

            foreach ($variants as $v) {
                $sizeKey = $v->size ? $v->size->size_name : 'Standard';
                $sizeId = $v->size_id ?? 0;
                $colorKey = $v->color ? $v->color->color_name : 'Default';
                $colorId = $v->color_id ?? 0;
                $colorHex = $v->color ? $v->color->hex_code : '#000000';

                if (! isset($sizes[$sizeId])) {
                    $sizes[$sizeId] = [
                        'size_id' => $sizeId,
                        'size_name' => $sizeKey,
                        'size_code' => $v->size->size_code ?? 'STD',
                    ];
                }

                if (! isset($colors[$colorId])) {
                    $colors[$colorId] = [
                        'color_id' => $colorId,
                        'color_name' => $colorKey,
                        'hex_code' => $colorHex,
                        'color_code' => $v->color->color_code ?? 'DFT',
                    ];
                }

                $matrixGrid[$colorId][$sizeId] = [
                    'variant_id' => $v->variant_id,
                    'sku' => $v->sku,
                    'barcode' => $v->barcode,
                    'quantity' => $v->quantity,
                    'sale_price' => (float) $v->sale_price,
                    'cost_price' => (float) $v->cost_price,
                    'wholesale_price' => (float) ($v->wholesale_price ?? $v->sale_price),
                    'unit_of_measure' => $v->unit_of_measure ?? 'PIECE',
                    'in_stock' => $v->quantity > 0,
                    'is_low_stock' => $v->quantity <= ($v->reorder_level ?? 5),
                    'image_url' => $v->image_url ?? $product->image_url,
                ];
            }

            return [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'brand' => $product->brand,
                'product_type' => $product->product_type,
                'gender' => $product->gender,
                'material_fabric' => $product->material_fabric,
                'season_collection' => $product->season_collection,
                'total_stock' => $variants->sum('quantity'),
                'sizes_available' => array_values($sizes),
                'colors_available' => array_values($colors),
                'matrix' => $matrixGrid,
            ];
        });

        return $this->successResponse($payload, 'Product inventory matrix generated successfully');
    }

    /**
     * Get colorways with swatch colors and total availability per color.
     * Results are cached for 1 hour.
     * Public - no authentication required.
     */
    public function colorways(int $productId): JsonResponse
    {
        $colorways = Cache::remember("product_colorways:{$productId}", 3600, function () use ($productId) {
            $product = Product::with(['variants.color', 'images'])->findOrFail($productId);
            $result = [];

            foreach ($product->variants->groupBy('color_id') as $colorId => $variants) {
                $sample = $variants->first();
                $colorName = $sample->color ? $sample->color->color_name : 'Standard';
                $hexCode = $sample->color ? $sample->color->hex_code : '#000000';

                $result[] = [
                    'color_id' => $colorId,
                    'color_name' => $colorName,
                    'hex_code' => $hexCode,
                    'swatch_image' => $sample->image_url ?? $product->image_url,
                    'variants_count' => $variants->count(),
                    'total_available' => $variants->sum('quantity'),
                ];
            }

            return $result;
        });

        return $this->successResponse($colorways, 'Product colorways retrieved successfully');
    }
}
