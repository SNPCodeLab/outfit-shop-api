<?php

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

$overrides = [
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'true',
    'LOG_CHANNEL' => 'stderr',
    'CACHE_STORE' => 'database',
    'CACHE_DRIVER' => 'database',
    'SESSION_DRIVER' => 'database',
    'QUEUE_CONNECTION' => 'database',
    'DB_CONNECTION' => 'pgsql',
    'APP_STORAGE' => $storagePath,
    'APP_SERVICES_CACHE' => $cachePath.'/services.php',
    'APP_PACKAGES_CACHE' => $cachePath.'/packages.php',
    'APP_CONFIG_CACHE' => $cachePath.'/config.php',
    'APP_ROUTES_CACHE' => $cachePath.'/routes.php',
    'APP_EVENTS_CACHE' => $cachePath.'/events.php',
];

// Emergency Fallback Key if Vercel ENV is missing
if (!getenv('APP_KEY') && !isset($_ENV['APP_KEY']) && !isset($_SERVER['APP_KEY'])) {
    $overrides['APP_KEY'] = 'base64:jRg4MlzbF1E+N+h86+fGqkM+8/BxWNmbu+Hvk0UWHSg=';
}

foreach ($overrides as $key => $value) {
    if (!getenv($key)) {
        putenv("{$key}={$value}");
    }
    $_ENV[$key] = $_ENV[$key] ?? $value;
    $_SERVER[$key] = $_SERVER[$key] ?? $value;
}

// Register Autoloader & Bootstrap App
require __DIR__.'/../vendor/autoload.php';

try {
    /** @var Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->useStoragePath($storagePath);
    $app->useBootstrapPath($cachePath);

    Facade::setFacadeApplication($app);

    // Standardize Server Variables for Vercel
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    $_SERVER['PHP_SELF'] = '/index.php';
    $_SERVER['ORIG_SCRIPT_NAME'] = '/index.php';

    $app->handleRequest(Request::capture());
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Vercel Laravel Boot Error',
        'error' => $e->getMessage(),
        'exception' => get_class($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => explode("\n", $e->getTraceAsString()),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
