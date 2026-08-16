<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ProductVariant;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductVariantController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductVariant::with(['product', 'size', 'color']);

        // Fast search by SKU, Barcode, or Product Name
        if ($search = $request->input('q') ?? $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'ILIKE', "%{$search}%")
                  ->orWhere('barcode', 'ILIKE', "%{$search}%")
                  ->orWhereHas('product', function ($p) use ($search) {
                      $p->where('product_name', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // Filter by Product ID
        if ($productId = $request->input('product_id')) {
            $query->where('product_id', $productId);
        }

        // Filter by Size ID
        if ($sizeId = $request->input('size_id')) {
            $query->where('size_id', $sizeId);
        }

        // Filter by Color ID
        if ($colorId = $request->input('color_id')) {
            $query->where('color_id', $colorId);
        }

        $variants = $query->orderBy('variant_id', 'desc')->get();
        return $this->successResponse($variants, 'Product variants retrieved');
    }

    public function lowStock(): JsonResponse
    {
        $lowStockVariants = ProductVariant::with(['product', 'size', 'color'])
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->get();

        return $this->successResponse($lowStockVariants, 'Low stock items retrieved');
    }

    public function lookupBarcode(string $barcode): JsonResponse
    {
        $variant = ProductVariant::with(['product', 'size', 'color'])
            ->where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->firstOrFail();

        return $this->successResponse($variant, 'Variant lookup successful');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id'    => 'required|exists:products,product_id',
            'size_id'       => 'required|exists:clothing_sizes,size_id',
            'color_id'      => 'required|exists:colors,color_id',
            'sku'             => 'required|string|unique:product_variants,sku',
            'barcode'         => 'nullable|string|unique:product_variants,barcode',
            'image_url'       => 'nullable|string|max:500',
            'image_public_id' => 'nullable|string|max:255',
            'cost_price'      => 'required|numeric|min:0',
            'sale_price'      => 'required|numeric|min:0',
            'quantity'        => 'required|integer|min:0',
            'reorder_level'   => 'nullable|integer|min:0',
        ]);

        // Validate unique combination of product_id, size_id, color_id
        $exists = ProductVariant::where('product_id', $validated['product_id'])
            ->where('size_id', $validated['size_id'])
            ->where('color_id', $validated['color_id'])
            ->exists();

        if ($exists) {
            return $this->errorResponse('A product variant with this Size and Color combination already exists.', 422);
        }

        $variant = ProductVariant::create($validated);

        AuditLogService::log('CREATE', 'ProductVariant', $variant->variant_id, null, $variant->toArray());

        return $this->successResponse($variant->load(['product', 'size', 'color']), 'Product variant created', 201);
    }

    public function show(int $id): JsonResponse
    {
        $variant = ProductVariant::with(['product', 'size', 'color', 'stockMovements'])->findOrFail($id);
        return $this->successResponse($variant, 'Product variant details');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $variant = ProductVariant::findOrFail($id);
        $old = $variant->toArray();

        $validated = $request->validate([
            'product_id'      => 'required|exists:products,product_id',
            'size_id'         => 'required|exists:clothing_sizes,size_id',
            'color_id'        => 'required|exists:colors,color_id',
            'sku'             => 'required|string|unique:product_variants,sku,' . $id . ',variant_id',
            'barcode'         => 'nullable|string|unique:product_variants,barcode,' . $id . ',variant_id',
            'image_url'       => 'nullable|string|max:500',
            'image_public_id' => 'nullable|string|max:255',
            'cost_price'      => 'required|numeric|min:0',
            'sale_price'      => 'required|numeric|min:0',
            'quantity'        => 'required|integer|min:0',
            'reorder_level'   => 'nullable|integer|min:0',
        ]);

        $variant->update($validated);

        AuditLogService::log('UPDATE', 'ProductVariant', $id, $old, $variant->toArray());

        return $this->successResponse($variant->load(['product', 'size', 'color']), 'Product variant updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $variant = ProductVariant::findOrFail($id);
        $old = $variant->toArray();
        $variant->delete();

        AuditLogService::log('DELETE', 'ProductVariant', $id, $old, null);

        return $this->successResponse(null, 'Product variant soft deleted');
    }
}
