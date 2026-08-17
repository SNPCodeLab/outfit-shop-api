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
        // ── 1. Dynamic Relationship Inclusion (?include=category,variants,images) ─
        $allowedIncludes = ['category', 'variants.size', 'variants.color', 'images', 'primaryImage'];
        $requestedIncludes = $request->input('include');
        
        if ($requestedIncludes) {
            $includes = array_filter(explode(',', $requestedIncludes));
            $withRelations = [];
            foreach ($includes as $inc) {
                $inc = trim($inc);
                if ($inc === 'variants') $withRelations[] = 'variants.size';
                if ($inc === 'variants') $withRelations[] = 'variants.color';
                if ($inc === 'category') $withRelations[] = 'category';
                if ($inc === 'images')   $withRelations[] = 'images';
            }
            $query = Product::with(array_unique($withRelations ?: $allowedIncludes));
        } else {
            $query = Product::with($allowedIncludes);
        }

        // ── 2. Advanced Filtering (?filter[brand]=Nike&filter[price_min]=10...) ──
        $filter = $request->input('filter', []);

        // Search query
        $search = $request->input('q') ?? $request->input('search') ?? ($filter['search'] ?? null);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'ILIKE', "%{$search}%")
                  ->orWhere('brand', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%")
                  ->orWhere('author_artist', 'ILIKE', "%{$search}%")
                  ->orWhere('isbn_code', 'ILIKE', "%{$search}%");
            });
        }

        // Brand filter
        if ($brand = $request->input('brand') ?? ($filter['brand'] ?? null)) {
            $query->where('brand', 'ILIKE', "%{$brand}%");
        }

        // Price range filter on variants
        $priceMin = $request->input('price_min') ?? ($filter['price_min'] ?? null);
        $priceMax = $request->input('price_max') ?? ($filter['price_max'] ?? null);
        if ($priceMin !== null || $priceMax !== null) {
            $query->whereHas('variants', function ($v) use ($priceMin, $priceMax) {
                if ($priceMin !== null) $v->where('sale_price', '>=', (float) $priceMin);
                if ($priceMax !== null) $v->where('sale_price', '<=', (float) $priceMax);
            });
        }

        // Stock availability filter
        $hasStock = $request->input('has_stock') ?? ($filter['has_stock'] ?? null);
        if ($hasStock !== null) {
            $inStock = filter_var($hasStock, FILTER_VALIDATE_BOOLEAN);
            if ($inStock) {
                $query->whereHas('variants', fn($v) => $v->where('quantity', '>', 0));
            } else {
                $query->whereDoesntHave('variants', fn($v) => $v->where('quantity', '>', 0));
            }
        }

        // Active status filter
        $isActive = $request->input('is_active') ?? ($filter['is_active'] ?? null);
        if ($isActive !== null) {
            $query->where('status', filter_var($isActive, FILTER_VALIDATE_BOOLEAN) ? 'ACTIVE' : 'INACTIVE');
        } elseif ($status = $request->input('status') ?? ($filter['status'] ?? null)) {
            $query->where('status', strtoupper($status));
        }

        // Color filter (supports comma-separated list e.g. Black,White,Red)
        if ($colors = $request->input('color') ?? ($filter['color'] ?? null)) {
            $colorList = is_array($colors) ? $colors : explode(',', $colors);
            $query->whereHas('variants.color', function ($c) use ($colorList) {
                $c->whereIn('color_name', array_map('trim', $colorList));
            });
        }

        // Size filter (supports comma-separated list e.g. S,M,L,XL)
        if ($sizes = $request->input('size') ?? ($filter['size'] ?? null)) {
            $sizeList = is_array($sizes) ? $sizes : explode(',', $sizes);
            $query->whereHas('variants.size', function ($s) use ($sizeList) {
                $s->whereIn('size_name', array_map('trim', $sizeList))
                  ->orWhereIn('size_code', array_map('trim', $sizeList));
            });
        }

        // Category & Department filter
        if ($categoryId = $request->input('category_id') ?? ($filter['category_id'] ?? null)) {
            $query->where('category_id', $categoryId);
        }
        if ($dept = $request->input('department_type') ?? ($filter['department_type'] ?? null)) {
            $query->whereHas('category', function ($c) use ($dept) {
                $c->where('department_type', strtoupper($dept));
            });
        }

        // Gender & Season filter
        if ($gender = $request->input('gender') ?? ($filter['gender'] ?? null)) {
            $query->where('gender', strtoupper($gender));
        }
        if ($season = $request->input('season') ?? $request->input('collection') ?? ($filter['season'] ?? null)) {
            $query->where('season_collection', 'ILIKE', "%{$season}%");
        }

        // ── 3. Multi-Column Sorting (?sort=-created_at,product_name) ─────────
        $sortParam = $request->input('sort') ?? $request->input('sort_by');
        if ($sortParam) {
            $sortFields = explode(',', $sortParam);
            foreach ($sortFields as $field) {
                $field = trim($field);
                $direction = 'asc';
                if (str_starts_with($field, '-')) {
                    $direction = 'desc';
                    $field = substr($field, 1);
                }
                if (in_array($field, ['product_name', 'created_at', 'brand', 'season_collection', 'product_id'])) {
                    $query->orderBy($field, $direction);
                }
            }
        } else {
            $query->orderBy('product_id', 'desc');
        }

        // ── 4. Pagination / Limit ─────────────────────────────────────────────
        $perPage = (int) $request->input('per_page', 0);
        $products = $perPage > 0 ? $query->paginate($perPage) : $query->get();

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
        // ── High-Speed Caching Layer (Cache hot products for 1 hour) ──────────
        $product = \Illuminate\Support\Facades\Cache::remember("product:{$id}", 3600, function () use ($id) {
            return Product::with(['category', 'variants.size', 'variants.color', 'images', 'primaryImage'])->findOrFail($id);
        });

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

        // Cache Invalidation
        \Illuminate\Support\Facades\Cache::forget("product:{$id}");
        \Illuminate\Support\Facades\Cache::forget("product_matrix:{$id}");

        AuditLogService::log('UPDATE', 'Product', $id, $old, $product->toArray());

        return $this->successResponse($product->load('category'), 'Product updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $old = $product->toArray();
        $product->delete();

        // Cache Invalidation
        \Illuminate\Support\Facades\Cache::forget("product:{$id}");
        \Illuminate\Support\Facades\Cache::forget("product_matrix:{$id}");

        AuditLogService::log('DELETE', 'Product', $id, $old, null);

        return $this->successResponse(null, 'Product soft deleted');
    }
}
