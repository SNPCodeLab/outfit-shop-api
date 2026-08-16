<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Product;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'variants.size', 'variants.color']);

        // Fast search query by name, brand, or description
        if ($search = $request->input('q') ?? $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'ILIKE', "%{$search}%")
                  ->orWhere('brand', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        // Filter by category ID
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', strtoupper($status));
        }

        // Fast sorting
        $sortField = in_array($request->input('sort_by'), ['product_name', 'created_at', 'brand']) 
            ? $request->input('sort_by') 
            : 'product_id';
        $sortOrder = strtolower($request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $products = $query->orderBy($sortField, $sortOrder)->get();

        return $this->successResponse($products, 'Products catalog retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id'  => 'required|exists:categories,category_id',
            'product_name' => 'required|string|max:150',
            'brand'           => 'nullable|string',
            'description'     => 'nullable|string',
            'image_url'       => 'nullable|string|max:500',
            'image_public_id' => 'nullable|string|max:255',
            'status'          => 'nullable|string|in:ACTIVE,INACTIVE',
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
            'category_id'     => 'required|exists:categories,category_id',
            'product_name'    => 'required|string|max:150',
            'brand'           => 'nullable|string',
            'description'     => 'nullable|string',
            'image_url'       => 'nullable|string|max:500',
            'image_public_id' => 'nullable|string|max:255',
            'status'          => 'nullable|string|in:ACTIVE,INACTIVE',
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
