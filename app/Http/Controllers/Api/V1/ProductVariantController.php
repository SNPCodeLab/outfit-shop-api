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
        $query = ProductVariant::with(['product', 'size', 'color', 'batches', 'images']);

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

        // Filter by Unit of Measure (PIECE, CAN, BOTTLE, CARTON_24, etc.)
        if ($uom = $request->input('unit_of_measure') ?? $request->input('uom')) {
            $query->where('unit_of_measure', strtoupper($uom));
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

        $variants = $query->orderBy('variant_id', 'desc')->get()->map(function ($variant) {
            $onHand = (int) $variant->quantity;
            $reserved = (int) \Illuminate\Support\Facades\DB::table('sale_details')
                ->join('sale_headers', 'sale_details.sale_id', '=', 'sale_headers.sale_id')
                ->where('sale_details.variant_id', $variant->variant_id)
                ->whereIn('sale_headers.status', ['PENDING', 'ESTIMATE', 'DRAFT'])
                ->sum('sale_details.quantity');
            $available = max(0, $onHand - $reserved);
            $incoming = (int) \Illuminate\Support\Facades\DB::table('purchase_details')
                ->join('purchase_headers', 'purchase_details.purchase_id', '=', 'purchase_headers.purchase_id')
                ->where('purchase_details.variant_id', $variant->variant_id)
                ->whereIn('purchase_headers.status', ['PENDING', 'ORDERED', 'SHIPPED'])
                ->sum('purchase_details.quantity');

            $variantArray = $variant->toArray();
            $variantArray['quantity_overview'] = [
                'on_hand'   => $onHand,
                'reserved'  => $reserved,
                'available' => $available,
                'incoming'  => $incoming,
            ];
            $variantArray['valuation'] = [
                'cost_price'      => (float) $variant->cost_price,
                'selling_price'   => (float) $variant->sale_price,
                'purchased_value' => round($onHand * (float) $variant->cost_price, 2),
                'resale_value'    => round($onHand * (float) $variant->sale_price, 2),
                'margin_percent'  => $variant->sale_price > 0 ? round((((float)$variant->sale_price - (float)$variant->cost_price) / (float)$variant->sale_price) * 100, 2) : 0,
            ];
            return $variantArray;
        });

        return $this->successResponse($variants, 'Product variants retrieved');
    }

    public function lowStock(): JsonResponse
    {
        $lowStockVariants = ProductVariant::with(['product', 'size', 'color'])
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->get()
            ->map(function ($variant) {
                $onHand = (int) $variant->quantity;
                $variantArray = $variant->toArray();
                $variantArray['quantity_overview'] = [
                    'on_hand'   => $onHand,
                    'available' => $onHand,
                    'status'    => $onHand <= 0 ? 'OUT_OF_STOCK' : 'LOW_STOCK',
                ];
                return $variantArray;
            });

        return $this->successResponse($lowStockVariants, 'Low stock items retrieved');
    }

    public function lookupBarcode(string $barcode): JsonResponse
    {
        $variant = ProductVariant::with(['product', 'size', 'color', 'batches'])
            ->where('barcode', $barcode)
            ->orWhere('sku', $barcode)
            ->firstOrFail();

        $onHand = (int) $variant->quantity;
        $reserved = (int) \Illuminate\Support\Facades\DB::table('sale_details')
            ->join('sale_headers', 'sale_details.sale_id', '=', 'sale_headers.sale_id')
            ->where('sale_details.variant_id', $variant->variant_id)
            ->whereIn('sale_headers.status', ['PENDING', 'ESTIMATE', 'DRAFT'])
            ->sum('sale_details.quantity');
        $available = max(0, $onHand - $reserved);

        $variantArray = $variant->toArray();
        $variantArray['quantity_overview'] = [
            'on_hand'   => $onHand,
            'reserved'  => $reserved,
            'available' => $available,
        ];

        return $this->successResponse($variantArray, 'Variant lookup successful');
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

        return $this->createdResponse($variant->load(['product', 'size', 'color']), 'Product variant created successfully', '/api/v1/variants/' . $variant->variant_id);
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
