<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Define all permissions using {resource}.{action} convention
        $permissions = [
            // General & Analytics
            'status.view',
            'dashboard.view',
            'audit-logs.view',

            // Catalog Resources (Categories, Sizes, Colors, Products, Variants)
            'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'clothing-sizes.view', 'clothing-sizes.create', 'clothing-sizes.update', 'clothing-sizes.delete',
            'colors.view', 'colors.create', 'colors.update', 'colors.delete',
            'products.view', 'products.create', 'products.update', 'products.delete',
            'variants.view', 'variants.create', 'variants.update', 'variants.delete',

            // Customer Management
            'customers.view', 'customers.create', 'customers.update',

            // POS Sales
            'sales.view', 'sales.checkout', 'sales.void',

            // Purchasing & Inventory
            'suppliers.view', 'suppliers.create', 'suppliers.update', 'suppliers.delete',
            'purchases.view', 'purchases.create',
            'stock-movements.view', 'stock-movements.adjust',

            // Employee & User Administration
            'employees.view', 'employees.create', 'employees.update', 'employees.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // 2. Define Roles and Assign Permissions

        // Viewer Role: Read-only access across resources
        $viewerRole = Role::findOrCreate('viewer', 'web');
        $viewerRole->syncPermissions([
            'status.view',
            'categories.view',
            'clothing-sizes.view',
            'colors.view',
            'products.view',
            'variants.view',
            'customers.view',
            'sales.view',
            'suppliers.view',
            'purchases.view',
            'stock-movements.view',
        ]);

        // Cashier Role: Checkout + Customer registration + Read-only catalog
        $cashierRole = Role::findOrCreate('cashier', 'web');
        $cashierRole->syncPermissions([
            'status.view',
            'categories.view',
            'clothing-sizes.view',
            'colors.view',
            'products.view',
            'variants.view',
            'customers.view',
            'customers.create',
            'customers.update',
            'sales.view',
            'sales.checkout',
        ]);

        // Manager Role: Full inventory, sales, suppliers, purchases access (No employee management / audit logs)
        $managerRole = Role::findOrCreate('manager', 'web');
        $managerRole->syncPermissions([
            'status.view',
            'dashboard.view',
            'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'clothing-sizes.view', 'clothing-sizes.create', 'clothing-sizes.update', 'clothing-sizes.delete',
            'colors.view', 'colors.create', 'colors.update', 'colors.delete',
            'products.view', 'products.create', 'products.update', 'products.delete',
            'variants.view', 'variants.create', 'variants.update', 'variants.delete',
            'customers.view', 'customers.create', 'customers.update',
            'sales.view', 'sales.checkout', 'sales.void',
            'suppliers.view', 'suppliers.create', 'suppliers.update', 'suppliers.delete',
            'purchases.view', 'purchases.create',
            'stock-movements.view', 'stock-movements.adjust',
        ]);

        // Admin Role: Full access to everything
        $adminRole = Role::findOrCreate('admin', 'web');
        $adminRole->syncPermissions(Permission::all());
    }
}
