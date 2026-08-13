<?php

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RbacApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cannot_access_admin_employee_management_endpoints(): void
    {
        $cashier = Employee::create([
            'employee_name' => 'Cashier User',
            'email'         => 'cashier@test.local',
            'username'      => 'cashier',
            'password_hash' => Hash::make('Secret123'),
            'role'          => 'CASHIER',
        ]);

        $token = $cashier->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/employees');

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_admin_can_access_employee_management_endpoints(): void
    {
        $admin = Employee::create([
            'employee_name' => 'Admin User',
            'email'         => 'admin@test.local',
            'username'      => 'admin',
            'password_hash' => Hash::make('Secret123'),
            'role'          => 'ADMIN',
        ]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/employees');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
