<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClothingSize;
use App\Models\Color;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CartAndCheckoutMathTest extends TestCase
{
    use RefreshDatabase;

    private function makeEmployee(string $role): Employee
    {
        return Employee::create([
            'employee_name' => ucfirst(strtolower($role)).' User',
            'email' => strtolower($role).uniqid().'@test.local',
            'username' => strtolower($role).uniqid(),
            'password_hash' => Hash::make('Secret123'),
            'role' => $role,
        ]);
    }

    private function makeVariant(int $quantity = 10): ProductVariant
    {
        $category = Category::firstOrCreate(['category_name' => 'Cart Tops']);
        $size = ClothingSize::firstOrCreate(['size_name' => 'M']);
        $color = Color::firstOrCreate(['color_name' => 'Black']);
        $product = Product::create([
            'category_id' => $category->category_id,
            'product_name' => 'Cart Test Shirt '.uniqid(),
        ]);

        return ProductVariant::create([
            'product_id' => $product->product_id,
            'size_id' => $size->size_id,
            'color_id' => $color->color_id,
            'sku' => 'CART-BLK-M-'.uniqid(),
            'cost_price' => 10.00,
            'sale_price' => 30.00,
            'quantity' => $quantity,
        ]);
    }

    public function test_guest_cart_full_lifecycle(): void
    {
        $variant = $this->makeVariant();
        $sessionId = (string) \Str::uuid();

        // Add an item to a guest cart identified by session header
        $add = $this->withHeader('X-Session-Id', $sessionId)
            ->postJson('/api/v1/cart/items', [
                'variant_id' => $variant->variant_id,
                'quantity' => 1,
            ]);
        $add->assertStatus(201)->assertJsonPath('success', true);

        $items = $add->json('data.items');
        $cartSessionId = $add->json('data.session_id');
        $this->assertNotNull($cartSessionId, 'Cart response must expose its session id');
        $itemId = $items[0]['cart_item_id'];

        // Update quantity via PATCH (partial cart update)
        $this->withHeader('X-Session-Id', $cartSessionId)
            ->patchJson("/api/v1/cart/items/{$itemId}", ['quantity' => 2])
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        // Remove the item
        $this->withHeader('X-Session-Id', $cartSessionId)
            ->deleteJson("/api/v1/cart/items/{$itemId}")
            ->assertStatus(200);

        // Canonical collection clear (DELETE /cart)
        $this->withHeader('X-Session-Id', $cartSessionId)
            ->deleteJson('/api/v1/cart')
            ->assertStatus(200);
    }

    public function test_product_show_cache_is_flushed_on_update(): void
    {
        $manager = $this->makeEmployee('MANAGER');
        $variant = $this->makeVariant();
        $productId = $variant->product_id;

        // Prime the cache
        $this->getJson("/api/v1/products/{$productId}")->assertStatus(200);

        // Update through the API - observer must flush product:{id}
        $sentinel = 'Cache invalidation sentinel '.uniqid();
        $this->withHeader('Authorization', 'Bearer '.$manager->createToken('t')->plainTextToken)
            ->patchJson("/api/v1/products/{$productId}", [
                'description' => $sentinel,
            ])
            ->assertStatus(200);

        // Second read must reflect the update, not the cached pre-update copy
        $this->getJson("/api/v1/products/{$productId}")
            ->assertStatus(200)
            ->assertJsonPath('data.description', $sentinel);
    }

    public function test_checkout_money_math_with_item_discount_overall_discount_and_tax(): void
    {
        $manager = $this->makeEmployee('MANAGER');
        $variant = $this->makeVariant(); // sale_price 30.00

        // 3 x 30.00 - 10.00 item discount = 80.00 total
        // Net = 80.00 - 5.00 overall discount = 75.00
        // Tax (10%) = 7.50 -> Grand total = 82.50
        // Tendered 100.00 -> Change = 17.50
        $response = $this->withHeader('Authorization', 'Bearer '.$manager->createToken('t')->plainTextToken)
            ->postJson('/api/v1/orders/checkout', [
                'items' => [
                    [
                        'variant_id' => $variant->variant_id,
                        'quantity' => 3,
                        'discount' => 10.00,
                    ],
                ],
                'overall_discount' => 5.00,
                'tax_rate' => 10.00,
                'payment_method' => 'CASH',
                'payment_amount' => 100.00,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.total_amount', 80)
            ->assertJsonPath('data.grand_total', 82.5)
            ->assertJsonPath('data.payments.0.change_due', 17.5);

        // Historical price preservation: line item keeps 30.00 unit price
        $this->assertSame(30.0, (float) $response->json('data.details.0.unit_price'));

        // Stock deducted once: 10 - 3 = 7
        $this->assertDatabaseHas('product_variants', [
            'variant_id' => $variant->variant_id,
            'quantity' => 7,
        ]);
    }
}
