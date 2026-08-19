<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Request;

require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = LoginRequest::create('/api/v1/auth/login', 'POST', [
    'username' => 'admin',
    'password' => 'Admin#Secure#2026'
]);

// Manually set up the request
$request->setMethod('POST');
$request->request->add([
    'username' => 'admin',
    'password' => 'Admin#Secure#2026'
]);

try {
    $response = $kernel->handle($request);
    echo "Status: " . $response->getStatusCode() . "\n";
    echo "Body: " . $response->getContent() . "\n";
} catch (\Throwable $e) {
    echo "Caught: " . get_class($e) . ": " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
