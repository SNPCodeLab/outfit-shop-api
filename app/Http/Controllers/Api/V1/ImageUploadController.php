<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\AuditLogService;
use App\Services\CloudinaryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageUploadController extends BaseApiController
{
    protected CloudinaryService $cloudinary;

    public function __construct(CloudinaryService $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    /**
     * Upload an image to Cloudinary (Multipart file or Remote URL).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'image'     => 'required_without:image_url|file|mimes:jpeg,png,jpg,webp,gif,svg|max:10240',
            'image_url' => 'required_without:image|nullable|url',
            'folder'    => 'nullable|string|max:100',
        ]);

        try {
            $folder = $request->input('folder', 'khmeriel/products');
            $file = $request->hasFile('image') ? $request->file('image') : $request->input('image_url');

            $uploadResult = $this->cloudinary->upload($file, $folder);

            if ($request->user()) {
                AuditLogService::log(
                    action: 'UPLOAD_IMAGE',
                    entity: 'CloudinaryAsset',
                    entityId: 0,
                    newValues: [
                        'public_id' => $uploadResult['public_id'],
                        'url'       => $uploadResult['secure_url'],
                    ],
                    userId: $request->user()->employee_id ?? $request->user()->id ?? null
                );
            }

            return $this->successResponse($uploadResult, 'Image uploaded successfully to Cloudinary', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete an image from Cloudinary by public ID or URL.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'public_id' => 'required_without:image_url|string',
            'image_url' => 'required_without:public_id|string',
        ]);

        try {
            $target = $request->input('public_id') ?? $request->input('image_url');
            $deleted = $this->cloudinary->delete($target);

            if (!$deleted) {
                return $this->errorResponse('Image could not be deleted from Cloudinary or was not found.', 404);
            }

            if ($request->user()) {
                AuditLogService::log(
                    action: 'DELETE_IMAGE',
                    entity: 'CloudinaryAsset',
                    entityId: 0,
                    oldValues: ['target' => $target],
                    userId: $request->user()->employee_id ?? $request->user()->id ?? null
                );
            }

            return $this->successResponse(null, 'Image deleted successfully from Cloudinary');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Upload and directly attach an image to a Product.
     *
     * @param Request $request
     * @param int $productId
     * @return JsonResponse
     */
    public function uploadForProduct(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'image'     => 'required_without:image_url|file|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'image_url' => 'required_without:image|nullable|url',
        ]);

        try {
            $file = $request->hasFile('image') ? $request->file('image') : $request->input('image_url');
            $uploadResult = $this->cloudinary->upload($file, 'khmeriel/products');

            // Delete old Cloudinary asset if exists
            if ($product->image_public_id) {
                $this->cloudinary->delete($product->image_public_id);
            }

            $oldValues = $product->toArray();
            $product->update([
                'image_url'       => $uploadResult['secure_url'],
                'image_public_id' => $uploadResult['public_id'],
            ]);

            AuditLogService::log(
                action: 'UPDATE_PRODUCT_IMAGE',
                entity: 'Product',
                entityId: $product->product_id,
                oldValues: $oldValues,
                newValues: $product->toArray(),
                userId: $request->user()->employee_id ?? $request->user()->id ?? null
            );

            return $this->successResponse($product, 'Product image updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Upload and directly attach an image to a Product Variant (colorway / specific SKU).
     *
     * @param Request $request
     * @param int $variantId
     * @return JsonResponse
     */
    public function uploadForVariant(Request $request, int $variantId): JsonResponse
    {
        $variant = ProductVariant::findOrFail($variantId);

        $request->validate([
            'image'     => 'required_without:image_url|file|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'image_url' => 'required_without:image|nullable|url',
        ]);

        try {
            $file = $request->hasFile('image') ? $request->file('image') : $request->input('image_url');
            $uploadResult = $this->cloudinary->upload($file, 'khmeriel/variants');

            // Delete old Cloudinary asset if exists
            if ($variant->image_public_id) {
                $this->cloudinary->delete($variant->image_public_id);
            }

            $oldValues = $variant->toArray();
            $variant->update([
                'image_url'       => $uploadResult['secure_url'],
                'image_public_id' => $uploadResult['public_id'],
            ]);

            AuditLogService::log(
                action: 'UPDATE_VARIANT_IMAGE',
                entity: 'ProductVariant',
                entityId: $variant->variant_id,
                oldValues: $oldValues,
                newValues: $variant->toArray(),
                userId: $request->user()->employee_id ?? $request->user()->id ?? null
            );

            return $this->successResponse($variant->load(['product', 'size', 'color']), 'Variant image updated successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Batch upload a list of images (URLs or Files) and optionally assign to Products/Variants.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadBatch(Request $request): JsonResponse
    {
        $request->validate([
            'items'             => 'required_without:images|array|min:1',
            'items.*.url'       => 'required_with:items|url',
            'items.*.product_id'=> 'nullable|exists:products,product_id',
            'items.*.variant_id'=> 'nullable|exists:product_variants,variant_id',
            'items.*.folder'    => 'nullable|string|max:100',
            'images'            => 'required_without:items|array|min:1',
            'images.*'          => 'file|mimes:jpeg,png,jpg,webp,gif,svg|max:10240',
            'folder'            => 'nullable|string|max:100',
        ]);

        $results = [];
        $folder = $request->input('folder', 'khmeriel/products');

        // Case 1: Batch of Remote URLs
        if ($request->has('items')) {
            foreach ($request->input('items') as $index => $item) {
                try {
                    $itemFolder = $item['folder'] ?? $folder;
                    $upload = $this->cloudinary->upload($item['url'], $itemFolder);

                    // Optionally link to Product
                    if (!empty($item['product_id'])) {
                        $product = Product::find($item['product_id']);
                        if ($product) {
                            $product->update([
                                'image_url'       => $upload['secure_url'],
                                'image_public_id' => $upload['public_id'],
                            ]);
                            $upload['attached_to_product_id'] = $item['product_id'];
                        }
                    }

                    // Optionally link to Variant
                    if (!empty($item['variant_id'])) {
                        $variant = ProductVariant::find($item['variant_id']);
                        if ($variant) {
                            $variant->update([
                                'image_url'       => $upload['secure_url'],
                                'image_public_id' => $upload['public_id'],
                            ]);
                            $upload['attached_to_variant_id'] = $item['variant_id'];
                        }
                    }

                    $results[] = array_merge(['status' => 'success', 'original_url' => $item['url']], $upload);
                } catch (Exception $e) {
                    $results[] = [
                        'status'       => 'error',
                        'original_url' => $item['url'] ?? null,
                        'message'      => $e->getMessage(),
                    ];
                }
            }
        }

        // Case 2: Batch of Uploaded Files
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                try {
                    $upload = $this->cloudinary->upload($file, $folder);
                    $results[] = array_merge(['status' => 'success', 'file_name' => $file->getClientOriginalName()], $upload);
                } catch (Exception $e) {
                    $results[] = [
                        'status'    => 'error',
                        'file_name' => $file->getClientOriginalName(),
                        'message'   => $e->getMessage(),
                    ];
                }
            }
        }

        return $this->successResponse($results, 'Batch upload processed', 200);
    }

    /**
     * Get list of all images currently attached across products and variants.
     *
     * @return JsonResponse
     */
    public function gallery(): JsonResponse
    {
        $productImages = Product::whereNotNull('image_url')
            ->select('product_id', 'product_name', 'brand', 'image_url', 'image_public_id', 'updated_at')
            ->get()
            ->map(fn ($p) => [
                'type'       => 'product',
                'id'         => $p->product_id,
                'name'       => $p->product_name,
                'brand'      => $p->brand,
                'image_url'  => $p->image_url,
                'public_id'  => $p->image_public_id,
                'updated_at' => $p->updated_at,
            ]);

        $variantImages = ProductVariant::whereNotNull('image_url')
            ->with(['product:product_id,product_name', 'size:size_id,size_name', 'color:color_id,color_name,hex_code'])
            ->select('variant_id', 'product_id', 'size_id', 'color_id', 'sku', 'image_url', 'image_public_id', 'updated_at')
            ->get()
            ->map(fn ($v) => [
                'type'         => 'variant',
                'id'           => $v->variant_id,
                'sku'          => $v->sku,
                'product_name' => $v->product->product_name ?? null,
                'size'         => $v->size->size_name ?? null,
                'color'        => $v->color->color_name ?? null,
                'image_url'    => $v->image_url,
                'public_id'    => $v->image_public_id,
                'updated_at'   => $v->updated_at,
            ]);

        return $this->successResponse([
            'total_product_images' => $productImages->count(),
            'total_variant_images' => $variantImages->count(),
            'product_images'       => $productImages,
            'variant_images'       => $variantImages,
        ], 'Media gallery retrieved');
    }
}
