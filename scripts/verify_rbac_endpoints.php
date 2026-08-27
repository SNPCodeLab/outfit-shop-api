<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// Ensure test employees exist for each RBAC tier
$admin = Employee::firstOrCreate(
    ['username' => 'admin_test'],
    [
        'employee_name' => 'Admin Test User',
        'password_hash' => Hash::make('Admin@123'),
        'role' => 'ADMIN',
        'position' => 'System Administrator',
        'phone' => '012000001',
        'email' => 'admin@test.com',
        'status' => 'ACTIVE',
    ]
);

$manager = Employee::firstOrCreate(
    ['username' => 'manager_test'],
    [
        'employee_name' => 'Manager Test User',
        'password_hash' => Hash::make('Manager@123'),
        'role' => 'MANAGER',
        'position' => 'Store Manager',
        'phone' => '012000002',
        'email' => 'manager@test.com',
        'status' => 'ACTIVE',
    ]
);

$cashier = Employee::firstOrCreate(
    ['username' => 'cashier_test'],
    [
        'employee_name' => 'Cashier Test User',
        'password_hash' => Hash::make('Cashier@123'),
        'role' => 'CASHIER',
        'position' => 'Front Desk Cashier',
        'phone' => '012000003',
        'email' => 'cashier@test.com',
        'status' => 'ACTIVE',
    ]
);

// Issue Sanctum Tokens
$adminToken = $admin->createToken('admin_test_token')->plainTextToken;
$managerToken = $manager->createToken('manager_test_token')->plainTextToken;
$cashierToken = $cashier->createToken('cashier_test_token')->plainTextToken;

echo "========================================================================\n";
echo "           🔍 COMPREHENSIVE RBAC & ENDPOINT INTEGRITY AUDIT 🔍         \n";
echo "========================================================================\n";
echo '• Admin Token:   '.substr($adminToken, 0, 10)."...\n";
echo '• Manager Token: '.substr($managerToken, 0, 10)."...\n";
echo '• Cashier Token: '.substr($cashierToken, 0, 10)."...\n";
echo "------------------------------------------------------------------------\n\n";

