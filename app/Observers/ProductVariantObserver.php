<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\ProductVariant;
use Illuminate\Support\Facades\Cache;

/**
 * Variant changes (price, stock, new colorway) invalidate the parent
 * product's cached show/matrix/colorways payloads.
 */
class ProductVariantObserver
{
    public function saved(ProductVariant $variant): void
    {
        $this->flushParentCaches($variant->product_id);
    }

    public function deleted(ProductVariant $variant): void
    {
        $this->flushParentCaches($variant->product_id);
    }

    private function flushParentCaches(int|string $productId): void
    {
        Cache::forget("product:{$productId}");
        Cache::forget("product_matrix:{$productId}");
        Cache::forget("product_colorways:{$productId}");
    }
}
