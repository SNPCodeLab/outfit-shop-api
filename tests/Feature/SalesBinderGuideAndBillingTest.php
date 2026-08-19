<?php

declare(strict_types=1);

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

class SalesBinderGuideAndBillingTest extends TestCase
{
    use RefreshDatabase;

    public function test_help_centre_web_ui_renders_html_successfully(): void
    {
        $response = $this->getJson('/guide');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_topics', 10);
    }

    public function test_help_centre_json_api_returns_structured_topics(): void
    {
        $response = $this->getJson('/api/v1/guide');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.total_topics', 10)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'title',
                    'tagline',
                    'total_topics',
                    'categories' => [
                        '*' => ['id', 'title', 'tagline', 'icon', 'description', 'sections', 'tips'],
                    ],
                    'popular_topics',
                ],
                'message',
            ]);
    }

    public function test_inventory_statistics_endpoint_returns_financial_valuation(): void
    {
        $category = Category::firstOrCreate(['category_name' => 'Shirts']);
        $size = ClothingSize::firstOrCreate(['size_name' => 'L']);
        $color = Color::firstOrCreate(['color_name' => 'Navy']);
        $product = Product::create(['category_id' => $category->category_id, 'product_name' => 'Oxford Shirt']);

        ProductVariant::create([
            'product_id' => $product->product_id,
            'size_id' => $size->size_id,
            'color_id' => $color->color_id,
            'sku' => 'OXF-NVY-L-'.uniqid(),
            'cost_price' => 20.00,
            'sale_price' => 50.00,
            'quantity' => 10,
        ]);

        $response = $this->getJson('/api/v1/inventory/statistics');
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'summary' => [
                        'total_skus',
                        'total_units_on_hand',
                        'total_units_reserved',
                        'total_units_available',
                        'total_units_incoming',
                        'purchased_value_usd',
                        'resale_value_usd',
                        'potential_profit_usd',
                        'margin_percent',
                    ],
                    'categories_breakdown',
                    'locations_breakdown',
                ],
            ]);
    }

    public function test_estimates_creation_and_conversion_lifecycle(): void
    {
        $cashier = Employee::create([
            'employee_name' => 'Staff Tester',
            'email' => 'staff@test.local',
            'username' => 'staff1',
            'password_hash' => Hash::make('Secret123'),
            'role' => 'CASHIER',
        ]);

        $customer = Customer::create([
            'customer_name' => 'VIP Client',
            'phone' => '012999888',
        ]);

        $category = Category::firstOrCreate(['category_name' => 'Tops']);
        $size = ClothingSize::firstOrCreate(['size_name' => 'M']);
        $color = Color::firstOrCreate(['color_name' => 'Black']);
        $product = Product::create(['category_id' => $category->category_id, 'product_name' => 'Polo Shirt']);

        $variant = ProductVariant::create([
            'product_id' => $product->product_id,
            'size_id' => $size->size_id,
            'color_id' => $color->color_id,
            'sku' => 'POLO-BLK-M-'.uniqid(),
            'cost_price' => 15.00,
            'sale_price' => 30.00,
            'quantity' => 15,
        ]);

        $token = $cashier->createToken('test-token')->plainTextToken;

        // 1. Create Estimate Quote
        $estimateRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/estimates', [
                'customer_id' => $customer->customer_id,
                'items' => [
                    [
                        'variant_id' => $variant->variant_id,
                        'quantity' => 2,
                        'discount' => 0.00,
                    ],
                ],
                'overall_discount' => 0.00,
                'tax_rate' => 10.00,
            ]);

        $estimateRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ESTIMATE')
            ->assertJsonPath('data.grand_total', 66); // (30*2=60) + 10% = 66

        $estimateId = $estimateRes->json('data.sale_id');

        // Verify stock remains untouched at 15
        $this->assertEquals(15, $variant->fresh()->quantity);

        // 2. 1-Click Convert Estimate to Invoice
        $convertRes = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson("/api/v1/estimates/{$estimateId}/convert", [
                'payment_method' => 'CASH',
            ]);

        $convertRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'COMPLETED');

        // Verify stock decremented to 13
        $this->assertEquals(13, $variant->fresh()->quantity);

        // 3. Render A4 Printable Invoice View
        $pdfRes = $this->get("/api/v1/sales/{$estimateId}/invoice-pdf");
        $pdfRes->assertStatus(200)
            ->assertHeader('Content-Type', 'text/html; charset=utf-8')
            ->assertSee('TAX INVOICE')
            ->assertSee('STORE RECEIPT');
    }
}
