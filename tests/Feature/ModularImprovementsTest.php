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
use App\Models\SaleDetail;
use App\Models\SaleHeader;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ModularImprovementsTest extends TestCase
{
    use RefreshDatabase;

    protected Employee $admin;

    protected string $adminToken;

    protected Category $category;

    protected ClothingSize $size;

    protected Color $color;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Employee::create([
            'employee_name' => 'Admin Manager',
            'email' => 'admin.modular@test.local',
            'username' => 'admin_modular',
            'password_hash' => Hash::make('Secret123!'),
            'role' => 'ADMIN',
        ]);

        $this->adminToken = $this->admin->createToken('test-token', ['*'])->plainTextToken;
        $this->category = Category::firstOrCreate(['category_name' => 'Shirts']);
        $this->size = ClothingSize::firstOrCreate(['size_name' => 'M']);
        $this->color = Color::firstOrCreate(['color_name' => 'Blue']);
    }

    public function test_stock_movements_returns_top_level_fields_and_eager_loads_product(): void
    {
        $product = Product::create([
            'category_id' => $this->category->category_id,
            'product_name' => 'Oxford Button-Down Shirt',
            'brand' => 'Ralph Lauren',
            'status' => 'ACTIVE',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->product_id,
            'size_id' => $this->size->size_id,
            'color_id' => $this->color->color_id,
            'sku' => 'OXF-BLU-M',
            'cost_price' => 25.00,
            'sale_price' => 59.99,
            'quantity' => 100,
        ]);

        StockMovement::create([
            'variant_id' => $variant->variant_id,
            'movement_type' => 'ADJUSTMENT',
            'quantity' => 10,
            'stock_before' => 90,
            'stock_after' => 100,
            'movement_date' => now(),
            'employee_id' => $this->admin->employee_id,
            'note' => 'Initial inventory count',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->getJson('/api/v1/stock-movements');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.sku', 'OXF-BLU-M')
            ->assertJsonPath('data.0.product_name', 'Oxford Button-Down Shirt')
            ->assertJsonPath('data.0.quantity', 10)
            ->assertJsonPath('data.0.movement_type', 'ADJUSTMENT');
    }

    public function test_sales_performance_report_computes_metrics_and_daily_chart_points(): void
    {
        SaleHeader::create([
            'invoice_no' => 'INV-2026001',
            'employee_id' => $this->admin->employee_id,
            'sale_date' => now(),
            'total_amount' => 100.00,
            'tax_rate' => 10.00,
            'tax_amount' => 10.00,
            'grand_total' => 110.00,
            'status' => 'COMPLETED',
            'payment_status' => 'PAID',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->getJson('/api/v1/reports/sales-performance?timeframe=30d');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.timeframe', '30d')
            ->assertJsonPath('data.order_count', 1);

        $this->assertEquals(110.0, $response->json('data.total_sales'));
        $this->assertEquals(110.0, $response->json('data.average_order_value'));
        $this->assertNotEmpty($response->json('data.daily_chart_points'));
    }

    public function test_order_resource_formats_floats_and_names_consistently(): void
    {
        $customer = Customer::create([
            'customer_name' => 'John Doe',
            'phone' => '012345678',
            'email' => 'john.doe@example.com',
        ]);

        $product = Product::create([
            'category_id' => $this->category->category_id,
            'product_name' => 'Classic Polo',
            'brand' => 'Lacoste',
            'status' => 'ACTIVE',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->product_id,
            'size_id' => $this->size->size_id,
            'color_id' => $this->color->color_id,
            'sku' => 'POLO-WHT-L',
            'cost_price' => 20.00,
            'sale_price' => 45.00,
            'quantity' => 50,
        ]);

        $order = SaleHeader::create([
            'invoice_no' => 'INV-2026002',
            'customer_id' => $customer->customer_id,
            'employee_id' => $this->admin->employee_id,
            'sale_date' => now(),
            'total_amount' => 45.00,
            'tax_rate' => 10.00,
            'tax_amount' => 4.50,
            'grand_total' => 49.50,
            'discount' => 0.00,
            'status' => 'COMPLETED',
            'payment_status' => 'PAID',
        ]);

        SaleDetail::create([
            'sale_id' => $order->sale_id,
            'variant_id' => $variant->variant_id,
            'quantity' => 1,
            'unit_price' => 45.00,
            'discount' => 0.00,
            'sub_total' => 45.00,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->getJson("/api/v1/orders/{$order->sale_id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.customer_name', 'John Doe')
            ->assertJsonPath('data.cashier_name', 'Admin Manager');

        $this->assertEquals(49.50, $response->json('data.total'));
        $this->assertEquals(45.00, $response->json('data.subtotal'));
        $this->assertEquals(4.50, $response->json('data.tax'));
        $this->assertIsNumeric($response->json('data.total'));
        $this->assertIsNumeric($response->json('data.subtotal'));
        $this->assertIsNumeric($response->json('data.tax'));
    }
}
