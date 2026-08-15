<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\Models\Employee;
use App\Models\User;

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

        // ─── 1. Permissions ──────────────────────────────────────────────────
        $permissions = [
            // Status & Analytics
            'status.view',
            'dashboard.view',
            'audit-logs.view',

            // Catalog (public reads are unrestricted at route level)
            'categories.view',   'categories.create',   'categories.update',   'categories.delete',
            'clothing-sizes.view','clothing-sizes.create','clothing-sizes.update','clothing-sizes.delete',
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
            'clothing-sizes.view','clothing-sizes.create','clothing-sizes.update','clothing-sizes.delete',
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

        $employees = [
            [
                'username'      => 'admin',
                'employee_name' => 'System Administrator',
                'email'         => 'admin@ssmis.local',
                'position'      => 'General Manager',
                'password_hash' => Hash::make('Admin@123456'),
                'role'          => 'ADMIN',
                'gender'        => 'Male',
                'phone'         => '+85512345678',
                'status'        => 'ACTIVE',
            ],
            [
                'username'      => 'manager',
                'employee_name' => 'Store Manager',
                'email'         => 'manager@ssmis.local',
                'position'      => 'Inventory Manager',
                'password_hash' => Hash::make('Manager@123456'),
                'role'          => 'MANAGER',
                'gender'        => 'Female',
                'phone'         => '+85512345679',
                'status'        => 'ACTIVE',
            ],
            [
                'username'      => 'cashier',
                'employee_name' => 'Senior Cashier',
                'email'         => 'cashier@ssmis.local',
                'position'      => 'POS Cashier',
                'password_hash' => Hash::make('Cashier@123456'),
                'role'          => 'CASHIER',
                'gender'        => 'Female',
                'phone'         => '+85512345680',
                'status'        => 'ACTIVE',
            ],
        ];

        foreach ($employees as $data) {
            Employee::firstOrCreate(
                ['username' => $data['username']],
                $data
            );
        }

        // ─── 4. Frontend Dev Team User Accounts ──────────────────────────────
        // These are User model accounts (not employees) for the frontend team.
        // Created via POST /api/v1/auth/register (admin only) in production.

        $devTeam = [
            [
                'name'     => 'Frontend Developer',
                'email'    => 'frontend@ssmis.local',
                'password' => Hash::make('Frontend@123456'),
                'is_admin' => false,
                'role'     => 'manager',
            ],
            [
                'name'     => 'Admin User',
                'email'    => 'superadmin@ssmis.local',
                'password' => Hash::make('SuperAdmin@123456'),
                'is_admin' => true,
                'role'     => 'admin',
            ],
        ];

        foreach ($devTeam as $data) {
            $roleName = $data['role'];
            unset($data['role']);

            $user = User::firstOrCreate(
                ['email' => $data['email']],
                $data
            );

            if (method_exists($user, 'syncRoles')) {
                $user->syncRoles([$roleName]);
            }
        }

        $this->command->info('✅  Roles, permissions, and default accounts seeded successfully.');
        $this->command->table(
            ['Role', 'Default Credentials'],
            [
                ['ADMIN (Employee)',    'admin / Admin@123456'],
                ['MANAGER (Employee)', 'manager / Manager@123456'],
                ['CASHIER (Employee)', 'cashier / Cashier@123456'],
                ['MANAGER (User)',     'frontend@ssmis.local / Frontend@123456'],
                ['ADMIN (User)',       'superadmin@ssmis.local / SuperAdmin@123456'],
            ]
        );
    }
}
