<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Product;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $products = Product::with(['category', 'variants.size', 'variants.color'])->get();
        return $this->successResponse($products, 'Products catalog retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id'  => 'required|exists:categories,category_id',
            'product_name' => 'required|string|max:150',
            'brand'        => 'nullable|string',
            'description'  => 'nullable|string',
            'status'       => 'nullable|string|in:ACTIVE,INACTIVE',
        ]);

        $product = Product::create($validated);

        AuditLogService::log('CREATE', 'Product', $product->product_id, null, $product->toArray());

        return $this->successResponse($product->load('category'), 'Product created', 201);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with(['category', 'variants.size', 'variants.color'])->findOrFail($id);
        return $this->successResponse($product, 'Product details');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $old = $product->toArray();

        $validated = $request->validate([
            'category_id'  => 'required|exists:categories,category_id',
            'product_name' => 'required|string|max:150',
            'brand'        => 'nullable|string',
            'description'  => 'nullable|string',
            'status'       => 'nullable|string|in:ACTIVE,INACTIVE',
        ]);

        $product->update($validated);

        AuditLogService::log('UPDATE', 'Product', $id, $old, $product->toArray());

        return $this->successResponse($product->load('category'), 'Product updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $old = $product->toArray();
        $product->delete();

        AuditLogService::log('DELETE', 'Product', $id, $old, null);

        return $this->successResponse(null, 'Product soft deleted');
    }
}
