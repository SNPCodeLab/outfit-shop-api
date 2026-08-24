<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\BundleItem;
use App\Models\ProductBundle;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductBundleController extends BaseApiController
{
    /**
     * List all active product bundles and combo packs.
     * Public - no authentication required.
     */
    public function index(): JsonResponse
    {
        $query = ProductBundle::with([
            'items.variant.product',
            'items.variant.size',
            'items.variant.color',
        ]);

        if (DB::getDriverName() === 'sqlite') {
            $query->where(function ($q) {
                $q->where('is_active', true)
                    ->orWhere('is_active', 1)
                    ->orWhere('is_active', 'true');
            });
        } else {
            $query->whereRaw('is_active is true');
        }

        $bundles = $query->get();

        return $this->successResponse($bundles, 'Product bundles retrieved successfully');
    }

    /**
     * Get a single bundle with all component details.
     * Public - no authentication required.
     */
    public function show(int $id): JsonResponse
    {
        $bundle = ProductBundle::with([
            'items.variant.product',
            'items.variant.size',
            'items.variant.color',
        ])->findOrFail($id);

        return $this->successResponse($bundle, 'Product bundle details retrieved successfully');
    }

    /**
     * Create a new product bundle.
     * Restricted to MANAGER or ADMIN.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bundle_name' => 'required|string|max:150',
            'sku' => 'required|string|max:50|unique:product_bundles,sku',
            'barcode' => 'nullable|string|max:50|unique:product_bundles,barcode',
            'bundle_price' => 'required|numeric|min:0',
            'original_total_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url|max:2048',
            'is_active' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,variant_id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $bundle = DB::transaction(function () use ($validated) {
            $bundle = ProductBundle::create([
                'bundle_name' => $validated['bundle_name'],
                'sku' => $validated['sku'],
                'barcode' => $validated['barcode'] ?? null,
                'bundle_price' => $validated['bundle_price'],
                'original_total_price' => $validated['original_total_price'] ?? null,
                'description' => $validated['description'] ?? null,
                'image_url' => $validated['image_url'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            foreach ($validated['items'] as $item) {
                BundleItem::create([
                    'bundle_id' => $bundle->bundle_id,
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            AuditLogService::log('CREATE', 'ProductBundle', $bundle->bundle_id, null, [
                'bundle_name' => $bundle->bundle_name,
                'sku' => $bundle->sku,
                'items_count' => count($validated['items']),
            ]);

            return $bundle->load(['items.variant.product', 'items.variant.size', 'items.variant.color']);
        });

        return $this->createdResponse($bundle, 'Product bundle created successfully', '/api/v1/bundles/'.$bundle->bundle_id);
    }

    /**
     * Update a product bundle.
     * Restricted to MANAGER or ADMIN.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $bundle = ProductBundle::findOrFail($id);
        $old = $bundle->load('items')->toArray();

        $validated = $request->validate([
            'bundle_name' => 'sometimes|required|string|max:150',
            'sku' => 'sometimes|required|string|max:50|unique:product_bundles,sku,'.$id.',bundle_id',
            'barcode' => 'nullable|string|max:50|unique:product_bundles,barcode,'.$id.',bundle_id',
            'bundle_price' => 'sometimes|required|numeric|min:0',
            'original_total_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url|max:2048',
            'is_active' => 'nullable|boolean',
            'items' => 'nullable|array',
            'items.*.variant_id' => 'required_with:items|exists:product_variants,variant_id',
            'items.*.quantity' => 'required_with:items|integer|min:1',
        ]);

        $updatedBundle = DB::transaction(function () use ($bundle, $validated) {
            $bundleFields = collect($validated)->except('items')->toArray();
            if (! empty($bundleFields)) {
                $bundle->update($bundleFields);
            }

            if (array_key_exists('items', $validated) && is_array($validated['items'])) {
                BundleItem::where('bundle_id', $bundle->bundle_id)->delete();
                foreach ($validated['items'] as $item) {
                    BundleItem::create([
                        'bundle_id' => $bundle->bundle_id,
                        'variant_id' => $item['variant_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }
            }

            return $bundle->fresh([
                'items.variant.product',
                'items.variant.size',
                'items.variant.color',
            ]);
        });

        AuditLogService::log('UPDATE', 'ProductBundle', $id, $old, $updatedBundle->toArray());

        return $this->successResponse($updatedBundle, 'Product bundle updated successfully');
    }

    /**
     * Delete a product bundle.
     * Restricted to MANAGER or ADMIN.
     */
    public function destroy(int $id): JsonResponse
    {
        $bundle = ProductBundle::findOrFail($id);

        AuditLogService::log('DELETE', 'ProductBundle', $id, $bundle->toArray(), null);

        $bundle->delete();

        return $this->deletedResponse('Product bundle deleted successfully');
    }
}
