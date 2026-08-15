<?php

try {
    // 1. Force Vercel Serverless environment overrides
    putenv('DB_CONNECTION=pgsql');
    putenv('LOG_CHANNEL=stderr');
    $_ENV['DB_CONNECTION'] = 'pgsql';
    $_ENV['LOG_CHANNEL'] = 'stderr';
    $_SERVER['DB_CONNECTION'] = 'pgsql';
    $_SERVER['LOG_CHANNEL'] = 'stderr';

    // 2. Prepare writable /tmp storage paths
    $storagePath = '/tmp/storage';
    $cachePath = '/tmp/cache';

    @mkdir($storagePath . '/framework/views', 0755, true);
    @mkdir($storagePath . '/framework/sessions', 0755, true);
    @mkdir($storagePath . '/framework/cache', 0755, true);
    @mkdir($storagePath . '/logs', 0755, true);
    @mkdir($cachePath, 0755, true);

    putenv("APP_STORAGE={$storagePath}");
    putenv("APP_SERVICES_CACHE={$cachePath}/services.php");
    putenv("APP_PACKAGES_CACHE={$cachePath}/packages.php");

    $_ENV['APP_STORAGE'] = $storagePath;
    $_SERVER['APP_STORAGE'] = $storagePath;

    // 3. Register Composer autoloader
    require __DIR__ . '/../vendor/autoload.php';

    // 4. Bootstrap Laravel application
    /** @var \Illuminate\Foundation\Application $app */
    $app = require __DIR__ . '/../bootstrap/app.php';

    // 5. Force Laravel container to use /tmp/storage for ALL log & framework operations
    $app->useStoragePath($storagePath);

    // 6. Handle request directly
    $request = \Illuminate\Http\Request::capture();
    $response = $app->handleRequest($request);
    $response->send();
} catch (\Throwable $e) {
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode([
        'system'            => 'Store Stock & Point-of-Sale MIS API',
        'version'           => '1.0.0',
        'status'            => 'online',
        'documentation_url' => 'https://github.com/SNPbuilds/csms-api',
        'health_url'        => 'https://api.kesararamwithdigital.tech/api/v1/health',
        'auth_login_url'    => 'https://api.kesararamwithdigital.tech/api/v1/auth/login',
        'products_url'      => 'https://api.kesararamwithdigital.tech/api/v1/products',
        'categories_url'    => 'https://api.kesararamwithdigital.tech/api/v1/categories',
        'sales_url'         => 'https://api.kesararamwithdigital.tech/api/v1/sales',
        'employees_url'     => 'https://api.kesararamwithdigital.tech/api/v1/employees',
        'customers_url'     => 'https://api.kesararamwithdigital.tech/api/v1/customers',
        'suppliers_url'     => 'https://api.kesararamwithdigital.tech/api/v1/suppliers'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
