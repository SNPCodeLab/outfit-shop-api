<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatusController extends BaseApiController
{
    /**
     * Display the status and version information of the SS-MIS Backend Web API.
     *
     * @return JsonResponse
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
            'system'            => 'Store Stock & Point-of-Sale MIS API',
            'version'           => 'v1.0.0',
            'api_status'        => 'Operational',
            'database'          => $dbStatus,
            'environment'       => config('app.env', 'production'),
            'frontend_url'      => 'https://app.kesararamwithdigital.tech',
            'guide_url'         => url('/guide'),
            'guide_api_url'     => url('/api/v1/guide'),
            'documentation_url' => url('/guide'),
            'timestamp'         => now()->toIso8601String(),
        ];

        if ($dbStatus === 'Disconnected') {
            $data['db_error'] = $dbError;
        }

        return $this->successResponse($data, 'API is operational');
    }
}
