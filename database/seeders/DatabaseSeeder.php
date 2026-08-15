<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ClothingSize;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Order matters:
     *  1. Roles & permissions + default accounts (RolesAndPermissionsSeeder)
     *  2. Reference data: categories, sizes, colors, suppliers, customers
     *  3. Products & variants (depend on categories, sizes, colors)
     */
    public function run(): void
    {
        // ── 1. Roles, Permissions & Default User Accounts ────────────────────
        $this->call(RolesAndPermissionsSeeder::class);

        // ── 2. Categories ────────────────────────────────────────────────────
        $catTops      = Category::firstOrCreate(['category_name' => 'Tops & T-Shirts'],    ['description' => 'Casual T-Shirts, Polo Shirts, and Silk Tops']);
        $catPants     = Category::firstOrCreate(['category_name' => 'Pants & Jeans'],       ['description' => 'Denim jeans, trousers, and shorts']);
        $catDresses   = Category::firstOrCreate(['category_name' => 'Dresses & Skirts'],    ['description' => 'Summer dresses, skirts, and evening wear']);
        $catOuterwear = Category::firstOrCreate(['category_name' => 'Jackets & Hoodies'],   ['description' => 'Outerwear, hoodies, and jackets']);

        // ── 3. Clothing Sizes ────────────────────────────────────────────────
        $sizeS  = ClothingSize::firstOrCreate(['size_name' => 'Small (S)'],       ['description' => 'Chest 36 inch']);
        $sizeM  = ClothingSize::firstOrCreate(['size_name' => 'Medium (M)'],      ['description' => 'Chest 38 inch']);
        $sizeL  = ClothingSize::firstOrCreate(['size_name' => 'Large (L)'],       ['description' => 'Chest 40 inch']);
        $sizeXL = ClothingSize::firstOrCreate(['size_name' => 'Extra Large (XL)'],['description' => 'Chest 42 inch']);

        // ── 4. Colors ────────────────────────────────────────────────────────
        $colorNavy  = Color::firstOrCreate(['color_name' => 'Navy Blue'],      ['description' => 'Deep Navy']);
        $colorBlack = Color::firstOrCreate(['color_name' => 'Black'],          ['description' => 'Classic Solid Black']);
        $colorWhite = Color::firstOrCreate(['color_name' => 'White'],          ['description' => 'Pure White']);
        $colorGray  = Color::firstOrCreate(['color_name' => 'Charcoal Gray'], ['description' => 'Dark Charcoal']);

        // ── 5. Suppliers ─────────────────────────────────────────────────────
        $supplier = Supplier::firstOrCreate(['supplier_name' => 'Khmer Garments Co., Ltd.'], [
            'phone'   => '+85523999888',
            'email'   => 'orders@khmergarments.com',
            'address' => 'Phnom Penh Special Economic Zone, Cambodia',
            'status'  => 'ACTIVE',
        ]);

        // ── 6. Customers ─────────────────────────────────────────────────────
        Customer::firstOrCreate(['email' => 'bopha@example.com'], [
            'customer_name' => 'Bopha Chea',
            'gender'        => 'Female',
            'phone'         => '+85598765432',
            'address'       => 'Street 271, Phnom Penh',
        ]);

        // ── 7. Products & Variants ───────────────────────────────────────────
        $products = [
            [
                'name'  => 'Cambodian Silk Short-Sleeve Shirt',
                'cat'   => $catTops->category_id,
                'brand' => 'Angkor Fashion',
                'desc'  => 'Traditional woven Cambodian natural silk short-sleeve shirt.',
                'sku'   => 'SILK-SHIRT-NVY-M',
                'code'  => '8851000100011',
                'cost'  => 18.00,
                'price' => 35.00,
                'qty'   => 45,
                'color' => $colorNavy,
                'size'  => $sizeM,
            ],
            [
                'name'  => 'Classic Organic Cotton Polo Shirt',
                'cat'   => $catTops->category_id,
                'brand' => 'SS Apparel',
                'desc'  => '100% Breathable Organic Cotton Polo Shirt with embroidered logo.',
                'sku'   => 'POLO-COTTON-BLK-L',
                'code'  => '8851000100022',
                'cost'  => 8.50,
                'price' => 18.50,
                'qty'   => 120,
                'color' => $colorBlack,
                'size'  => $sizeL,
            ],
            [
                'name'  => 'Slim Fit Stretch Denim Jeans',
                'cat'   => $catPants->category_id,
                'brand' => 'Denim Co.',
                'desc'  => 'Premium 12oz stretch cotton denim jeans.',
                'sku'   => 'JEANS-DENIM-GRY-32',
                'code'  => '8851000100033',
                'cost'  => 20.00,
                'price' => 42.00,
                'qty'   => 65,
                'color' => $colorGray,
                'size'  => $sizeM,
            ],
            [
                'name'  => 'Linen Summer Sundress',
                'cat'   => $catDresses->category_id,
                'brand' => 'Breeze Wear',
                'desc'  => 'Lightweight breathable linen floral print summer dress.',
                'sku'   => 'DRESS-LINEN-WHT-S',
                'code'  => '8851000100044',
                'cost'  => 17.50,
                'price' => 38.00,
                'qty'   => 28,
                'color' => $colorWhite,
                'size'  => $sizeS,
            ],
            [
                'name'  => 'Heavyweight Fleece Pullover Hoodie',
                'cat'   => $catOuterwear->category_id,
                'brand' => 'Urban Streetwear',
                'desc'  => '350gsm brushed fleece pullover hoodie with kangaroo pocket.',
                'sku'   => 'HOODIE-FLEECE-BLK-XL',
                'code'  => '8851000100055',
                'cost'  => 22.00,
                'price' => 45.00,
                'qty'   => 85,
                'color' => $colorBlack,
                'size'  => $sizeXL,
            ],
        ];

        foreach ($products as $p) {
            $product = Product::firstOrCreate(['product_name' => $p['name']], [
                'category_id' => $p['cat'],
                'brand'       => $p['brand'],
                'description' => $p['desc'],
                'status'      => 'ACTIVE',
            ]);

            ProductVariant::firstOrCreate(['sku' => $p['sku']], [
                'product_id'    => $product->product_id,
                'size_id'       => $p['size']->size_id,
                'color_id'      => $p['color']->color_id,
                'barcode'       => $p['code'],
                'cost_price'    => $p['cost'],
                'sale_price'    => $p['price'],
                'quantity'      => $p['qty'],
                'reorder_level' => 10,
            ]);
        }

        $this->command->info('✅  Database seeded successfully.');
    }
}
