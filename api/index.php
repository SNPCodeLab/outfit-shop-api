<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

define('LARAVEL_START', microtime(true));

// Silence PHP 8 deprecation notices
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');

/**
 * Vercel Serverless Environment Hardening
 */
$storagePath = '/tmp/storage';
$cachePath = '/tmp/cache';
@mkdir($storagePath.'/framework/views', 0755, true);
@mkdir($storagePath.'/framework/sessions', 0755, true);
@mkdir($storagePath.'/framework/cache/data', 0755, true);
@mkdir($storagePath.'/logs', 0755, true);
@mkdir($cachePath, 0755, true);

// 1. Force Application Encryption Key
$appKey = getenv('APP_KEY') ?: ($_ENV['APP_KEY'] ?? $_SERVER['APP_KEY'] ?? '');
if (empty($appKey) || strlen($appKey) < 32) {
    $appKey = 'base64:jRg4MlzbF1E+N+h86+fGqkM+8/BxWNmbu+Hvk0UWHSg=';
}
putenv("APP_KEY=$appKey");
$_ENV['APP_KEY'] = $appKey;
$_SERVER['APP_KEY'] = $appKey;

// 2. Database Discovery (Neon / Vercel)
$dbUrl = getenv('DATABASE_URL') ?: (getenv('POSTGRES_URL') ?: 'postgresql://neondb_owner:npg_SsC0GRvWm1Bz@ep-blue-mode-avbaa8zy-pooler.c-11.us-east-1.aws.neon.tech/neondb?sslmode=require&channel_binding=require');

if ($dbUrl && str_contains($dbUrl, '://')) {
    $parsedUrl = parse_url($dbUrl);
    $query = [];
    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $query);
    }

    $dbConfig = [
        'DB_CONNECTION' => 'pgsql',
        'DB_HOST' => $parsedUrl['host'] ?? null,
        'DB_PORT' => $parsedUrl['port'] ?? 5432,
        'DB_DATABASE' => ltrim($parsedUrl['path'] ?? '', '/'),
        'DB_USERNAME' => $parsedUrl['user'] ?? null,
        'DB_PASSWORD' => $parsedUrl['pass'] ?? null,
        'DB_SSLMODE' => $query['sslmode'] ?? 'require',
    ];

    foreach ($dbConfig as $key => $value) {
        if ($value !== null) {
            putenv("{$key}={$value}");
            $_ENV[$key] = (string) $value;
            $_SERVER[$key] = (string) $value;
        }
    }

    // Force clear noisy variables
    putenv('DATABASE_URL=');
    putenv('DB_URL=');
}

// 3. Set App Overrides
$overrides = [
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'true',
    'LOG_CHANNEL' => 'stderr',
    'CACHE_STORE' => 'database',
    'SESSION_DRIVER' => 'database',
    'QUEUE_CONNECTION' => 'database',
    'APP_STORAGE' => $storagePath,
    'APP_SERVICES_CACHE' => $cachePath.'/services.php',
    'APP_PACKAGES_CACHE' => $cachePath.'/packages.php',
    'APP_CONFIG_CACHE' => $cachePath.'/config.php',
    'APP_ROUTES_CACHE' => $cachePath.'/routes.php',
    'APP_EVENTS_CACHE' => $cachePath.'/events.php',
];

foreach ($overrides as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}

// Register Autoloader & Bootstrap App
require __DIR__.'/../vendor/autoload.php';

try {
    /** @var Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->useStoragePath($storagePath);
    $app->useBootstrapPath($cachePath);

    Facade::setFacadeApplication($app);

    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
    $_SERVER['ORIG_SCRIPT_NAME'] = '/index.php';

    $app->handleRequest(Request::capture());
} catch (Throwable $e) {
    if (! headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => false,
        'message' => 'Vercel Laravel Boot Error',
        'error' => $e->getMessage(),
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
