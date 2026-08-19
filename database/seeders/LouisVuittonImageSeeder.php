<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\CloudinaryService;
use Illuminate\Database\Seeder;

class LouisVuittonImageSeeder extends Seeder
{
    public function run(): void
    {
        $cloudinary = new CloudinaryService();
        $brand = Brand::where('slug', 'louis-vuitton')->first();

        if (!$brand) {
            return;
        }

        // 1. Fetch ALL LV/Monogram resources from Cloudinary
        try {
            $resources = $cloudinary->listResources(null, 1000);
        } catch (\Exception $e) {
            echo "Cloudinary API Error: " . $e->getMessage() . "\n";
            return;
        }

        $targetKeywords = ['LV', 'Louis', 'Vuitton', 'Monogram'];
        $matches = [];
        foreach ($resources as $res) {
            $publicId = strtolower($res['public_id']);
            foreach ($targetKeywords as $kw) {
                if (str_contains($publicId, strtolower($kw))) {
                    $matches[] = [
                        'public_id' => $res['public_id'],
                        'url' => $res['secure_url'] ?? $res['url']
                    ];
                    break;
                }
            }
        }

        if (empty($matches)) {
            echo "No matching images found in Cloudinary.\n";
            return;
        }

        // 2. Fetch all LV products
        $products = Product::where('brand_id', $brand->brand_id)->get();

        if ($products->isEmpty()) {
            echo "No Louis Vuitton products found in database.\n";
            return;
        }

        echo "Seeding " . count($matches) . " images for " . $products->count() . " products...\n";

        // 3. Distribute images across products
        $imgIndex = 0;
        $totalMatches = count($matches);

        foreach ($products as $product) {
            // Give each product at least 8 unique images (cycling through matches)
            for ($i = 0; $i < 8; $i++) {
                $img = $matches[$imgIndex % $totalMatches];

                ProductImage::updateOrCreate(
                    [
                        'brand_id' => $brand->brand_id,
                        'product_id' => $product->product_id,
                        'image_url' => $img['url'],
                    ],
                    [
                        'image_public_id' => $img['public_id'],
                        'shot_type' => $i === 0 ? 'LOOK' : 'DETAIL',
                        'is_primary' => $i === 0,
                    ]
                );

                $imgIndex++;
            }
        }

        echo "Louis Vuitton Image Seeding Completed.\n";
    }
}
