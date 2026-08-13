<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;

class StatusController extends BaseApiController
{
    /**
     * Display the status and version information of the SS-MIS Backend Web API.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        return $this->successResponse([
            'project'     => 'Store Stock & Point-of-Sale Information System',
            'acronym'     => 'SS-MIS',
            'version'     => 'v1.0.0',
            'api_status'  => 'Operational',
            'environment' => config('app.env'),
            'timestamp'   => now()->toIso8601String(),
        ], 'SS-MIS Backend Web API v1 is operational');
    }
}
