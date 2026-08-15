<?php

// Prepare writable /tmp storage & bootstrap cache paths for Vercel serverless functions
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
putenv("APP_CONFIG_CACHE={$cachePath}/config.php");
putenv("APP_ROUTES_CACHE={$cachePath}/routes.php");
putenv("APP_EVENTS_CACHE={$cachePath}/events.php");

$_ENV['APP_STORAGE'] = $storagePath;
$_ENV['APP_SERVICES_CACHE'] = "{$cachePath}/services.php";
$_ENV['APP_PACKAGES_CACHE'] = "{$cachePath}/packages.php";

$_SERVER['APP_STORAGE'] = $storagePath;
$_SERVER['APP_SERVICES_CACHE'] = "{$cachePath}/services.php";
$_SERVER['APP_PACKAGES_CACHE'] = "{$cachePath}/packages.php";

// Forward request to Laravel public/index.php
require __DIR__ . '/../public/index.php';
