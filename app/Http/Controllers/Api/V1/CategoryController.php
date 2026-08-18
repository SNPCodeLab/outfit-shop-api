<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Category;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends BaseApiController
{
    public function index(): JsonResponse
    {
        // ── High-Speed Caching Layer (Categories rarely change) ───────────────
        $categories = Cache::rememberForever('categories:all', function () {
            return Category::all();
        });

        return $this->successResponse($categories, 'Categories retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_name' => 'required|string|unique:categories,category_name',
            'description' => 'nullable|string',
        ]);

        $category = Category::create($validated);

        // Cache Invalidation
        Cache::forget('categories:all');

        AuditLogService::log('CREATE', 'Category', $category->category_id, null, $category->toArray());

        return $this->createdResponse($category, 'Category created successfully', '/api/v1/categories/'.$category->category_id);
    }

    public function show(int $id): JsonResponse
    {
        $category = Cache::remember("category:{$id}", 3600, function () use ($id) {
            return Category::with('products.variants')->findOrFail($id);
        });

        return $this->successResponse($category, 'Category details retrieved');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $oldValues = $category->toArray();

        $validated = $request->validate([
            'category_name' => 'required|string|unique:categories,category_name,'.$id.',category_id',
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        // Cache Invalidation
        Cache::forget('categories:all');
        Cache::forget("category:{$id}");

        AuditLogService::log('UPDATE', 'Category', $category->category_id, $oldValues, $category->toArray());

        return $this->successResponse($category, 'Category updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $oldValues = $category->toArray();
        $category->delete();

        // Cache Invalidation
        Cache::forget('categories:all');
        Cache::forget("category:{$id}");

        AuditLogService::log('DELETE', 'Category', $id, $oldValues, null);

        return $this->successResponse(null, 'Category deleted');
    }
}
