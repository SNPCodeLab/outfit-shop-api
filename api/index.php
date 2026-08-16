<?php

define('LARAVEL_START', microtime(true));

// Silence PHP 8 deprecation notices
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '0');

// Environment Overrides for Vercel Serverless
putenv('LOG_CHANNEL=stderr');
putenv('LOG_LEVEL=error');
putenv('CACHE_STORE=array');
putenv('CACHE_DRIVER=array');
putenv('SESSION_DRIVER=cookie');
putenv('APP_MAINTENANCE_DRIVER=file');
putenv('APP_MAINTENANCE_STORE=array');

$_ENV['LOG_CHANNEL']            = 'stderr';
$_ENV['CACHE_STORE']            = 'array';
$_ENV['CACHE_DRIVER']           = 'array';
$_ENV['SESSION_DRIVER']         = 'cookie';
$_ENV['APP_MAINTENANCE_DRIVER'] = 'file';
$_ENV['APP_MAINTENANCE_STORE']  = 'array';

if (!getenv('APP_KEY')) {
    putenv('APP_KEY=base64:bSQmNjhGdsJcRUWtzZqs50fNJf5uQVe80BuOfTV6uLk=');
    $_ENV['APP_KEY'] = 'base64:bSQmNjhGdsJcRUWtzZqs50fNJf5uQVe80BuOfTV6uLk=';
}

if (!getenv('DB_CONNECTION') || trim((string)getenv('DB_CONNECTION')) === '') {
    putenv('DB_CONNECTION=pgsql');
    $_ENV['DB_CONNECTION'] = 'pgsql';
}

// Create writable /tmp paths
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

$_ENV['APP_STORAGE'] = $storagePath;

// Register Autoloader & Bootstrap App
require __DIR__ . '/../vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require_once __DIR__ . '/../bootstrap/app.php';

$app->useStoragePath($storagePath);
\Illuminate\Support\Facades\Facade::setFacadeApplication($app);

$_SERVER['SCRIPT_NAME']      = '/index.php';
$_SERVER['PHP_SELF']         = '/index.php';
$_SERVER['ORIG_SCRIPT_NAME'] = '/index.php';

$app->handleRequest(\Illuminate\Http\Request::capture());
