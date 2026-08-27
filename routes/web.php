<?php

declare(strict_types=1);

use App\Http\Controllers\Web\StatusController;
use App\Http\Response\ApiResponse;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — OutfitShop API Gateway Root Entrypoint
|--------------------------------------------------------------------------
|
| This file serves the JSON root index and the system status dashboard.
| All API routes are defined in routes/api.php under the /api/v1 prefix.
|
*/

Route::get('/', function () {
    return ApiResponse::success([
        'system' => 'OutfitShop-Backend-API',
        'version' => config('api.version', 'Version: 1.2.0'),
        'status' => 'online',
    ], 'OutfitShop-Backend-API gateway is operational');
});

// System Status Dashboard (UI Page)
Route::get('/status', [StatusController::class, 'index'])->name('web.status');
