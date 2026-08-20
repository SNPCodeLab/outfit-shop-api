<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Brand;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends BaseApiController
{
    /**
     * List all brands.
     * Supports optional ?featured=true and ?search= filters.
     * Public - no authentication required.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Brand::query()->withCount('products');

        if ($request->has('featured')) {
            $query->where('is_featured', filter_var($request->featured, FILTER_VALIDATE_BOOLEAN));
        }

        if ($search = $request->input('search')) {
            $query->where('brand_name', 'ILIKE', "%{$search}%");
        }

        $brands = $query->orderBy('brand_name', 'asc')->get();

        return $this->successResponse($brands, 'Brands retrieved successfully');
    }

    /**
     * Get single brand with associated products.
     * Public - no authentication required.
     */
    public function show(int $id): JsonResponse
    {
        $brand = Brand::with(['products.category', 'products.variants'])->findOrFail($id);

        return $this->successResponse($brand, 'Brand details retrieved successfully');
    }

    /**
     * Create a new brand.
     * Restricted to MANAGER or ADMIN.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'brand_name' => 'required|string|max:100|unique:brands,brand_name',
            'slug' => 'nullable|string|max:120|unique:brands,slug',
            'logo_url' => 'nullable|url|max:500',
            'banner_url' => 'nullable|url|max:500',
            'country_of_origin' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'website_url' => 'nullable|url|max:255',
            'is_featured' => 'nullable|boolean',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['brand_name']);
        }

        $brand = Brand::create($validated);

        AuditLogService::log('CREATE', 'Brand', $brand->brand_id, null, $brand->toArray());

        return $this->createdResponse(
            $brand,
            'Brand created successfully',
            '/api/v1/brands/'.$brand->brand_id
        );
    }

    /**
     * Update a brand.
     * Restricted to MANAGER or ADMIN.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);
        $old = $brand->toArray();

        $validated = $request->validate([
            'brand_name' => 'sometimes|required|string|max:100|unique:brands,brand_name,'.$id.',brand_id',
            'logo_url' => 'nullable|url|max:500',
            'banner_url' => 'nullable|url|max:500',
            'country_of_origin' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'website_url' => 'nullable|url|max:255',
            'is_featured' => 'nullable|boolean',
        ]);

        if (isset($validated['brand_name'])) {
            $validated['slug'] = Str::slug($validated['brand_name']);
        }

        $brand->update($validated);

        AuditLogService::log('UPDATE', 'Brand', $id, $old, $brand->toArray());

        return $this->successResponse($brand, 'Brand updated successfully');
    }

    /**
     * Delete a brand.
     * Restricted to MANAGER or ADMIN.
     */
    public function destroy(int $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);
        $old = $brand->toArray();
        $brand->delete();

        AuditLogService::log('DELETE', 'Brand', $id, $old, null);

        return $this->deletedResponse('Brand deleted successfully');
    }
}
