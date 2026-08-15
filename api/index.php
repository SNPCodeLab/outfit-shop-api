<?php

// Prepare writable /tmp storage paths for Vercel serverless functions
$storagePath = '/tmp/storage';
@mkdir($storagePath . '/framework/views', 0755, true);
@mkdir($storagePath . '/framework/sessions', 0755, true);
@mkdir($storagePath . '/framework/cache', 0755, true);
@mkdir($storagePath . '/logs', 0755, true);

putenv("APP_STORAGE={$storagePath}");
$_ENV['APP_STORAGE'] = $storagePath;
$_SERVER['APP_STORAGE'] = $storagePath;

// Forward request to Laravel public/index.php
require __DIR__ . '/../public/index.php';
