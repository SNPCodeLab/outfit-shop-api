<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ProductVariant;
use App\Models\VariantPricingTier;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VariantPricingTierController extends BaseApiController
{
    /**
     * Get wholesale pricing tiers for a variant.
     * Public - no authentication required.
     */
    public function index(int $variantId): JsonResponse
    {
        $variant = ProductVariant::with('product')->findOrFail($variantId);

        $tiers = VariantPricingTier::where('variant_id', $variantId)
            ->orderBy('min_quantity', 'asc')
            ->get();

        return $this->successResponse([
            'variant_id' => $variant->variant_id,
            'sku' => $variant->sku,
            'retail_price' => (float) $variant->sale_price,
            'tiers' => $tiers,
        ], 'Wholesale pricing tiers retrieved successfully');
    }

    /**
     * Create a volume pricing tier for a variant.
     * Restricted to MANAGER or ADMIN.
     */
    public function store(Request $request, int $variantId): JsonResponse
    {
        $variant = ProductVariant::findOrFail($variantId);

        $validated = $request->validate([
            'min_quantity' => 'required|integer|min:2',
            'max_quantity' => 'nullable|integer|gt:min_quantity',
            'unit_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $validated['variant_id'] = $variantId;

        if (! isset($validated['discount_percentage']) && $variant->sale_price > 0) {
            $validated['discount_percentage'] = round(
                (($variant->sale_price - $validated['unit_price']) / $variant->sale_price) * 100,
                2
            );
        }

        $tier = VariantPricingTier::create($validated);

        AuditLogService::log('CREATE', 'VariantPricingTier', $tier->id, null, $tier->toArray());

        return $this->createdResponse($tier, 'Wholesale pricing tier created successfully', '/api/v1/variants/'.$variantId.'/tiers');
    }
}
