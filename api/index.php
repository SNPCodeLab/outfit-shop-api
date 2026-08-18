<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

define('LARAVEL_START', microtime(true));

// Silence PHP 8 deprecation notices
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');

// Environment Overrides for Vercel Serverless
putenv('LOG_CHANNEL=stderr');
putenv('LOG_LEVEL=debug');
putenv('CACHE_STORE=database');
putenv('SESSION_DRIVER=database');
putenv('APP_DEBUG=true');
$_ENV['APP_DEBUG'] = 'true';

if (! getenv('APP_DEBUG')) {
    putenv('APP_DEBUG=true');
    $_ENV['APP_DEBUG'] = 'true';
}

if (! getenv('APP_KEY') && isset($_ENV['APP_KEY'])) {
    putenv("APP_KEY={$_ENV['APP_KEY']}");
}

// Ensure database connection is set correctly for Vercel/Neon
if (getenv('DATABASE_URL')) {
    putenv('DB_CONNECTION=pgsql');
    $_ENV['DB_CONNECTION'] = 'pgsql';
}

// Create writable /tmp paths for Laravel storage
$storagePath = '/tmp/storage';
@mkdir($storagePath.'/framework/views', 0755, true);
@mkdir($storagePath.'/framework/sessions', 0755, true);
@mkdir($storagePath.'/framework/cache/data', 0755, true);
@mkdir($storagePath.'/logs', 0755, true);
@mkdir('/tmp/cache', 0755, true);

putenv("APP_STORAGE={$storagePath}");
putenv('APP_SERVICES_CACHE=/tmp/cache/services.php');
putenv('APP_PACKAGES_CACHE=/tmp/cache/packages.php');
putenv('APP_CONFIG_CACHE=/tmp/cache/config.php');
putenv('APP_ROUTES_CACHE=/tmp/cache/routes.php');
putenv('APP_EVENTS_CACHE=/tmp/cache/events.php');

$_ENV['APP_STORAGE'] = $storagePath;

// Register Autoloader & Bootstrap App
require __DIR__.'/../vendor/autoload.php';

try {
    /** @var Application $app */
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $app->useStoragePath($storagePath);
    Facade::setFacadeApplication($app);

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
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => explode("\n", $e->getTraceAsString()),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
