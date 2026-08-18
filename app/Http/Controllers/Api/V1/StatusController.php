<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatusController extends BaseApiController
{
    /**
     * Display the status and version information of the OutfitShop Ecommerce Clothing API.
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
            'system'            => 'OutfitShop Ecommerce Clothing API',
            'version'           => config('api.version', 'v1.0.0'),
            'api_status'        => 'Operational',
            'database'          => $dbStatus,
            'environment'       => config('app.env', 'production'),
            'frontend_url'      => 'https://app.kesararamwithdigital.tech',
            'guide_url'         => url('/guide'),
            'guide_api_url'     => url('/api/v1/guide'),
            'documentation_url' => url('/guide'),
            'brand'             => config('api.brand', [
                'name'           => 'OutfitShop',
                'title'          => 'OutfitShop Ecommerce Clothing API',
                'primary_logo'   => 'https://res.cloudinary.com/od8t271n/image/upload/v1787064621/bleu-SNPCodeLab.png',
                'primary_gif'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1787062663/default-cycle-SNPCodeLab.gif',
                'secondary_logo' => 'https://res.cloudinary.com/od8t271n/image/upload/v1787062664/bleu-SNPCodeLab.gif',
                'vector_logo'    => 'https://res.cloudinary.com/od8t271n/image/upload/v1787062662/anime-SNPCodeLab.svg',
                'video_logo'     => 'https://res.cloudinary.com/od8t271n/video/upload/v1787062665/default-cycle-SNPCodeLab.mp4',
            ]),
            'timestamp'         => now()->toISOString(),
        ];

        // Only expose database error detail in non-production environments.
        if ($dbStatus === 'Disconnected' && config('app.debug')) {
            $data['db_error'] = $dbError;
        }

        return $this->successResponse($data, 'OutfitShop API is operational');
    }
}
