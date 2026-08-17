<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClothingSize;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class POSCheckoutApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_transactional_pos_checkout_deducts_stock_and_creates_payment(): void
    {
        $cashier = Employee::create([
            'employee_name' => 'Cashier One',
            'email'         => 'cashier@test.local',
            'username'      => 'cashier1',
            'password_hash' => Hash::make('Secret123'),
            'role'          => 'CASHIER',
        ]);

        $category = Category::firstOrCreate(['category_name' => 'Tops']);
        $size     = ClothingSize::firstOrCreate(['size_name' => 'M']);
        $color    = Color::firstOrCreate(['color_name' => 'Black']);

        $product = Product::create([
            'category_id'  => $category->category_id,
            'product_name' => 'Classic Polo Shirt ' . uniqid(),
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->product_id,
            'size_id'    => $size->size_id,
            'color_id'   => $color->color_id,
            'sku'        => 'POLO-BLK-M-' . uniqid(),
            'cost_price' => 15.00,
            'sale_price' => 30.00,
            'quantity'   => 20,
        ]);

        $token = $cashier->createToken('pos-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/sales/checkout', [
                'items' => [
                    [
                        'variant_id' => $variant->variant_id,
                        'quantity'   => 3,
                        'discount'   => 0.00,
                    ]
                ],
                'tax_rate'       => 0.00,
                'payment_method' => 'CASH',
                'payment_amount' => 90.00,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.grand_total', 90);

        // Verify stock quantity was decremented atomically (20 - 3 = 17)
        $this->assertDatabaseHas('product_variants', [
            'variant_id' => $variant->variant_id,
            'quantity'   => 17,
        ]);

        // Verify payment record
        $this->assertDatabaseHas('payments', [
            'payment_method' => 'CASH',
            'amount'         => 90.00,
        ]);
    }

    public function test_pos_checkout_rejects_negative_stock_requests(): void
    {
        $cashier = Employee::create([
            'employee_name' => 'Cashier One',
            'email'         => 'cashier@test.local',
            'username'      => 'cashier1',
            'password_hash' => Hash::make('Secret123'),
            'role'          => 'CASHIER',
        ]);

        $category = Category::firstOrCreate(['category_name' => 'Tops']);
        $size     = ClothingSize::firstOrCreate(['size_name' => 'M']);
        $color    = Color::firstOrCreate(['color_name' => 'Black']);

        $product = Product::create([
            'category_id'  => $category->category_id,
            'product_name' => 'Classic Polo Shirt ' . uniqid(),
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->product_id,
            'size_id'    => $size->size_id,
            'color_id'   => $color->color_id,
            'sku'        => 'POLO-BLK-M-' . uniqid(),
            'cost_price' => 15.00,
            'sale_price' => 30.00,
            'quantity'   => 2, // Only 2 in stock
        ]);

        $token = $cashier->createToken('pos-token')->plainTextToken;

        // Try to buy 5 items when stock is 2
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/sales/checkout', [
                'items' => [
                    [
                        'variant_id' => $variant->variant_id,
                        'quantity'   => 5,
                    ]
                ],
                'payment_method' => 'CASH',
            ]);

        $response->assertStatus(400)
            ->assertJsonPath('success', false);

        // Verify stock remains untouched at 2
        $this->assertDatabaseHas('product_variants', [
            'variant_id' => $variant->variant_id,
            'quantity'   => 2,
        ]);
    }
}
