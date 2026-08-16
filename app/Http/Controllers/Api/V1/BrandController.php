<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    /**
     * List all brands (Public)
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

        return response()->json([
            'success' => true,
            'data'    => $brands,
            'message' => 'Brands retrieved successfully',
        ]);
    }

    /**
     * Get single brand details with products
     */
    public function show(int $id): JsonResponse
    {
        $brand = Brand::with(['products.category', 'products.variants'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $brand,
            'message' => 'Brand details retrieved successfully',
        ]);
    }

    /**
     * Create a new brand (Manager / Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'brand_name'         => 'required|string|max:100|unique:brands,brand_name',
            'logo_url'           => 'nullable|url|max:500',
            'banner_url'         => 'nullable|url|max:500',
            'country_of_origin'  => 'nullable|string|max:50',
            'description'        => 'nullable|string',
            'website_url'        => 'nullable|url|max:255',
            'is_featured'        => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['brand_name']);

        $brand = Brand::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $brand,
            'message' => 'Brand created successfully',
        ], 201);
    }

    /**
     * Update brand
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);

        $validated = $request->validate([
            'brand_name'         => 'sometimes|required|string|max:100|unique:brands,brand_name,' . $id . ',brand_id',
            'logo_url'           => 'nullable|url|max:500',
            'banner_url'         => 'nullable|url|max:500',
            'country_of_origin'  => 'nullable|string|max:50',
            'description'        => 'nullable|string',
            'website_url'        => 'nullable|url|max:255',
            'is_featured'        => 'nullable|boolean',
        ]);

        if (isset($validated['brand_name'])) {
            $validated['slug'] = Str::slug($validated['brand_name']);
        }

        $brand->update($validated);

        return response()->json([
            'success' => true,
            'data'    => $brand,
            'message' => 'Brand updated successfully',
        ]);
    }

    /**
     * Delete brand
     */
    public function destroy(int $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully',
        ]);
    }
}
