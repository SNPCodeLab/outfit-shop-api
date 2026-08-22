<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_login_with_valid_credentials(): void
    {
        $employee = Employee::create([
            'employee_name' => 'Test Cashier',
            'email' => 'cashier@test.local',
            'username' => 'cashier_test',
            'password_hash' => Hash::make('Secret123'),
            'role' => 'CASHIER',
            'status' => 'ACTIVE',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'cashier_test',
            'password' => 'Secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'employee' => ['employee_id', 'username', 'role'],
                ],
            ]);
    }

    public function test_employee_login_fails_with_invalid_password(): void
    {
        Employee::create([
            'employee_name' => 'Test Cashier',
            'email' => 'cashier@test.local',
            'username' => 'cashier_test',
            'password_hash' => Hash::make('Secret123'),
            'role' => 'CASHIER',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'username' => 'cashier_test',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error.code', 'AUTHENTICATION_FAILED');
    }

    public function test_authenticated_employee_can_fetch_profile_and_logout(): void
    {
        $employee = Employee::create([
            'employee_name' => 'Test Cashier',
            'email' => 'cashier@test.local',
            'username' => 'cashier_test',
            'password_hash' => Hash::make('Secret123'),
            'role' => 'CASHIER',
        ]);

        $token = $employee->createToken('test-token')->plainTextToken;

        // Profile
        $profileResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me');

        $profileResponse->assertStatus(200)
            ->assertJsonPath('data.username', 'cashier_test');

        // Logout
        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout');

        $logoutResponse->assertStatus(200);
    }
}
