<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ClothingSize;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\PurchaseDetail;
use App\Models\PurchaseHeader;
use App\Models\SaleDetail;
use App\Models\SaleHeader;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with real clothing store data.
     */
    public function run(): void
    {
        // 1. Employees (RBAC Users)
        $admin = Employee::firstOrCreate(['username' => 'admin'], [
            'employee_name' => 'System Administrator',
            'gender'        => 'Male',
            'phone'         => '+85512345678',
            'email'         => 'admin@ssmis.local',
            'position'      => 'General Manager',
            'password_hash' => Hash::make('Admin@123456'),
            'role'          => 'ADMIN',
            'status'        => 'ACTIVE',
        ]);

        $manager = Employee::firstOrCreate(['username' => 'manager'], [
            'employee_name' => 'Store Manager',
            'gender'        => 'Female',
            'phone'         => '+85512345679',
            'email'         => 'manager@ssmis.local',
            'position'      => 'Inventory Manager',
            'password_hash' => Hash::make('Manager@123456'),
            'role'          => 'MANAGER',
            'status'        => 'ACTIVE',
        ]);

        $cashier = Employee::firstOrCreate(['username' => 'cashier'], [
            'employee_name' => 'Senior Cashier',
            'gender'        => 'Female',
            'phone'         => '+85512345680',
            'email'         => 'cashier@ssmis.local',
            'position'      => 'POS Cashier',
            'password_hash' => Hash::make('Cashier@123456'),
            'role'          => 'CASHIER',
            'status'        => 'ACTIVE',
        ]);

        // 2. Categories
        $catTops     = Category::firstOrCreate(['category_name' => 'Tops & T-Shirts'], ['description' => 'Casual T-Shirts, Polo Shirts, and Silk Tops']);
        $catPants    = Category::firstOrCreate(['category_name' => 'Pants & Jeans'], ['description' => 'Denim jeans, trousers, and shorts']);
        $catDresses  = Category::firstOrCreate(['category_name' => 'Dresses & Skirts'], ['description' => 'Summer dresses, skirts, and evening wear']);
        $catOuterwear = Category::firstOrCreate(['category_name' => 'Jackets & Hoodies'], ['description' => 'Outerwear, hoodies, and jackets']);

        // 3. Sizes
        $sizeS  = ClothingSize::firstOrCreate(['size_name' => 'Small (S)'], ['description' => 'Chest 36 inch']);
        $sizeM  = ClothingSize::firstOrCreate(['size_name' => 'Medium (M)'], ['description' => 'Chest 38 inch']);
        $sizeL  = ClothingSize::firstOrCreate(['size_name' => 'Large (L)'], ['description' => 'Chest 40 inch']);
        $sizeXL = ClothingSize::firstOrCreate(['size_name' => 'Extra Large (XL)'], ['description' => 'Chest 42 inch']);

        // 4. Colors
        $colorNavy  = Color::firstOrCreate(['color_name' => 'Navy Blue'], ['description' => 'Deep Navy']);
        $colorBlack = Color::firstOrCreate(['color_name' => 'Black'], ['description' => 'Classic Solid Black']);
        $colorWhite = Color::firstOrCreate(['color_name' => 'White'], ['description' => 'Pure Pure White']);
        $colorGray  = Color::firstOrCreate(['color_name' => 'Charcoal Gray'], ['description' => 'Dark Charcoal']);

        // 5. Suppliers
        $supplier = Supplier::firstOrCreate(['supplier_name' => 'Khmer Garments Co., Ltd.'], [
            'phone'         => '+85523999888',
            'email'         => 'orders@khmergarments.com',
            'address'       => 'Phnom Penh Special Economic Zone, Cambodia',
            'status'        => 'ACTIVE',
        ]);

        // 6. Customers
        $customer = Customer::firstOrCreate(['email' => 'bopha@example.com'], [
            'customer_name' => 'Bopha Chea',
            'gender'        => 'Female',
            'phone'         => '+85598765432',
            'address'       => 'Street 271, Phnom Penh',
        ]);

        // 7. Products & Real SKUs
        $productsData = [
            [
                'name' => 'Cambodian Silk Short-Sleeve Shirt',
                'cat'  => $catTops->category_id,
                'brand'=> 'Angkor Fashion',
                'desc' => 'Traditional woven Cambodian natural silk short-sleeve shirt.',
                'sku'  => 'SILK-SHIRT-NVY-M',
                'code' => '8851000100011',
                'cost' => 18.00,
                'price'=> 35.00,
                'qty'  => 45,
            ],
            [
                'name' => 'Classic Organic Cotton Polo Shirt',
                'cat'  => $catTops->category_id,
                'brand'=> 'SS Apparel',
                'desc' => '100% Breathable Organic Cotton Polo Shirt with embroidered logo.',
                'sku'  => 'POLO-COTTON-BLK-L',
                'code' => '8851000100022',
                'cost' => 8.50,
                'price'=> 18.50,
                'qty'  => 120,
            ],
            [
                'name' => 'Slim Fit Stretch Denim Jeans',
                'cat'  => $catPants->category_id,
                'brand'=> 'Denim Co.',
                'desc' => 'Premium 12oz stretch cotton denim jeans.',
                'sku'  => 'JEANS-DENIM-GRY-32',
                'code' => '8851000100033',
                'cost' => 20.00,
                'price'=> 42.00,
                'qty'  => 65,
            ],
            [
                'name' => 'Linen Summer Sundress',
                'cat'  => $catDresses->category_id,
                'brand'=> 'Breeze Wear',
                'desc' => 'Lightweight breathable linen floral print summer dress.',
                'sku'  => 'DRESS-LINEN-WHT-S',
                'code' => '8851000100044',
                'cost' => 17.50,
                'price'=> 38.00,
                'qty'  => 28,
            ],
            [
                'name' => 'Heavyweight Fleece Pullover Hoodie',
                'cat'  => $catOuterwear->category_id,
                'brand'=> 'Urban Streetwear',
                'desc' => '350gsm brushed fleece pullover hoodie with kangaroo pocket.',
                'sku'  => 'HOODIE-FLEECE-BLK-XL',
                'code' => '8851000100055',
                'cost' => 22.00,
                'price'=> 45.00,
                'qty'  => 85,
            ]
        ];

        foreach ($productsData as $p) {
            $product = Product::firstOrCreate(['product_name' => $p['name']], [
                'category_id'  => $p['cat'],
                'brand'        => $p['brand'],
                'description'  => $p['desc'],
                'status'       => 'ACTIVE',
            ]);

            ProductVariant::firstOrCreate(['sku' => $p['sku']], [
                'product_id'    => $product->product_id,
                'size_id'       => $sizeM->size_id,
                'color_id'      => $colorNavy->color_id,
                'barcode'       => $p['code'],
                'cost_price'    => $p['cost'],
                'sale_price'    => $p['price'],
                'quantity'      => $p['qty'],
                'reorder_level' => 10,
            ]);
        }
    }
}
