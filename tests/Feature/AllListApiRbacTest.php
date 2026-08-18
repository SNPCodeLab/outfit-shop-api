<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClothingSize;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreBranch;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AllListApiRbacTest extends TestCase
{
    use RefreshDatabase;

    protected Employee $staff;

    protected Employee $cashier;

    protected Employee $manager;

    protected Employee $admin;

    protected string $staffToken;

    protected string $cashierToken;

    protected string $managerToken;

    protected string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed basic fixtures needed for foreign keys and queries
        $category = Category::create(['category_name' => 'T-Shirts', 'description' => 'Cotton tees']);
        $size = ClothingSize::create(['size_name' => 'Medium', 'size_code' => 'M']);
        $color = Color::create(['color_name' => 'Royal Blue', 'hex_code' => '#002366']);

        $product = Product::create([
            'category_id' => $category->category_id,
            'product_name' => 'Signature Khmer Silk Shirt',
            'brand' => 'KhmeRiel',
            'status' => 'ACTIVE',
        ]);

        ProductVariant::create([
            'product_id' => $product->product_id,
            'size_id' => $size->size_id,
            'color_id' => $color->color_id,
            'sku' => 'KM-SHIRT-M-BLU',
            'barcode' => '8851234567890',
            'cost_price' => 15.00,
            'sale_price' => 35.00,
            'quantity' => 50,
            'reorder_level' => 10,
        ]);

        StoreBranch::create([
            'branch_name' => 'Flagship Siem Reap',
            'branch_code' => 'SR-01',
            'is_active' => true,
        ]);

        Supplier::create([
            'supplier_name' => 'Phnom Penh Silk Mills',
            'status' => 'ACTIVE',
        ]);

        Customer::create([
            'customer_name' => 'Sokha Chan',
            'phone' => '012345678',
        ]);

        // Create Employees with distinct roles
        $this->staff = Employee::create([
            'employee_name' => 'Staff Member',
            'email' => 'staff@khmeriel.local',
            'username' => 'staff_user',
            'password_hash' => Hash::make('Password123'),
            'role' => 'STAFF',
            'status' => 'ACTIVE',
        ]);

        $this->cashier = Employee::create([
            'employee_name' => 'Cashier Member',
            'email' => 'cashier@khmeriel.local',
            'username' => 'cashier_user',
            'password_hash' => Hash::make('Password123'),
            'role' => 'CASHIER',
            'status' => 'ACTIVE',
        ]);

        $this->manager = Employee::create([
            'employee_name' => 'Store Manager',
            'email' => 'manager@khmeriel.local',
            'username' => 'manager_user',
            'password_hash' => Hash::make('Password123'),
            'role' => 'MANAGER',
            'status' => 'ACTIVE',
        ]);

        $this->admin = Employee::create([
            'employee_name' => 'System Administrator',
            'email' => 'admin@khmeriel.local',
            'username' => 'admin_user',
            'password_hash' => Hash::make('Password123'),
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
        ]);

        $this->staffToken = $this->staff->createToken('test-staff')->plainTextToken;
        $this->cashierToken = $this->cashier->createToken('test-cashier')->plainTextToken;
        $this->managerToken = $this->manager->createToken('test-manager')->plainTextToken;
        $this->adminToken = $this->admin->createToken('test-admin')->plainTextToken;
    }

    // =========================================================================
    // 1. PUBLIC TIER LIST ENDPOINTS (Unauthenticated 200 OK)
    // =========================================================================

    public function test_public_catalog_list_endpoints_accessible_without_auth(): void
    {
        $publicEndpoints = [
            '/api/v1/health',
            '/api/v1/status',
            '/api/v1/categories',
            '/api/v1/clothing-sizes',
            '/api/v1/colors',
            '/api/v1/products',
            '/api/v1/brands',
            '/api/v1/bundles',
            '/api/v1/promotions/active',
            '/api/v1/branches',
            '/api/v1/variants',
            '/api/v1/variants/low-stock',
            '/api/v1/marketing/banners',
            '/api/v1/settings/audio-cues',
        ];

        foreach ($publicEndpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(200)
                ->assertJsonPath('success', true);
        }
    }

    // =========================================================================
    // 2. AUTHENTICATED TIER LIST ENDPOINTS (Requires valid token)
    // =========================================================================

    public function test_authenticated_list_endpoints_reject_guests(): void
    {
        $authEndpoints = [
            '/api/v1/customers',
            '/api/v1/sales',
            '/api/v1/shipping/orders',
        ];

        foreach ($authEndpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401)
                ->assertJsonPath('success', false);
        }
    }

    public function test_authenticated_list_endpoints_allow_cashier_and_staff(): void
    {
        $authEndpoints = [
            '/api/v1/customers',
            '/api/v1/sales',
            '/api/v1/shipping/orders',
        ];

        foreach ($authEndpoints as $endpoint) {
            $response = $this->withHeader('Authorization', 'Bearer '.$this->cashierToken)
                ->getJson($endpoint);
            $response->assertStatus(200)
                ->assertJsonPath('success', true);
        }
    }

    // =========================================================================
    // 3. MANAGER TIER LIST ENDPOINTS (Requires MANAGER or ADMIN)
    // =========================================================================

    public function test_manager_list_endpoints_reject_guests(): void
    {
        $managerEndpoints = [
            '/api/v1/dashboard/stats',
            '/api/v1/inventory/restock-recommendations',
            '/api/v1/promotions',
            '/api/v1/inventory/expiring-soon',
            '/api/v1/suppliers',
            '/api/v1/purchases',
            '/api/v1/stock-movements',
            '/api/v1/uploads/gallery',
            '/api/v1/audit-logs',
        ];

        foreach ($managerEndpoints as $endpoint) {
            $response = $this->getJson($endpoint);
            $response->assertStatus(401)
                ->assertJsonPath('success', false);
        }
    }

    public function test_manager_list_endpoints_reject_cashier_and_staff_with_403(): void
    {
        $managerEndpoints = [
            '/api/v1/dashboard/stats',
            '/api/v1/inventory/restock-recommendations',
            '/api/v1/promotions',
            '/api/v1/inventory/expiring-soon',
            '/api/v1/suppliers',
            '/api/v1/purchases',
            '/api/v1/stock-movements',
            '/api/v1/uploads/gallery',
            '/api/v1/audit-logs',
        ];

        foreach ($managerEndpoints as $endpoint) {
            // Test CASHIER role is forbidden
            $cashierRes = $this->withHeader('Authorization', 'Bearer '.$this->cashierToken)
                ->getJson($endpoint);
            $cashierRes->assertStatus(403)
                ->assertJsonPath('success', false);

            // Test STAFF role is forbidden
            $staffRes = $this->withHeader('Authorization', 'Bearer '.$this->staffToken)
                ->getJson($endpoint);
            $staffRes->assertStatus(403)
                ->assertJsonPath('success', false);
        }
    }

    public function test_manager_list_endpoints_allow_manager_and_admin_with_200(): void
    {
        $managerEndpoints = [
            '/api/v1/dashboard/stats',
            '/api/v1/inventory/restock-recommendations',
            '/api/v1/promotions',
            '/api/v1/inventory/expiring-soon',
            '/api/v1/suppliers',
            '/api/v1/purchases',
            '/api/v1/stock-movements',
            '/api/v1/uploads/gallery',
            '/api/v1/audit-logs',
        ];

        foreach ($managerEndpoints as $endpoint) {
            // Test MANAGER access
            $mgrRes = $this->withHeader('Authorization', 'Bearer '.$this->managerToken)
                ->getJson($endpoint);
            $mgrRes->assertStatus(200)
                ->assertJsonPath('success', true);

            // Test ADMIN access
            $adminRes = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
                ->getJson($endpoint);
            $adminRes->assertStatus(200)
                ->assertJsonPath('success', true);
        }
    }

    // =========================================================================
    // 4. ADMIN TIER LIST ENDPOINTS (Requires ADMIN only)
    // =========================================================================

    public function test_employee_directory_rejects_guests_with_401(): void
    {
        $response = $this->getJson('/api/v1/employees');
        $response->assertStatus(401)
            ->assertJsonPath('success', false);
    }

    public function test_employee_directory_rejects_cashier_staff_manager_with_403(): void
    {
        $nonAdminTokens = [
            'staff' => $this->staffToken,
            'cashier' => $this->cashierToken,
            'manager' => $this->managerToken,
        ];

        foreach ($nonAdminTokens as $roleName => $token) {
            $response = $this->withHeader('Authorization', 'Bearer '.$token)
                ->getJson('/api/v1/employees');

            $response->assertStatus(403)
                ->assertJsonPath('success', false);
        }
    }

    public function test_employee_directory_allows_admin_with_200(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer '.$this->adminToken)
            ->getJson('/api/v1/employees');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['employee_id', 'employee_name', 'username', 'role', 'status'],
                ],
            ]);
    }
}
