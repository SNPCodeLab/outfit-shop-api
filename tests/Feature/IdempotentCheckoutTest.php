<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClothingSize;
use App\Models\Color;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SaleHeader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class IdempotentCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_retry_with_same_idempotency_key_returns_original_sale_without_duplicate_charge(): void
    {
        $cashier = Employee::create([
            'employee_name' => 'Cashier One',
            'email' => 'cashier@test.local',
            'username' => 'cashier1',
            'password_hash' => Hash::make('Secret123'),
            'role' => 'CASHIER',
        ]);

        $category = Category::firstOrCreate(['category_name' => 'Tops']);
        $size = ClothingSize::firstOrCreate(['size_name' => 'M']);
        $color = Color::firstOrCreate(['color_name' => 'Black']);

        $product = Product::create([
            'category_id' => $category->category_id,
            'product_name' => 'Idempotent Test Shirt '.uniqid(),
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->product_id,
            'size_id' => $size->size_id,
            'color_id' => $color->color_id,
            'sku' => 'IDEM-BLK-M-'.uniqid(),
            'cost_price' => 10.00,
            'sale_price' => 25.00,
            'quantity' => 10,
        ]);

        $token = $cashier->createToken('pos-token')->plainTextToken;
        $key = 'idem-'.uniqid();

        $payload = [
            'items' => [
                ['variant_id' => $variant->variant_id, 'quantity' => 2],
            ],
            'tax_rate' => 0.00,
            'payment_method' => 'CASH',
            'payment_amount' => 50.00,
            'idempotency_key' => $key,
        ];

        // First request creates the sale
        $first = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/sales/checkout', $payload);
        $first->assertStatus(201)
            ->assertJsonPath('success', true);
        $firstSaleId = $first->json('data.sale_id');

        // Retry with the same key returns the original sale (200, same id)
        $second = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/sales/checkout', $payload);
        $second->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sale_id', $firstSaleId);

        // Exactly one sale row carries the key: no duplicate charge
        $this->assertSame(
            1,
            SaleHeader::where('idempotency_key', $key)->count(),
            'Idempotency key must map to exactly one sale header'
        );

        // Stock deducted exactly once (10 - 2 = 8)
        $this->assertDatabaseHas('product_variants', [
            'variant_id' => $variant->variant_id,
            'quantity' => 8,
        ]);
    }

    public function test_idempotency_key_is_enforced_by_unique_database_index(): void
    {
        $this->assertTrue(
            collect(\Schema::getIndexes('sale_headers'))->pluck('name')
                ->contains('uq_sale_headers_idempotency_key'),
            'The UNIQUE index backing the idempotency guarantee must exist'
        );
    }
}
