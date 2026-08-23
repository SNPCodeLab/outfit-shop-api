<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * RolesAndPermissionsSeeder
 *
 * Seeds:
 *  1. All permissions using {resource}.{action} convention
 *  2. Four roles: admin, manager, cashier, staff
 *  3. Default Employee accounts (admin, manager, cashier)
 *  4. Default User accounts for the frontend dev team
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear Spatie permission cache
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Clear existing role-permission assignments to avoid duplicate key errors on PostgreSQL
        DB::table('role_has_permissions')->delete();
        DB::table('model_has_permissions')->delete();
        DB::table('model_has_roles')->delete();

        // ─── 1. Permissions ──────────────────────────────────────────────────
        $permissions = [
            // Status & Analytics
            'status.view',
            'dashboard.view',
            'audit-logs.view',

            // Catalog (public reads are unrestricted at route level)
            'categories.view',   'categories.create',   'categories.update',   'categories.delete',
            'clothing-sizes.view', 'clothing-sizes.create', 'clothing-sizes.update', 'clothing-sizes.delete',
            'colors.view',       'colors.create',       'colors.update',       'colors.delete',
            'products.view',     'products.create',     'products.update',     'products.delete',
            'variants.view',     'variants.create',     'variants.update',     'variants.delete',

            // Customers
            'customers.view', 'customers.create', 'customers.update',

            // POS Sales
            'sales.view', 'sales.checkout', 'sales.void',

            // Suppliers & Purchasing
            'suppliers.view', 'suppliers.create', 'suppliers.update', 'suppliers.delete',
            'purchases.view', 'purchases.create',

            // Inventory
            'stock-movements.view', 'stock-movements.adjust',

            // Employee & User Administration
            'employees.view',  'employees.create',  'employees.update',  'employees.delete',
            'users.create',
        ];

        foreach ($permissions as $perm) {
            Permission::findOrCreate($perm, 'web');
        }

        // ─── 2. Roles ────────────────────────────────────────────────────────

        // STAFF — read-only basics
        $staffRole = Role::findOrCreate('staff', 'web');
        $staffRole->syncPermissions([
            'status.view',
            'categories.view', 'clothing-sizes.view', 'colors.view',
            'products.view', 'variants.view',
            'customers.view', 'sales.view',
        ]);

        // CASHIER — POS + customer registration
        $cashierRole = Role::findOrCreate('cashier', 'web');
        $cashierRole->syncPermissions([
            'status.view',
            'categories.view', 'clothing-sizes.view', 'colors.view',
            'products.view', 'variants.view',
            'customers.view', 'customers.create', 'customers.update',
            'sales.view', 'sales.checkout',
        ]);

        // MANAGER — full catalog + inventory (no employee management)
        $managerRole = Role::findOrCreate('manager', 'web');
        $managerRole->syncPermissions([
            'status.view', 'dashboard.view', 'audit-logs.view',
            'categories.view',   'categories.create',   'categories.update',   'categories.delete',
            'clothing-sizes.view', 'clothing-sizes.create', 'clothing-sizes.update', 'clothing-sizes.delete',
            'colors.view',       'colors.create',       'colors.update',       'colors.delete',
            'products.view',     'products.create',     'products.update',     'products.delete',
            'variants.view',     'variants.create',     'variants.update',     'variants.delete',
            'customers.view',    'customers.create',    'customers.update',
            'sales.view',        'sales.checkout',      'sales.void',
            'suppliers.view',    'suppliers.create',    'suppliers.update',    'suppliers.delete',
            'purchases.view',    'purchases.create',
            'stock-movements.view', 'stock-movements.adjust',
        ]);

        // ADMIN — everything
        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->syncPermissions(Permission::all());

        // Also create viewer alias (maps to staff)
        $viewerRole = Role::findOrCreate('viewer', 'web');
        $viewerRole->syncPermissions($staffRole->permissions);

        // ─── 3. Default Employee Accounts ────────────────────────────────────

        // Default profile pictures (DiceBear initials avatars - deterministic,
        // no upload needed; replaced by real uploads via POST /auth/avatar).
        $avatar = fn (string $seed): string => 'https://api.dicebear.com/9.x/initials/svg?seed='.rawurlencode($seed);

        $employees = [
            [
                'username' => 'admin',
                'employee_name' => 'System Administrator',
                'email' => 'admin@api.kesararamwithdigital.tech',
                'position' => 'Chief Executive Officer',
                'password_hash' => Hash::make('Adm!nKh@2026#Sec'),
                'role' => 'ADMIN',
                'gender' => 'Male',
                'phone' => '+85512345678',
                'status' => 'ACTIVE',
                'avatar_url' => $avatar('System Administrator'),
            ],
            [
                'username' => 'manager',
                'employee_name' => 'Store Manager',
                'email' => 'manager@api.kesararamwithdigital.tech',
                'position' => 'Store Operations Manager',
                'password_hash' => Hash::make('Mgr$Store@KH2026'),
                'role' => 'MANAGER',
                'gender' => 'Female',
                'phone' => '+85512345679',
                'status' => 'ACTIVE',
                'avatar_url' => $avatar('Store Manager'),
            ],
            [
                'username' => 'cashier',
                'employee_name' => 'Senior Cashier',
                'email' => 'cashier@api.kesararamwithdigital.tech',
                'position' => 'Senior POS Operator',
                'password_hash' => Hash::make('C@sh!erPOS#KH26'),
                'role' => 'CASHIER',
                'gender' => 'Female',
                'phone' => '+85512345680',
                'status' => 'ACTIVE',
                'avatar_url' => $avatar('Senior Cashier'),
            ],
            [
                'username' => 'staff',
                'employee_name' => 'General Staff',
                'email' => 'staff@api.kesararamwithdigital.tech',
                'position' => 'Inventory Assistant',
                'password_hash' => Hash::make('St@ffKhmer!2026'),
                'role' => 'STAFF',
                'gender' => 'Male',
                'phone' => '+85512345681',
                'status' => 'ACTIVE',
                'avatar_url' => $avatar('General Staff'),
            ],
        ];

        foreach ($employees as $data) {
            $employee = Employee::firstOrCreate(
                ['username' => $data['username']],
                $data
            );

            // Backfill the default avatar on pre-existing accounts without
            // clobbering an avatar the user uploaded themselves.
            if ($employee->wasRecentlyCreated === false && blank($employee->avatar_url)) {
                $employee->update(['avatar_url' => $data['avatar_url']]);
            }
        }

        // ─── 4. Frontend Dev Team User Accounts ──────────────────────────────
        // These are User model accounts (not employees) for the frontend team.
        // Created via POST /api/v1/auth/register (admin only) in production.

        $devTeam = [
            [
                'name' => 'Frontend Developer',
                'email' => 'frontend@api.kesararamwithdigital.tech',
                'password' => Hash::make('Frontend@123456'),
                'role' => 'manager',
                'is_admin' => false,
                'avatar_url' => $avatar('Frontend Developer'),
            ],
            [
                'name' => 'Admin User',
                'email' => 'superadmin@api.kesararamwithdigital.tech',
                'password' => Hash::make('SuperAdmin@123456'),
                'role' => 'admin',
                'is_admin' => true,
                'avatar_url' => $avatar('Admin User'),
            ],
        ];

        foreach ($devTeam as $data) {
            $roleName = $data['role'];
            $shouldBeAdmin = (bool) $data['is_admin'];
            // is_admin is not mass-assignable by policy; it is also set via
            // a raw boolean literal because the pooled production connection
            // binds PHP booleans as integers, which Postgres rejects.
            unset($data['role'], $data['is_admin']);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            if ($shouldBeAdmin) {
                DB::table('users')
                    ->where('email', $user->email)
                    ->update(['is_admin' => DB::raw('true')]);
            }

            // Backfill without overwriting a user-uploaded avatar.
            if ($user->wasRecentlyCreated === false && blank($user->avatar_url)) {
                $user->update(['avatar_url' => $data['avatar_url']]);
            }

            if (method_exists($user, 'syncRoles')) {
                $user->syncRoles([$roleName]);
            }
        }

        $this->command->info('✅  Roles, permissions, and default accounts seeded successfully.');
        $this->command->table(
            ['Role', 'Default Credentials'],
            [
                ['ADMIN (Employee)',    'admin / Adm!nKh@2026#Sec'],
                ['MANAGER (Employee)', 'manager / Mgr$Store@KH2026'],
                ['CASHIER (Employee)', 'cashier / C@sh!erPOS#KH26'],
                ['STAFF (Employee)',   'staff / St@ffKhmer!2026'],
                ['MANAGER (User)',     'frontend@api.kesararamwithdigital.tech / Frontend@123456'],
                ['ADMIN (User)',       'superadmin@api.kesararamwithdigital.tech / SuperAdmin@123456'],
            ]
        );
    }
}
