<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

/**
 * Event-driven cache invalidation: every write path (controller, seeder,
 * tinker, job) clears the product read caches - not only the controllers
 * that remember to call Cache::forget manually.
 */
class ProductObserver
{
    public function saved(Product $product): void
    {
        $this->flushProductCaches($product->product_id);
    }

    public function deleted(Product $product): void
    {
        $this->flushProductCaches($product->product_id);
    }

    public function restored(Product $product): void
    {
        $this->flushProductCaches($product->product_id);
    }

    private function flushProductCaches(int|string $productId): void
    {
        Cache::forget("product:{$productId}");
        Cache::forget("product_matrix:{$productId}");
        Cache::forget("product_colorways:{$productId}");
    }
}
