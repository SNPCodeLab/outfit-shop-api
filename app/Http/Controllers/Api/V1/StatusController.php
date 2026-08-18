<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatusController extends BaseApiController
{
    /**
     * Display the status and version information of the OutfitShop Ecommerce Clothing API.
     */
    public function index(): JsonResponse
    {
        $dbStatus = 'Connected';
        $dbError = null;

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $dbStatus = 'Disconnected';
            $dbError = $e->getMessage();
        }

        $data = [
            'system' => 'OutfitShop Ecommerce Clothing API',
            'version' => config('api.version', 'v1.0.0'),
            'api_status' => 'Operational',
            'database' => $dbStatus,
            'environment' => config('app.env', 'production'),
            'frontend_url' => 'https://app.kesararamwithdigital.tech',
            'guide_url' => url('/guide'),
            'guide_api_url' => url('/api/v1/guide'),
            'documentation_url' => url('/guide'),
            'timestamp' => now()->toISOString(),
        ];

        // Only expose database error detail in non-production environments.
        if ($dbStatus === 'Disconnected' && config('app.debug')) {
            $data['db_error'] = $dbError;
        }

        return $this->successResponse($data, 'OutfitShop API is operational');
    }
}
