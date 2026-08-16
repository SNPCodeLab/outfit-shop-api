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
            'brand'       => 'KhmeRiel',
            'brand_logo'  => 'https://res.cloudinary.com/od8t271n/image/upload/v1786898754/KhmerRiel.png',
            'project'     => 'KhmeRiel Store Stock & Point-of-Sale MIS',
            'acronym'     => 'SS-MIS',
            'version'     => 'v1.0.0',
            'api_status'  => 'Operational',
            'database'    => $dbStatus,
            'environment' => config('app.env', 'production'),
            'frontend_url'=> 'https://app.kesararamwithdigital.tech',
            'timestamp'   => now()->toIso8601String(),
        ];

        if ($dbStatus === 'Disconnected') {
            $data['db_error'] = $dbError;
        }

        return $this->successResponse($data, 'SS-MIS Backend Web API v1 is operational');
    }
}
