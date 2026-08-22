<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Employee;
use App\Services\CloudinaryService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AvatarUploadTest extends TestCase
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
        return 'Bearer '.$employee->createToken('test-token', ['*'])->plainTextToken;
    }

    public function test_avatar_can_be_set_from_url_for_any_role(): void
    {
        $cashier = $this->makeEmployee('CASHIER');

        $response = $this->withHeader('Authorization', $this->token($cashier))
            ->postJson('/api/v1/auth/avatar', [
                'avatar_url' => 'https://cdn.example.com/avatars/cashier.png',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.avatar_url', 'https://cdn.example.com/avatars/cashier.png');

        $this->assertDatabaseHas('employees', [
            'employee_id' => $cashier->employee_id,
            'avatar_url' => 'https://cdn.example.com/avatars/cashier.png',
        ]);

        // The profile read reflects the new picture.
        $this->app['auth']->forgetGuards();
        $this->withHeader('Authorization', $this->token($cashier))
            ->getJson('/api/v1/auth/me')
            ->assertStatus(200)
            ->assertJsonPath('data.avatar_url', 'https://cdn.example.com/avatars/cashier.png');
    }

    public function test_avatar_upload_uses_cloudinary_and_overwrites_stable_asset(): void
    {
        $admin = $this->makeEmployee('ADMIN');

        // The expected public id must be known before the request runs.
        $this->employeeId = $admin->employee_id;

        $this->mock(CloudinaryService::class, function ($mock) {
            $mock->shouldReceive('upload')
                ->once()
                ->withArgs(function ($file, $folder, $publicId): bool {
                    return $folder === 'khmeriel/avatars'
                        && $publicId === 'employee-'.$this->employeeId.'-avatar';
                })
                ->andReturn([
                    'secure_url' => 'https://res.cloudinary.com/test/image/upload/employee-avatar.png',
                    'public_id' => 'khmeriel/avatars/employee-avatar',
                ]);
        });

        $response = $this->withHeader('Authorization', $this->token($admin))
            ->post('/api/v1/auth/avatar', [
                'image' => UploadedFile::fake()->image('new-avatar.png', 120, 120),
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.avatar_url', 'https://res.cloudinary.com/test/image/upload/employee-avatar.png');

        $this->assertDatabaseHas('employees', [
            'employee_id' => $admin->employee_id,
            'avatar_url' => 'https://res.cloudinary.com/test/image/upload/employee-avatar.png',
        ]);
    }

    public function test_avatar_requires_a_source(): void
    {
        $staff = $this->makeEmployee('STAFF');

        $this->withHeader('Authorization', $this->token($staff))
            ->postJson('/api/v1/auth/avatar', [])
            ->assertStatus(422);
    }

    public function test_admin_can_set_employee_avatar_on_create(): void
    {
        $admin = $this->makeEmployee('ADMIN');

        $response = $this->withHeader('Authorization', $this->token($admin))
            ->postJson('/api/v1/employees', [
                'employee_name' => 'New Hire',
                'email' => 'newhire@test.local',
                'username' => 'newhire01',
                'password' => 'Secret123!',
                'role' => 'STAFF',
                'avatar_url' => 'https://cdn.example.com/avatars/newhire.png',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('employees', [
            'username' => 'newhire01',
            'avatar_url' => 'https://cdn.example.com/avatars/newhire.png',
        ]);
    }

    public function test_seeder_backfills_default_avatars_without_clobbering(): void
    {
        // An existing account with a user-uploaded avatar must keep it.
        $existing = Employee::create([
            'employee_name' => 'Pre Existing Cashier',
            'email' => 'cashier@api.kesararamwithdigital.tech',
            'username' => 'cashier',
            'password_hash' => Hash::make('Secret123'),
            'role' => 'CASHIER',
            'avatar_url' => 'https://res.cloudinary.com/test/image/upload/custom.png',
        ]);

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->assertSame(
            'https://res.cloudinary.com/test/image/upload/custom.png',
            $existing->fresh()->avatar_url,
            'A user-uploaded avatar must not be overwritten by the seeder backfill'
        );

        foreach (['admin', 'manager', 'staff'] as $username) {
            $this->assertNotNull(
                Employee::where('username', $username)->value('avatar_url'),
                "Standard account [{$username}] must receive a default avatar"
            );
        }

        // Idempotent: a second run changes nothing.
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->assertNotNull(Employee::where('username', 'admin')->value('avatar_url'));
    }
}
