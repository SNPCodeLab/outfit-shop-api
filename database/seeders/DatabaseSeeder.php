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
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Employees (RBAC Users)
        $admin = Employee::create([
            'employee_name' => 'System Administrator',
            'gender'        => 'Male',
            'phone'         => '+1234567890',
            'email'         => 'admin@ssmis.local',
            'position'      => 'General Manager',
            'username'      => 'admin',
            'password_hash' => Hash::make('Admin@123456'),
            'role'          => 'ADMIN',
            'status'        => 'ACTIVE',
        ]);

        $manager = Employee::create([
            'employee_name' => 'Store Manager',
            'gender'        => 'Female',
            'phone'         => '+1234567891',
            'email'         => 'manager@ssmis.local',
            'position'      => 'Inventory Manager',
            'username'      => 'manager',
            'password_hash' => Hash::make('Manager@123456'),
            'role'          => 'MANAGER',
            'status'        => 'ACTIVE',
        ]);

        $cashier = Employee::create([
            'employee_name' => 'Senior Cashier',
            'gender'        => 'Female',
            'phone'         => '+1234567892',
            'email'         => 'cashier@ssmis.local',
            'position'      => 'POS Cashier',
            'username'      => 'cashier',
            'password_hash' => Hash::make('Cashier@123456'),
            'role'          => 'CASHIER',
            'status'        => 'ACTIVE',
        ]);

        $staff = Employee::create([
            'employee_name' => 'Warehouse Staff',
            'gender'        => 'Male',
            'phone'         => '+1234567893',
            'email'         => 'staff@ssmis.local',
            'position'      => 'Stock Keeper',
            'username'      => 'staff',
            'password_hash' => Hash::make('Staff@123456'),
            'role'          => 'STAFF',
            'status'        => 'ACTIVE',
        ]);

        // 2. Categories
        $catShirts = Category::create(['category_name' => 'Shirts & Tops', 'description' => 'T-Shirts, Polos, and Formal Shirts']);
        $catPants  = Category::create(['category_name' => 'Pants & Jeans', 'description' => 'Denim jeans, trousers, and shorts']);
        $catJackets = Category::create(['category_name' => 'Jackets & Outerwear', 'description' => 'Winter coats, hoodies, and jackets']);

        // 3. Sizes
        $sizeS  = ClothingSize::create(['size_name' => 'Small (S)', 'description' => 'Size S']);
        $sizeM  = ClothingSize::create(['size_name' => 'Medium (M)', 'description' => 'Size M']);
        $sizeL  = ClothingSize::create(['size_name' => 'Large (L)', 'description' => 'Size L']);
        $sizeXL = ClothingSize::create(['size_name' => 'Extra Large (XL)', 'description' => 'Size XL']);

        // 4. Colors
        $colorBlack = Color::create(['color_name' => 'Black', 'description' => 'Classic Black']);
        $colorWhite = Color::create(['color_name' => 'White', 'description' => 'Bright White']);
        $colorNavy  = Color::create(['color_name' => 'Navy Blue', 'description' => 'Deep Navy']);

        // 5. Suppliers
        $supplier = Supplier::create([
            'supplier_name' => 'Global Apparel Distributors',
            'phone'         => '+18005550199',
            'email'         => 'sales@globalapparel.com',
            'address'       => '100 Garment Avenue, Industrial Park',
            'status'        => 'ACTIVE',
        ]);

        // 6. Customers
        $customer = Customer::create([
            'customer_name' => 'Jane Smith',
            'gender'        => 'Female',
            'phone'         => '+1555987654',
            'email'         => 'janesmith@example.com',
            'address'       => '456 Retail Boulevard, Suite 12',
        ]);

        // 7. Products & Variants
        $prodTshirt = Product::create([
            'category_id'  => $catShirts->category_id,
            'product_name' => 'Premium Cotton T-Shirt',
            'brand'        => 'Urban Style',
            'description'  => '100% Organic Cotton Crew Neck T-Shirt',
            'status'       => 'ACTIVE',
        ]);

        $var1 = ProductVariant::create([
            'product_id'    => $prodTshirt->product_id,
            'size_id'       => $sizeM->size_id,
            'color_id'      => $colorBlack->color_id,
            'sku'           => 'TSHIRT-BLK-M',
            'barcode'       => '8850001001001',
            'cost_price'    => 10.00,
            'sale_price'    => 25.00,
            'quantity'      => 50,
            'reorder_level' => 10,
        ]);

        $var2 = ProductVariant::create([
            'product_id'    => $prodTshirt->product_id,
            'size_id'       => $sizeL->size_id,
            'color_id'      => $colorWhite->color_id,
            'sku'           => 'TSHIRT-WHT-L',
            'barcode'       => '8850001001002',
            'cost_price'    => 10.00,
            'sale_price'    => 25.00,
            'quantity'      => 30,
            'reorder_level' => 10,
        ]);

        // 8. Sample Purchase Receiving
        $purchase = PurchaseHeader::create([
            'supplier_id'   => $supplier->supplier_id,
            'employee_id'   => $manager->employee_id,
            'purchase_date' => now(),
            'total_amount'  => 800.00,
            'status'        => 'COMPLETED',
        ]);

        PurchaseDetail::create([
            'purchase_id' => $purchase->purchase_id,
            'variant_id'  => $var1->variant_id,
            'quantity'    => 50,
            'cost_price'  => 10.00,
            'sub_total'   => 500.00,
        ]);

        PurchaseDetail::create([
            'purchase_id' => $purchase->purchase_id,
            'variant_id'  => $var2->variant_id,
            'quantity'    => 30,
            'cost_price'  => 10.00,
            'sub_total'   => 300.00,
        ]);

        StockMovement::create([
            'variant_id'     => $var1->variant_id,
            'movement_type'  => 'PURCHASE',
            'quantity'       => 50,
            'movement_date'  => now(),
            'reference_type' => 'PurchaseHeader',
            'reference_id'   => $purchase->purchase_id,
            'note'           => 'Initial Purchase Receiving',
            'employee_id'    => $manager->employee_id,
        ]);

        // 9. Sample POS Sale
        $sale = SaleHeader::create([
            'customer_id'  => $customer->customer_id,
            'employee_id'  => $cashier->employee_id,
            'sale_date'    => now(),
            'total_amount' => 50.00,
            'discount'     => 5.00,
            'grand_total'  => 45.00,
            'status'       => 'COMPLETED',
        ]);

        SaleDetail::create([
            'sale_id'    => $sale->sale_id,
            'variant_id' => $var1->variant_id,
            'quantity'   => 2,
            'unit_price' => 25.00,
            'discount'   => 5.00,
            'sub_total'  => 45.00,
        ]);

        Payment::create([
            'sale_id'          => $sale->sale_id,
            'payment_date'     => now(),
            'amount'           => 45.00,
            'payment_method'   => 'CASH',
            'payment_status'   => 'PAID',
            'reference_number' => 'POS-1-SEED',
        ]);

        StockMovement::create([
            'variant_id'     => $var1->variant_id,
            'movement_type'  => 'SALE',
            'quantity'       => -2,
            'movement_date'  => now(),
            'reference_type' => 'SaleHeader',
            'reference_id'   => $sale->sale_id,
            'note'           => 'Initial Seed POS Sale #1',
            'employee_id'    => $cashier->employee_id,
        ]);
    }
}
