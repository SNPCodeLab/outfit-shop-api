<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\SaleHeader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DigitalAssetController extends BaseApiController
{
    /**
     * Get digital publication details and a time-limited download link.
     *
     * Authenticated endpoint: employees may fetch any asset (store
     * operations); customer accounts must have a completed purchase of
     * this product before a download link is issued.
     */
    public function download(Request $request, int $productId): JsonResponse
    {
        $product = Product::with(['category', 'variants'])->findOrFail($productId);

        if ($product->product_type !== 'DIGITAL_DOWNLOAD') {
            return $this->errorResponse(
                'This product is a physical item, not a digital publication.',
                422,
                'NOT_A_DIGITAL_PRODUCT'
            );
        }

        if (! $this->isEntitled($request->user(), $product)) {
            return $this->forbiddenResponse(
                'Purchase verification failed. Only customers who have purchased this digital publication can download it.'
            );
        }

        $variant = $product->variants->first();
        $fileUrl = $variant?->download_file_url ?? $product->image_url;

        return $this->successResponse([
            'product_id' => $product->product_id,
            'title' => $product->product_name,
            'author' => $product->author_artist ?? 'KhmeRiel Press',
            'isbn' => $product->isbn_code ?? null,
            'file_format' => 'PDF',
            'download_url' => $fileUrl,
            'cover_image_url' => $product->image_url,
            'download_expires' => now()->addHours(24)->toISOString(),
        ], 'Digital publication ready for download');
    }

    /**
     * Employees (store staff) are always entitled; customer accounts must
     * match a Customer record by email with a COMPLETED sale containing
     * one of this product's variants.
     */
    private function isEntitled(mixed $account, Product $product): bool
    {
        if ($account instanceof Employee) {
            return true;
        }

        if (! $account) {
            return false;
        }

        $customer = Customer::where('email', $account->email ?? '')->first();

        if (! $customer) {
            return false;
        }

        $variantIds = $product->variants->pluck('variant_id');

        return SaleHeader::where('customer_id', $customer->customer_id)
            ->where('status', 'COMPLETED')
            ->whereHas('details', fn ($query) => $query->whereIn('variant_id', $variantIds))
            ->exists();
    }
}
