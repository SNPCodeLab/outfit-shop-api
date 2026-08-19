<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CloudinarySyncService
{
    public function __construct(
        protected CloudinaryService $cloudinary
    ) {}

    /**
     * Synchronize brand images from Cloudinary to the local database.
     */
    public function syncAllBrands(): array
    {
        $brands = Brand::all();
        $stats = [
            'inserted' => 0,
            'skipped' => 0,
            'updated' => 0,
            'errors' => 0,
            'details' => [],
        ];

        foreach ($brands as $brand) {
            try {
                $brandStats = $this->syncBrandImages($brand);
                $stats['inserted'] += $brandStats['inserted'];
                $stats['skipped'] += $brandStats['skipped'];
                $stats['updated'] += $brandStats['updated'];
                $stats['details'][] = [
                    'brand' => $brand->brand_name,
                    'result' => $brandStats,
                ];
            } catch (Exception $e) {
                $stats['errors']++;
                Log::error("Failed to sync images for brand {$brand->brand_name}: " . $e->getMessage());
            }
        }

        return $stats;
    }

    /**
     * Sync images for a specific brand.
     */
    public function syncBrandImages(Brand $brand): array
    {
        $inserted = 0;
        $skipped = 0;
        $updated = 0;

        // Fetch resources from Cloudinary matching the brand slug folder or prefix
        // Convention: Folder 'khmeriel/products/{brand_slug}'
        $prefix = config('cloudinary.folder', 'khmeriel/products') . '/' . $brand->slug;
        $resources = $this->cloudinary->listResources($prefix);

        foreach ($resources as $resource) {
            $publicId = $resource['public_id'];
            $url = $resource['secure_url'] ?? $resource['url'];

            // Attempt to resolve product_id from naming convention or metadata
            // Basic convention: {brand_slug}_{product_id}_{suffix}
            $productId = $this->resolveProductIdFromPublicId($publicId, $brand);

            if (!$productId) {
                // If we can't map to a specific product, we might link to the brand logo or just skip
                continue;
            }

            // Condition 1, 2, 4: Deduplication Check
            $exists = ProductImage::where('image_url', $url)
                ->orWhere('image_public_id', $publicId)
                ->first();

            if ($exists) {
                // Condition 3: Update existing record if association matches
                if ($exists->brand_id === $brand->brand_id && $exists->product_id === $productId) {
                    $exists->touch(); // Record was seen, update timestamp
                    $updated++;
                    continue;
                }
                $skipped++;
                continue;
            }

            // Condition 7: Verification by brand_id and product_id combination
            // Handled by the check above, but for strictness:
            $sameAssoc = ProductImage::where('brand_id', $brand->brand_id)
                ->where('product_id', $productId)
                ->where('image_url', $url)
                ->exists();

            if ($sameAssoc) {
                $skipped++;
                continue;
            }

            // Insert new record
            ProductImage::create([
                'brand_id' => $brand->brand_id,
                'product_id' => $productId,
                'image_url' => $url,
                'image_public_id' => $publicId,
                'shot_type' => 'LOOK',
                'is_primary' => false,
            ]);
            $inserted++;
        }

        return [
            'inserted' => $inserted,
            'skipped' => $skipped,
            'updated' => $updated,
        ];
    }

    /**
     * Resolves product ID from Cloudinary public_id.
     * Expected format: .../{brand_slug}_{product_id}_*.jpg
     */
    protected function resolveProductIdFromPublicId(string $publicId, Brand $brand): ?int
    {
        $filename = basename($publicId);

        // Match pattern: {brand_slug}_(\d+)_
        $pattern = '/^' . preg_quote($brand->slug, '/') . '_(\d+)/i';

        if (preg_match($pattern, $filename, $matches)) {
            return (int) $matches[1];
        }

        // Fallback: Check if the filename itself is just a numeric ID
        if (is_numeric($filename)) {
            return (int) $filename;
        }

        return null;
    }

    /**
     * Detect brands with products but no images.
     */
    public function getMissingImagesReport(): array
    {
        return DB::select("
            SELECT
                b.brand_id,
                b.brand_name,
                COUNT(p.product_id) AS total_products,
                COUNT(pi.image_id) AS products_with_images,
                COUNT(p.product_id) - COUNT(DISTINCT pi.product_id) AS products_missing_images
            FROM brands b
            LEFT JOIN products p ON b.brand_id = p.brand_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id
            GROUP BY b.brand_id, b.brand_name
            HAVING COUNT(pi.image_id) = 0 OR COUNT(p.product_id) > COUNT(DISTINCT pi.product_id)
        ");
    }
}
