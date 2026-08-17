<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;

class ProductMatrixController extends Controller
{
    /**
     * Get Size x Color Inventory Matrix for a Product (SalesBinder / Luxury POS Grid)
     */
    public function matrix(int $productId): JsonResponse
    {
        $payload = \Illuminate\Support\Facades\Cache::remember("product_matrix:{$productId}", 3600, function () use ($productId) {
            $product = Product::with(['category', 'images', 'variants.size', 'variants.color'])
                ->findOrFail($productId);

            $variants = $product->variants;

            // Collect unique sizes & colors
            $sizes = [];
            $colors = [];
            $matrixGrid = [];

            foreach ($variants as $v) {
                $sizeKey = $v->size ? $v->size->size_name : 'Standard';
                $sizeId  = $v->size_id ?? 0;
                $colorKey = $v->color ? $v->color->color_name : 'Default';
                $colorId  = $v->color_id ?? 0;
                $colorHex = $v->color ? $v->color->hex_code : '#000000';

                if (!isset($sizes[$sizeId])) {
                    $sizes[$sizeId] = [
                        'size_id'   => $sizeId,
                        'size_name' => $sizeKey,
                        'size_code' => $v->size->size_code ?? 'STD',
                    ];
                }

                if (!isset($colors[$colorId])) {
                    $colors[$colorId] = [
                        'color_id'   => $colorId,
                        'color_name' => $colorKey,
                        'hex_code'   => $colorHex,
                        'color_code' => $v->color->color_code ?? 'DFT',
                    ];
                }

                $matrixGrid[$colorId][$sizeId] = [
                    'variant_id'       => $v->variant_id,
                    'sku'              => $v->sku,
                    'barcode'          => $v->barcode,
                    'quantity'         => $v->quantity,
                    'sale_price'       => (float) $v->sale_price,
                    'cost_price'       => (float) $v->cost_price,
                    'wholesale_price'  => (float) ($v->wholesale_price ?? $v->sale_price),
                    'unit_of_measure'  => $v->unit_of_measure ?? 'PIECE',
                    'in_stock'         => $v->quantity > 0,
                    'is_low_stock'     => $v->quantity <= $v->reorder_level,
                    'image_url'        => $v->image_url ?? $product->image_url,
                ];
            }

            return [
                'product_id'        => $product->product_id,
                'product_name'      => $product->product_name,
                'brand'             => $product->brand,
                'product_type'      => $product->product_type,
                'gender'            => $product->gender,
                'material_fabric'   => $product->material_fabric,
                'season_collection' => $product->season_collection,
                'total_stock'       => $variants->sum('quantity'),
                'sizes_available'   => array_values($sizes),
                'colors_available'  => array_values($colors),
                'matrix'            => $matrixGrid,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $payload,
            'message' => 'Product inventory matrix generated successfully',
        ]);
    }

    /**
     * Get Colorways with Swatches and Gallery photos
     */
    public function colorways(int $productId): JsonResponse
    {
        $colorways = \Illuminate\Support\Facades\Cache::remember("product_colorways:{$productId}", 3600, function () use ($productId) {
            $product = Product::with(['variants.color', 'images'])->findOrFail($productId);

            $result = [];
            foreach ($product->variants->groupBy('color_id') as $colorId => $variants) {
                $sample = $variants->first();
                $colorName = $sample->color ? $sample->color->color_name : 'Standard';
                $hexCode = $sample->color ? $sample->color->hex_code : '#000000';

                $result[] = [
                    'color_id'        => $colorId,
                    'color_name'      => $colorName,
                    'hex_code'        => $hexCode,
                    'swatch_image'    => $sample->image_url ?? $product->image_url,
                    'variants_count'  => $variants->count(),
                    'total_available' => $variants->sum('quantity'),
                ];
            }
            return $result;
        });

        return response()->json([
            'success' => true,
            'data'    => $colorways,
            'message' => 'Product colorways retrieved successfully',
        ]);
    }
}
