<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPersistenceAndRbacCrudTest extends TestCase
{
    use RefreshDatabase;

    protected Employee $admin;

    protected Employee $manager;

    protected Employee $cashier;

    protected Employee $staff;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Employee::create([
            'employee_name' => 'Admin User',
            'first_name' => 'Admin',
            'last_name' => 'Root',
            'username' => 'admin',
            'email' => 'admin@test.local',
            'role' => 'ADMIN',
            'status' => 'ACTIVE',
            'password_hash' => Hash::make('Password123'),
        ]);

        $this->manager = Employee::create([
            'employee_name' => 'Manager User',
            'first_name' => 'Manager',
            'last_name' => 'User',
            'username' => 'manager',
            'email' => 'manager@test.local',
            'role' => 'MANAGER',
            'status' => 'ACTIVE',
            'password_hash' => Hash::make('Password123'),
        ]);

        $this->cashier = Employee::create([
            'employee_name' => 'Cashier User',
            'first_name' => 'Cashier',
            'last_name' => 'User',
            'username' => 'cashier',
            'email' => 'cashier@test.local',
            'role' => 'CASHIER',
            'status' => 'ACTIVE',
            'password_hash' => Hash::make('Password123'),
        ]);

        $this->staff = Employee::create([
            'employee_name' => 'Staff User',
            'first_name' => 'Staff',
            'last_name' => 'User',
            'username' => 'staff',
            'email' => 'staff@test.local',
            'role' => 'STAFF',
            'status' => 'ACTIVE',
            'password_hash' => Hash::make('Password123'),
        ]);

        $this->category = Category::create([
            'category_name' => 'Dresses & Tops',
            'department_type' => 'WOMEN',
            'description' => 'Women clothing category',
        ]);
    }

    private function token(Employee $employee): string
    {
        return $employee->createToken('test-token')->plainTextToken;
    }

    public function test_customer_creation_by_cashier(): void
    {
        $createRes = $this->withToken($this->token($this->cashier))->postJson('/api/v1/customers', [
            'customer_name' => 'Jane Doe',
            'phone' => '+85512345678',
            'email' => 'jane@example.com',
            'loyalty_points' => 150,
            'loyalty_tier' => 'Gold',
        ]);
        $createRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.customer_name', 'Jane Doe');

        $this->assertDatabaseHas('customers', [
            'customer_name' => 'Jane Doe',
            'phone' => '+85512345678',
        ]);
    }

    public function test_customer_deletion_forbidden_for_staff(): void
    {
        $customer = Customer::create([
            'customer_name' => 'Customer Staff Reject',
            'phone' => '+85599887711',
        ]);

        $this->withToken($this->token($this->staff))
            ->deleteJson("/api/v1/customers/{$customer->customer_id}")
            ->assertStatus(403);
    }

    public function test_customer_deletion_allowed_for_manager(): void
    {
        $customer = Customer::create([
            'customer_name' => 'Customer Manager Allow',
            'phone' => '+85599887722',
        ]);

        $this->flushHeaders();
        $this->app['auth']->forgetGuards();
        $this->withToken($this->token($this->manager))
            ->deleteJson("/api/v1/customers/{$customer->customer_id}")
            ->assertStatus(200);

        $this->assertSoftDeleted('customers', ['customer_id' => $customer->customer_id]);
    }

    public function test_product_creation_with_default_variant_persistence(): void
    {
        $res = $this->withToken($this->token($this->manager))->postJson('/api/v1/products', [
            'category_id' => $this->category->category_id,
            'product_name' => 'Silk Evening Dress',
            'brand' => 'OUTFIT Luxe',
            'description' => 'Fine silk evening dress with embroidery',
            'image_url' => 'https://res.cloudinary.com/demo/image/upload/v12345/dress.jpg',
            'sku' => 'SKU-DRS-001',
            'sale_price' => 129.99,
            'cost_price' => 50.00,
            'quantity' => 25,
            'material_fabric' => '100% Silk',
            'gender' => 'WOMEN',
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.product_name', 'Silk Evening Dress');

        $productId = $res->json('data.product_id');

        $this->assertDatabaseHas('products', [
            'product_id' => $productId,
            'brand' => 'OUTFIT Luxe',
        ]);

        $this->assertDatabaseHas('product_variants', [
            'product_id' => $productId,
            'sku' => 'SKU-DRS-001',
            'sale_price' => 129.99,
            'quantity' => 25,
        ]);
    }

    public function test_branch_and_inventory_batches_full_crud(): void
    {
        // 1. Create store branch
        $branchRes = $this->withToken($this->token($this->admin))->postJson('/api/v1/branches', [
            'branch_name' => 'Riverside Flagship',
            'branch_code' => 'BR-RS-001',
            'city' => 'Phnom Penh',
            'is_active' => true,
        ]);
        $branchRes->assertStatus(201)
            ->assertJsonPath('data.branch_name', 'Riverside Flagship');

        $branchId = $branchRes->json('data.branch_id');

        // 2. Fetch single branch
        $this->withToken($this->token($this->admin))
            ->getJson("/api/v1/branches/{$branchId}")
            ->assertStatus(200)
            ->assertJsonPath('data.branch_code', 'BR-RS-001');

        // 3. Update branch
        $this->withToken($this->token($this->admin))
            ->putJson("/api/v1/branches/{$branchId}", [
                'branch_name' => 'Riverside Flagship Central',
            ])->assertStatus(200)
            ->assertJsonPath('data.branch_name', 'Riverside Flagship Central');

        // 4. Delete branch
        $this->withToken($this->token($this->admin))
            ->deleteJson("/api/v1/branches/{$branchId}")
            ->assertStatus(200);
    }
}
