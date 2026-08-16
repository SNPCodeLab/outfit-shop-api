<?php

/*
|--------------------------------------------------------------------------
| Vercel Serverless Entry Point (KhmeRiel MIS & POS API)
|--------------------------------------------------------------------------
|
| Bootstraps Laravel 11 for Vercel's read-only serverless AWS Lambda environment.
| All environment overrides, deprecation filters, and writable /tmp paths are set
| BEFORE the Composer autoloader and Laravel application are loaded.
|
*/

define('LARAVEL_START', microtime(true));

// Silence PHP 8.x PDO deprecation notices so they do not corrupt HTTP JSON output
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');

// ─── 1. Vercel Environment Overrides (MUST run before autoloader) ─────────
putenv('LOG_CHANNEL=stderr');
putenv('LOG_LEVEL=error');
putenv('CACHE_STORE=array');
putenv('CACHE_DRIVER=array');
putenv('SESSION_DRIVER=cookie');
putenv('APP_MAINTENANCE_DRIVER=file');
putenv('APP_MAINTENANCE_STORE=array');

$_ENV['LOG_CHANNEL']            = 'stderr';
$_ENV['LOG_LEVEL']              = 'error';
$_ENV['CACHE_STORE']            = 'array';
$_ENV['CACHE_DRIVER']           = 'array';
$_ENV['SESSION_DRIVER']         = 'cookie';
$_ENV['APP_MAINTENANCE_DRIVER'] = 'file';
$_ENV['APP_MAINTENANCE_STORE']  = 'array';

$_SERVER['LOG_CHANNEL']            = 'stderr';
$_SERVER['CACHE_STORE']            = 'array';
$_SERVER['CACHE_DRIVER']           = 'array';
$_SERVER['SESSION_DRIVER']         = 'cookie';
$_SERVER['APP_MAINTENANCE_DRIVER'] = 'file';
$_SERVER['APP_MAINTENANCE_STORE']  = 'array';

if (!getenv('APP_KEY')) {
    putenv('APP_KEY=base64:bSQmNjhGdsJcRUWtzZqs50fNJf5uQVe80BuOfTV6uLk=');
    $_ENV['APP_KEY']    = 'base64:bSQmNjhGdsJcRUWtzZqs50fNJf5uQVe80BuOfTV6uLk=';
    $_SERVER['APP_KEY'] = 'base64:bSQmNjhGdsJcRUWtzZqs50fNJf5uQVe80BuOfTV6uLk=';
}

if (!getenv('DB_CONNECTION')) {
    putenv('DB_CONNECTION=pgsql');
}

// Normalize SCRIPT_NAME so Laravel doesn't strip /api from request URIs in serverless
$_SERVER['SCRIPT_NAME']     = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';

// ─── 2. Create writable /tmp directory structure ───────────────────────────
$storagePath = '/tmp/storage';

@mkdir($storagePath . '/framework/views',      0755, true);
@mkdir($storagePath . '/framework/sessions',   0755, true);
@mkdir($storagePath . '/framework/cache/data', 0755, true);
@mkdir($storagePath . '/logs',                 0755, true);
@mkdir('/tmp/cache',                            0755, true);

putenv("APP_STORAGE={$storagePath}");
putenv("APP_SERVICES_CACHE=/tmp/cache/services.php");
putenv("APP_PACKAGES_CACHE=/tmp/cache/packages.php");
putenv("APP_CONFIG_CACHE=/tmp/cache/config.php");
putenv("APP_ROUTES_CACHE=/tmp/cache/routes.php");
putenv("APP_EVENTS_CACHE=/tmp/cache/events.php");

$_ENV['APP_STORAGE']         = $storagePath;
$_SERVER['APP_STORAGE']      = $storagePath;

// ─── 3. Bootstrap & Dispatch ───────────────────────────────────────────────
try {
    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        throw new \RuntimeException('Composer vendor directory not found. Please run composer install.');
    }
    require $autoloadPath;

    /** @var \Illuminate\Foundation\Application $app */
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // Override storage path BEFORE any logging or framework file operations
    $app->useStoragePath($storagePath);

    \Illuminate\Support\Facades\Facade::setFacadeApplication($app);

    // Laravel 11 handleRequest executes and sends the response internally
    $app->handleRequest(\Illuminate\Http\Request::capture());

} catch (\Throwable $e) {
    // Last-resort: always return clean JSON — never expose a PHP fatal page
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success'    => false,
        'message'    => $e->getMessage(),
        'error_code' => 'ERR_BOOTSTRAP_FAILURE',
        'file'       => $e->getFile(),
        'line'       => $e->getLine(),
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
