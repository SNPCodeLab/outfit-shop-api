<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CloudinarySyncTestDataSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(['category_name' => 'T-Shirts'], ['description' => 'Cotton tees']);

        $brands = [
            ['name' => 'KhmeRiel Signature', 'slug' => 'khmeriel'],
            ['name' => 'Ralph Lauren RLX', 'slug' => 'ralph-lauren-rlx'],
        ];

        foreach ($brands as $b) {
            $brand = Brand::firstOrCreate(
                ['slug' => $b['slug']],
                ['brand_name' => $b['name'], 'country_of_origin' => 'Cambodia', 'is_featured' => true]
            );

            // Create 5 products for each brand to test missing images report
            for ($i = 1; $i <= 5; $i++) {
                Product::create([
                    'category_id' => $category->category_id,
                    'brand_id' => $brand->brand_id,
                    'product_name' => $brand->brand_name." Product {$i}",
                    'description' => "Luxury product from {$brand->brand_name}",
                    'status' => 'ACTIVE',
                ]);
            }
        }
    }
}