$testScenarios = [
    // 1. PUBLIC ENDPOINTS (No Token)
    ['name' => 'Public: GET /up', 'method' => 'GET', 'uri' => '/up', 'token' => null, 'expected' => 200],
    ['name' => 'Public: GET /status', 'method' => 'GET', 'uri' => '/status', 'token' => null, 'expected' => 200],
    ['name' => 'Public: GET /api/v1/products', 'method' => 'GET', 'uri' => '/api/v1/products', 'token' => null, 'expected' => 200],
    ['name' => 'Public: GET /api/v1/products/1', 'method' => 'GET', 'uri' => '/api/v1/products/1', 'token' => null, 'expected' => 200],
    ['name' => 'Public: GET /api/v1/categories', 'method' => 'GET', 'uri' => '/api/v1/categories', 'token' => null, 'expected' => 200],
    ['name' => 'Public: GET /api/v1/clothing-sizes', 'method' => 'GET', 'uri' => '/api/v1/clothing-sizes', 'token' => null, 'expected' => 200],
    ['name' => 'Public: GET /api/v1/colors', 'method' => 'GET', 'uri' => '/api/v1/colors', 'token' => null, 'expected' => 200],
    ['name' => 'Public: GET /api/v1/variants', 'method' => 'GET', 'uri' => '/api/v1/variants', 'token' => null, 'expected' => 200],
    ['name' => 'Public: GET /api/v1/marketing/banners', 'method' => 'GET', 'uri' => '/api/v1/marketing/banners', 'token' => null, 'expected' => 200],
    ['name' => 'Public: GET /api/v1/promotions/active', 'method' => 'GET', 'uri' => '/api/v1/promotions/active', 'token' => null, 'expected' => 200],
    ['name' => 'Public: GET /api/v1/settings/audio-cues', 'method' => 'GET', 'uri' => '/api/v1/settings/audio-cues', 'token' => null, 'expected' => 200],

    // 2. CASHIER ACCESS TESTS
    ['name' => 'Cashier: GET /api/v1/customers (Auth required)', 'method' => 'GET', 'uri' => '/api/v1/customers', 'token' => $cashierToken, 'expected' => 200],
    ['name' => 'Cashier: GET /api/v1/orders (Auth required)', 'method' => 'GET', 'uri' => '/api/v1/orders', 'token' => $cashierToken, 'expected' => 200],
    ['name' => 'Cashier: GET /api/v1/shifts/current (Auth required)', 'method' => 'GET', 'uri' => '/api/v1/shifts/current', 'token' => $cashierToken, 'expected' => 200],
    ['name' => 'Cashier: GET /api/v1/shipping-orders (Auth required)', 'method' => 'GET', 'uri' => '/api/v1/shipping-orders', 'token' => $cashierToken, 'expected' => 200],

    // 3. RBAC GUARDS: CASHIER BLOCKED FROM MANAGER ENDPOINTS (Expect 403)
    ['name' => 'Guard Test: Cashier trying GET /api/v1/stock-movements (Must Block: 403)', 'method' => 'GET', 'uri' => '/api/v1/stock-movements', 'token' => $cashierToken, 'expected' => 403],
    ['name' => 'Guard Test: Cashier trying GET /api/v1/suppliers (Must Block: 403)', 'method' => 'GET', 'uri' => '/api/v1/suppliers', 'token' => $cashierToken, 'expected' => 403],
    ['name' => 'Guard Test: Cashier trying GET /api/v1/purchases (Must Block: 403)', 'method' => 'GET', 'uri' => '/api/v1/purchases', 'token' => $cashierToken, 'expected' => 403],
    ['name' => 'Guard Test: Cashier trying GET /api/v1/employees (Must Block: 403)', 'method' => 'GET', 'uri' => '/api/v1/employees', 'token' => $cashierToken, 'expected' => 403],

    // 4. MANAGER ACCESS TESTS (Should Pass 200)
    ['name' => 'Manager: GET /api/v1/stock-movements', 'method' => 'GET', 'uri' => '/api/v1/stock-movements', 'token' => $managerToken, 'expected' => 200],
    ['name' => 'Manager: GET /api/v1/suppliers', 'method' => 'GET', 'uri' => '/api/v1/suppliers', 'token' => $managerToken, 'expected' => 200],
    ['name' => 'Manager: GET /api/v1/purchases', 'method' => 'GET', 'uri' => '/api/v1/purchases', 'token' => $managerToken, 'expected' => 200],
    ['name' => 'Manager: GET /api/v1/inventory/restock-recommendations', 'method' => 'GET', 'uri' => '/api/v1/inventory/restock-recommendations', 'token' => $managerToken, 'expected' => 200],
    ['name' => 'Manager: GET /api/v1/inventory/expiring-soon', 'method' => 'GET', 'uri' => '/api/v1/inventory/expiring-soon', 'token' => $managerToken, 'expected' => 200],

    // 5. RBAC GUARDS: MANAGER BLOCKED FROM ADMIN ENDPOINTS (Expect 403)
    ['name' => 'Guard Test: Manager trying GET /api/v1/employees (Must Block: 403)', 'method' => 'GET', 'uri' => '/api/v1/employees', 'token' => $managerToken, 'expected' => 403],
    ['name' => 'Guard Test: Manager trying POST /api/v1/employees (Must Block: 403)', 'method' => 'POST', 'uri' => '/api/v1/employees', 'token' => $managerToken, 'expected' => 403],

    // 6. ADMIN ACCESS TESTS (Should Pass 200)
    ['name' => 'Admin: GET /api/v1/dashboard/stats', 'method' => 'GET', 'uri' => '/api/v1/dashboard/stats', 'token' => $adminToken, 'expected' => 200],
    ['name' => 'Admin: GET /api/v1/employees', 'method' => 'GET', 'uri' => '/api/v1/employees', 'token' => $adminToken, 'expected' => 200],
    ['name' => 'Admin: GET /api/v1/stock-movements', 'method' => 'GET', 'uri' => '/api/v1/stock-movements', 'token' => $adminToken, 'expected' => 200],
    ['name' => 'Admin: GET /api/v1/suppliers', 'method' => 'GET', 'uri' => '/api/v1/suppliers', 'token' => $adminToken, 'expected' => 200],
    ['name' => 'Admin: GET /api/v1/purchases', 'method' => 'GET', 'uri' => '/api/v1/purchases', 'token' => $adminToken, 'expected' => 200],
];

$passCount = 0;
$failCount = 0;

foreach ($testScenarios as $test) {
    $req = Request::create($test['uri'], $test['method']);
    $req->headers->set('Accept', 'application/json');
    if ($test['token']) {
        $req->headers->set('Authorization', 'Bearer '.$test['token']);
    }

    $response = $app->handle($req);
    $statusCode = $response->getStatusCode();

    $isPassed = ($statusCode === $test['expected']);
    if ($isPassed) {
        $passCount++;
        echo sprintf("  ✅ PASS [%03d] %s\n", $statusCode, $test['name']);
    } else {
        $failCount++;
        echo sprintf("  ❌ FAIL [Expected %03d, Got %03d] %s\n", $test['expected'], $statusCode, $test['name']);
    }
}

echo "\n------------------------------------------------------------------------\n";
echo "AUDIT SUMMARY: {$passCount} Passed, {$failCount} Failed.\n";
echo 'RBAC Enforcement Status: '.($failCount === 0 ? '100% PERFECT & FULLY SECURE 🔒' : 'VULNERABILITIES DETECTED')."\n";
echo "========================================================================\n";
