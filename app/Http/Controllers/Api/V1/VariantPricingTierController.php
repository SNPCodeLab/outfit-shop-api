<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\VariantPricingTier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VariantPricingTierController extends Controller
{
    /**
     * Get wholesale pricing tiers for a variant
     */
    public function index(int $variantId): JsonResponse
    {
        $variant = ProductVariant::with('product')->findOrFail($variantId);
        $tiers = VariantPricingTier::where('variant_id', $variantId)
            ->orderBy('min_quantity', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'variant_id'   => $variant->variant_id,
                'sku'          => $variant->sku,
                'retail_price' => (float) $variant->sale_price,
                'tiers'        => $tiers,
            ],
            'message' => 'Wholesale pricing tiers retrieved successfully',
        ]);
    }

    /**
     * Create or update a volume pricing tier
     */
    public function store(Request $request, int $variantId): JsonResponse
    {
        $variant = ProductVariant::findOrFail($variantId);

        $validated = $request->validate([
            'min_quantity'        => 'required|integer|min:2',
            'max_quantity'        => 'nullable|integer|gt:min_quantity',
            'unit_price'          => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['variant_id'] = $variantId;
        if (!isset($validated['discount_percentage']) && $variant->sale_price > 0) {
            $validated['discount_percentage'] = round((($variant->sale_price - $validated['unit_price']) / $variant->sale_price) * 100, 2);
        }

        $tier = VariantPricingTier::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $tier,
            'message' => 'Wholesale pricing tier created successfully',
        ], 201);
    }
}
