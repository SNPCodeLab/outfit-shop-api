<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ClothingSize;
use App\Models\Color;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreBranch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RestStandardsRegressionTest extends TestCase
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

    private function token(Employee $employee): string
    {
        return 'Bearer '.$employee->createToken('test-token')->plainTextToken;
    }

    public function test_guest_is_rejected_from_protected_endpoints(): void
    {
        $this->getJson('/api/v1/orders/1/receipt-thermal')->assertStatus(401);
        $this->getJson('/api/v1/orders/1/invoice-pdf')->assertStatus(401);
        $this->getJson('/api/v1/products/1/download')->assertStatus(401);
        $this->postJson('/api/v1/products/1/reviews', ['rating' => 5])->assertStatus(401);
        $this->getJson('/api/v1/inventory/statistics')->assertStatus(401);
        $this->getJson('/api/v1/variants/low-stock')->assertStatus(401);
    }

    public function test_registration_does_not_issue_token_or_mass_assign_admin_flag(): void
    {
        $admin = $this->makeEmployee('ADMIN');

        $response = $this->withHeader('Authorization', $this->token($admin))
            ->postJson('/api/v1/auth/register', [
                'name' => 'Fresh Account',
                'email' => 'fresh-'.uniqid().'@test.local',
                'password' => 'Secret123!',
                'password_confirmation' => 'Secret123!',
                'role' => 'staff',
                // Privilege escalation attempt via mass assignment:
                'is_admin' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true);

        // No token is issued at creation - the account must login.
        $this->assertNull($response->json('data.access_token'));

        // The is_admin payload must have been ignored (not mass-assignable).
        $userId = $response->json('data.user.id');
        $this->assertFalse(
            (bool) \DB::table('users')->where('id', $userId)->value('is_admin'),
            'is_admin must not be settable through the register endpoint'
        );
    }

    public function test_staff_cannot_write_customers(): void
    {
        $staff = $this->makeEmployee('STAFF');

        $this->withHeader('Authorization', $this->token($staff))
            ->postJson('/api/v1/customers', [
                'customer_name' => 'Sovan Sophea',
                'phone' => '012345678',
                'email' => 'sovan'.uniqid().'@test.local',
            ])
            ->assertStatus(403);
    }

    public function test_cashier_can_write_customers(): void
    {
        $cashier = $this->makeEmployee('CASHIER');

        $this->withHeader('Authorization', $this->token($cashier))
            ->postJson('/api/v1/customers', [
                'customer_name' => 'Sovan Sophea',
                'phone' => '012345678',
                'email' => 'sovan'.uniqid().'@test.local',
            ])
            ->assertStatus(201);
    }

    public function test_patch_is_accepted_for_partial_updates(): void
    {
        $manager = $this->makeEmployee('MANAGER');
        $category = Category::create(['category_name' => 'Original Name '.uniqid()]);

        $this->withHeader('Authorization', $this->token($manager))
            ->patchJson("/api/v1/categories/{$category->category_id}", [
                'description' => 'PATCHed description',
            ])
            ->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('categories', [
            'category_id' => $category->category_id,
            'description' => 'PATCHed description',
        ]);
    }

    public function test_duplicate_branch_code_returns_409_conflict(): void
    {
        $manager = $this->makeEmployee('MANAGER');

        StoreBranch::create([
            'branch_name' => 'Main Branch',
            'branch_code' => 'BR001',
        ]);

        $this->withHeader('Authorization', $this->token($manager))
            ->postJson('/api/v1/branches', [
                'branch_name' => 'Another Branch',
                'branch_code' => 'BR001',
            ])
            ->assertStatus(409)
            ->assertJsonPath('error.code', 'DUPLICATE_BRANCH_CODE');
    }

    public function test_per_page_is_capped_at_100(): void
    {
        $response = $this->getJson('/api/v1/products?per_page=5000');

        $response->assertStatus(200)
            ->assertJsonPath('meta.pagination.per_page', 100);
    }

    public function test_void_restores_stock_and_rejects_double_void(): void
    {
        // Manager performs both checkout and void so a single identity is
        // exercised end to end (checkout -> stock deduction -> void -> restore).
        $manager = $this->makeEmployee('MANAGER');

        $category = Category::firstOrCreate(['category_name' => 'Tops']);
        $size = ClothingSize::firstOrCreate(['size_name' => 'M']);
        $color = Color::firstOrCreate(['color_name' => 'Black']);
        $product = Product::create([
            'category_id' => $category->category_id,
            'product_name' => 'Void Test Shirt '.uniqid(),
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->product_id,
            'size_id' => $size->size_id,
            'color_id' => $color->color_id,
            'sku' => 'VOID-BLK-M-'.uniqid(),
            'cost_price' => 5.00,
            'sale_price' => 10.00,
            'quantity' => 5,
        ]);

        $auth = $this->token($manager);

        $checkout = $this->withHeader('Authorization', $auth)
            ->postJson('/api/v1/orders/checkout', [
                'items' => [['variant_id' => $variant->variant_id, 'quantity' => 2]],
                'tax_rate' => 0.00,
                'payment_method' => 'CASH',
            ]);
        $checkout->assertStatus(201);
        $saleId = $checkout->json('data.sale_id');

        $this->assertDatabaseHas('product_variants', [
            'variant_id' => $variant->variant_id,
            'quantity' => 3,
        ]);

        $this->withHeader('Authorization', $auth)
            ->postJson("/api/v1/orders/{$saleId}/void", ['reason' => 'test void'])
            ->assertStatus(200);

        $this->assertDatabaseHas('product_variants', [
            'variant_id' => $variant->variant_id,
            'quantity' => 5,
        ]);

        $this->withHeader('Authorization', $auth)
            ->postJson("/api/v1/orders/{$saleId}/void")
            ->assertStatus(422);
    }

    public function test_refresh_rotation_preserves_role_scoped_token_abilities(): void
    {
        $cashier = $this->makeEmployee('CASHIER');
        $original = $cashier->createToken('original', ['sales.checkout'])->plainTextToken;

        $refresh = $this->withHeader('Authorization', 'Bearer '.$original)
            ->postJson('/api/v1/auth/refresh');
        $refresh->assertStatus(200);

        $rotated = $refresh->json('data.access_token');
        $this->assertNotSame($original, $rotated);

        // Rotation revokes the presented token row (production resolves one
        // request per process; the guard cache is cleared between requests
        // here because the test process reuses a single app instance).
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => (int) explode('|', $original)[0],
        ]);

        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$original)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(401);

        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', 'Bearer '.$rotated)
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200);

        // The rotated token carries the cashier least-privilege scope,
        // never the wildcard ['*'] abilities.
        $abilities = json_decode((string) \DB::table('personal_access_tokens')
            ->latest('id')
            ->value('abilities'), true);
        $this->assertContains('sales.checkout', $abilities);
        $this->assertNotContains('*', $abilities);
    }

    public function test_forgot_and_reset_password_flow_rotates_credentials(): void
    {
        config(['app.debug' => true]);

        $employee = Employee::create([
            'employee_name' => 'Reset Target',
            'email' => 'reset-target@test.local',
            'username' => 'reset_target',
            'password_hash' => Hash::make('OldSecret123'),
            'role' => 'CASHIER',
        ]);

        $forgot = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'reset-target@test.local',
        ]);
        $forgot->assertStatus(200);

        $resetToken = $forgot->json('data.reset_token');
        $this->assertNotNull($resetToken, 'Debug mode must return the reset token for testing');

        $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'reset-target@test.local',
            'token' => $resetToken,
            'password' => 'NewSecret456',
            'password_confirmation' => 'NewSecret456',
        ])->assertStatus(200);

        // Old password no longer works; the new one does.
        $this->postJson('/api/v1/auth/login', [
            'username' => 'reset_target',
            'password' => 'OldSecret123',
        ])->assertStatus(401);

        $this->postJson('/api/v1/auth/login', [
            'username' => 'reset_target',
            'password' => 'NewSecret456',
        ])->assertStatus(200);

        // Reusing the single-use token must fail.
        $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'reset-target@test.local',
            'token' => $resetToken,
            'password' => 'Another789',
            'password_confirmation' => 'Another789',
        ])->assertStatus(401);

        unset($employee);
    }
}
