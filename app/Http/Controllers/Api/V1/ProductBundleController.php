<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\BundleItem;
use App\Models\ProductBundle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBundleController extends Controller
{
    /**
     * List all product bundles & combo packs
     */
    public function index(): JsonResponse
    {
        $bundles = ProductBundle::with(['items.variant.product', 'items.variant.size', 'items.variant.color'])
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $bundles,
            'message' => 'Product bundles retrieved successfully',
        ]);
    }

    /**
     * Get single bundle details
     */
    public function show(int $id): JsonResponse
    {
        $bundle = ProductBundle::with(['items.variant.product', 'items.variant.size', 'items.variant.color'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $bundle,
            'message' => 'Product bundle details retrieved successfully',
        ]);
    }

    /**
     * Create a new product bundle (Manager / Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bundle_name'          => 'required|string|max:150',
            'sku'                  => 'required|string|max:50|unique:product_bundles,sku',
            'barcode'              => 'nullable|string|max:50|unique:product_bundles,barcode',
            'bundle_price'         => 'required|numeric|min:0',
            'original_total_price' => 'nullable|numeric|min:0',
            'description'          => 'nullable|string',
            'image_url'            => 'nullable|url|max:500',
            'is_active'            => 'nullable|boolean',
            'items'                => 'required|array|min:1',
            'items.*.variant_id'   => 'required|exists:product_variants,variant_id',
            'items.*.quantity'     => 'required|integer|min:1',
        ]);

        $bundle = DB::transaction(function () use ($validated) {
            $bundle = ProductBundle::create([
                'bundle_name'          => $validated['bundle_name'],
                'sku'                  => $validated['sku'],
                'barcode'              => $validated['barcode'] ?? null,
                'bundle_price'         => $validated['bundle_price'],
                'original_total_price' => $validated['original_total_price'] ?? null,
                'description'          => $validated['description'] ?? null,
                'image_url'            => $validated['image_url'] ?? null,
                'is_active'            => $validated['is_active'] ?? true,
            ]);

            foreach ($validated['items'] as $item) {
                BundleItem::create([
                    'bundle_id'  => $bundle->bundle_id,
                    'variant_id' => $item['variant_id'],
                    'quantity'   => $item['quantity'],
                ]);
            }

            return $bundle->load(['items.variant.product']);
        });

        return response()->json([
            'success' => true,
            'data'    => $bundle,
            'message' => 'Product bundle created successfully',
        ], 201);
    }

    /**
     * Delete bundle
     */
    public function destroy(int $id): JsonResponse
    {
        $bundle = ProductBundle::findOrFail($id);
        $bundle->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product bundle deleted successfully',
        ]);
    }
}
