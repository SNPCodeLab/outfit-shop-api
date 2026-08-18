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
 * Ensures Laravel boots with correct drivers even if Vercel ENV propagation is slow.
 */
$overrides = [
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'true', // Temporarily true for debugging
    'LOG_CHANNEL' => 'stderr',
    'CACHE_STORE' => 'database',
    'CACHE_DRIVER' => 'database',
    'SESSION_DRIVER' => 'database',
    'QUEUE_CONNECTION' => 'database',
    'DB_CONNECTION' => 'pgsql',
];

foreach ($overrides as $key => $value) {
    if (!getenv($key)) {
        putenv("{$key}={$value}");
    }
    $_ENV[$key] = getenv($key) ?: $value;
    $_SERVER[$key] = getenv($key) ?: $value;
}

// Ensure APP_KEY exists
if (!getenv('APP_KEY') && isset($_ENV['APP_KEY'])) {
    putenv("APP_KEY={$_ENV['APP_KEY']}");
}

// Create writable /tmp paths for Laravel storage (Vercel is read-only except /tmp)
$storagePath = '/tmp/storage';
@mkdir($storagePath.'/framework/views', 0755, true);
@mkdir($storagePath.'/framework/sessions', 0755, true);
@mkdir($storagePath.'/framework/cache/data', 0755, true);
@mkdir($storagePath.'/logs', 0755, true);
@mkdir('/tmp/cache', 0755, true);

putenv("APP_STORAGE={$storagePath}");
$_ENV['APP_STORAGE'] = $storagePath;

// Register Autoloader & Bootstrap App
require __DIR__.'/../vendor/autoload.php';

try {
    /** @var Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->useStoragePath($storagePath);
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
