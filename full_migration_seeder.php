<?php
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

try {
    DB::transaction(function () {
        echo "Starting Full Database Migration & Seeding...\n";

        // 1. Create Default Admin
        DB::table('employees')->insert([
            'employee_name' => 'Admin User',
            'email' => 'admin@ssmis.local',
            'username' => 'admin',
            'password_hash' => Hash::make('password123'),
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
            'position' => 'ADMINISTRATOR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Admin employee created (admin / password123).\n";

        // 2. Categories
        $categories = [
            ['name' => 'Tops', 'dept' => 'APPAREL'],
            ['name' => 'Bottoms', 'dept' => 'APPAREL'],
            ['name' => 'Outerwear', 'dept' => 'APPAREL'],
            ['name' => 'Footwear', 'dept' => 'FOOTWEAR'],
            ['name' => 'Accessories', 'dept' => 'ACCESSORIES'],
        ];

        $categoryIds = [];
        foreach ($categories as $cat) {
            $id = DB::table('categories')->insertGetId([
                'category_name' => $cat['name'],
                'department_type' => $cat['dept'],
                'description' => "Premium " . strtolower($cat['name']) . " collection.",
                'created_at' => now(),
                'updated_at' => now(),
            ], 'category_id');
            $categoryIds[$cat['name']] = $id;
        }
        echo "Categories created.\n";

        // 3. Brands
        $brands = [
            ['name' => 'Louis Vuitton', 'country' => 'France', 'desc' => 'French luxury fashion house.'],
            ['name' => 'Adidas', 'country' => 'Germany', 'desc' => 'Global sportswear leader.'],
            ['name' => 'Nike', 'country' => 'USA', 'desc' => 'American footwear and apparel giant.'],
            ['name' => 'Gucci', 'country' => 'Italy', 'desc' => 'Italian high-end luxury brand.'],
            ['name' => 'Prada', 'country' => 'Italy', 'desc' => 'Italian luxury leather goods specialist.'],
            ['name' => 'Dior', 'country' => 'France', 'desc' => 'French luxury fashion house.'],
        ];

        $brandIds = [];
        foreach ($brands as $b) {
            $id = DB::table('brands')->insertGetId([
                'brand_name' => $b['name'],
                'slug' => Str::slug($b['name']),
                'country_of_origin' => $b['country'],
                'description' => $b['desc'],
                'is_featured' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'brand_id');
            $brandIds[$b['name']] = $id;
        }
        echo "Brands created.\n";

        // 4. Colors & Sizes
        $colors = ['Midnight Black', 'Pure White', 'Slate Grey', 'Royal Red', 'Navy Blue'];
        $colorIds = [];
        foreach ($colors as $c) {
            $colorIds[] = DB::table('colors')->insertGetId([
                'color_name' => $c,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'color_id');
        }

        $sizes = ['XS', 'S', 'M', 'L', 'XL'];
        $sizeIds = [];
        foreach ($sizes as $s) {
            $sizeIds[] = DB::table('clothing_sizes')->insertGetId([
                'size_name' => $s,
                'created_at' => now(),
                'updated_at' => now(),
            ], 'size_id');
        }
        echo "Attributes created.\n";

        // 5. Products Data
        $productsData = [
            'Louis Vuitton' => [
                ['name' => 'Monogram Gradient Hoodie', 'cat' => 'Outerwear', 'price' => 2500, 'gender' => 'UNISEX'],
                ['name' => 'Damier Pattern Dress Shirt', 'cat' => 'Tops', 'price' => 950, 'gender' => 'MEN'],
            ],
            'Adidas' => [
                ['name' => 'Adicolor Firebird Track Jacket', 'cat' => 'Outerwear', 'price' => 85, 'gender' => 'UNISEX'],
                ['name' => 'Stan Smith Sustainable Shoes', 'cat' => 'Footwear', 'price' => 110, 'gender' => 'UNISEX'],
            ],
            'Nike' => [
                ['name' => 'Air Jordan 1 Retro High OG', 'cat' => 'Footwear', 'price' => 180, 'gender' => 'MEN'],
                ['name' => 'Nike Tech Fleece Windrunner', 'cat' => 'Outerwear', 'price' => 145, 'gender' => 'MEN'],
            ],
            'Gucci' => [
                ['name' => 'GG Supreme Logo T-Shirt', 'cat' => 'Tops', 'price' => 650, 'gender' => 'UNISEX'],
            ],
            'Prada' => [
                ['name' => 'Re-Nylon Gabardine Jacket', 'cat' => 'Outerwear', 'price' => 1950, 'gender' => 'UNISEX'],
            ],
            'Dior' => [
                ['name' => 'Dior Oblique Sweater', 'cat' => 'Tops', 'price' => 1550, 'gender' => 'MEN'],
            ],
        ];

        foreach ($productsData as $brandName => $products) {
            foreach ($products as $pData) {
                $productId = DB::table('products')->insertGetId([
                    'category_id' => $categoryIds[$pData['cat']],
                    'brand_id' => $brandIds[$brandName],
                    'product_name' => $pData['name'],
                    'brand' => $brandName,
                    'product_type' => 'PHYSICAL_APPAREL',
                    'gender' => $pData['gender'],
                    'season_collection' => 'Fall/Winter 2026',
                    'status' => 'ACTIVE',
                    'created_at' => now(),
                    'updated_at' => now(),
                ], 'product_id');

                // Variants
                $selectedSizes = (array) array_rand(array_flip($sizeIds), rand(2, 3));
                foreach ($selectedSizes as $sizeId) {
                    $colorId = $colorIds[array_rand($colorIds)];
                    $sizeName = DB::table('clothing_sizes')->where('size_id', $sizeId)->value('size_name');

                    DB::table('product_variants')->insert([
                        'product_id' => $productId,
                        'size_id' => $sizeId,
                        'color_id' => $colorId,
                        'sku' => strtoupper(substr($brandName, 0, 2)) . "-" . rand(1000, 9999) . "-" . $sizeName,
                        'barcode' => "885" . rand(100000000, 999999999),
                        'cost_price' => round($pData['price'] * 0.4, 2),
                        'sale_price' => round($pData['price'], 2),
                        'wholesale_price' => round($pData['price'] * 0.8, 2),
                        'quantity' => rand(20, 100),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        echo "Migration and Seeding completed successfully!\n";
    });
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
