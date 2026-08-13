<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Category;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends BaseApiController
{
    public function index(): JsonResponse
    {
        return $this->successResponse(Category::all(), 'Categories retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_name' => 'required|string|unique:categories,category_name',
            'description'   => 'nullable|string',
        ]);

        $category = Category::create($validated);

        AuditLogService::log('CREATE', 'Category', $category->category_id, null, $category->toArray());

        return $this->successResponse($category, 'Category created', 201);
    }

    public function show(int $id): JsonResponse
    {
        $category = Category::with('products.variants')->findOrFail($id);
        return $this->successResponse($category, 'Category details retrieved');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $oldValues = $category->toArray();

        $validated = $request->validate([
            'category_name' => 'required|string|unique:categories,category_name,' . $id . ',category_id',
            'description'   => 'nullable|string',
        ]);

        $category->update($validated);

        AuditLogService::log('UPDATE', 'Category', $category->category_id, $oldValues, $category->toArray());

        return $this->successResponse($category, 'Category updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $oldValues = $category->toArray();
        $category->delete();

        AuditLogService::log('DELETE', 'Category', $id, $oldValues, null);

        return $this->successResponse(null, 'Category deleted');
    }
}
