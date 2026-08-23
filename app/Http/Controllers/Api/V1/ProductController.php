<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Product\StoreProductRequest;
use App\Models\Product;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        // ── 1. Dynamic Relationship Inclusion (?include=category,variants,images) ─
        $allowedIncludes = ['category', 'variants.size', 'variants.color', 'images', 'primaryImage'];
        $requestedIncludes = $request->input('include');

        if ($requestedIncludes) {
            $includeMap = [
                'variants' => ['variants.size', 'variants.color'],
                'category' => ['category'],
                'images' => ['images'],
                'primaryImage' => ['primaryImage'],
            ];
            $includes = array_filter(explode(',', $requestedIncludes));
            $withRelations = [];
            foreach ($includes as $inc) {
                $inc = trim($inc);
                if (isset($includeMap[$inc])) {
                    $withRelations = array_merge($withRelations, $includeMap[$inc]);
                }
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
            $search = $this->escapeLike($search);
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
            $brand = $this->escapeLike((string) $brand);
            $query->where('brand', 'ILIKE', "%{$brand}%");
        }

        // Price range filter on variants
        $priceMin = $request->input('price_min') ?? ($filter['price_min'] ?? null);
        $priceMax = $request->input('price_max') ?? ($filter['price_max'] ?? null);
        if ($priceMin !== null || $priceMax !== null) {
            $query->whereHas('variants', function ($v) use ($priceMin, $priceMax) {
                if ($priceMin !== null) {
                    $v->where('sale_price', '>=', (float) $priceMin);
                }
                if ($priceMax !== null) {
                    $v->where('sale_price', '<=', (float) $priceMax);
                }
            });
        }

        // Stock availability filter
        $hasStock = $request->input('has_stock') ?? ($filter['has_stock'] ?? null);
        if ($hasStock !== null) {
            $inStock = filter_var($hasStock, FILTER_VALIDATE_BOOLEAN);
            if ($inStock) {
                $query->whereHas('variants', fn ($v) => $v->where('quantity', '>', 0));
            } else {
                $query->whereDoesntHave('variants', fn ($v) => $v->where('quantity', '>', 0));
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
            $query->withBrandPriority()->orderBy('product_id', 'desc');
        }

        // ── 4. Pagination (capped at 100 per page; the unpaginated
        // per_page=all / per_page=0 mode was removed as a DoS vector) ────────
        $products = $query->paginate($this->perPage($request));

        return $this->successResponse($products, 'Products catalog retrieved');
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $product = Product::create($validated);

        AuditLogService::log('CREATE', 'Product', $product->product_id, null, $product->toArray());

        return $this->createdResponse(
            $product->load('category'),
            'Product created successfully',
            '/api/v1/products/'.$product->product_id
        );
    }

    public function show(int $id): JsonResponse
    {
        // ── High-Speed Caching Layer (1h TTL, array payload so the cache
        //    store never grows with serialized Eloquent model weight) ────────
        $product = Cache::remember("product:{$id}", 3600, function () use ($id) {
            return Product::with(['category', 'variants.size', 'variants.color', 'images', 'primaryImage'])
                ->findOrFail($id)
                ->toArray();
        });

        return $this->successResponse($product, 'Product details');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $old = $product->toArray();

        $validated = $request->validate([
            'category_id' => 'sometimes|required|exists:categories,category_id',
            'product_name' => 'sometimes|required|string|max:150',
            'brand' => 'nullable|string',
            'brand_id' => 'nullable|exists:brands,brand_id',
            'description' => 'nullable|string',
            'image_url' => 'nullable|string|max:500',
            'image_public_id' => 'nullable|string|max:255',
            'featured_badge' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:ACTIVE,INACTIVE,DRAFT',
        ]);

        $product->update($validated);

        // Cache Invalidation
        Cache::forget("product:{$id}");
        Cache::forget("product_matrix:{$id}");

        AuditLogService::log('UPDATE', 'Product', $id, $old, $product->toArray());

        return $this->successResponse($product->load('category'), 'Product updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $old = $product->toArray();
        $product->delete();

        // Cache Invalidation
        Cache::forget("product:{$id}");
        Cache::forget("product_matrix:{$id}");

        AuditLogService::log('DELETE', 'Product', $id, $old, null);

        return $this->deletedResponse('Product deleted successfully');
    }
}
