<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;

class SystemSettingController extends BaseApiController
{
    /**
     * Get system sound effects and audio cues for POS and frontend UI.
     * Public - no authentication required.
     */
    public function audioCues(): JsonResponse
    {
        return $this->successResponse([
            'scanner_beep' => 'https://res.cloudinary.com/od8t271n/video/upload/v1786898754/khmeriel/audio/KHMERIEL_POS_SCANNER_AUDIO_FX_SMS_audio_001.wav',
            'success_chime' => 'https://res.cloudinary.com/od8t271n/video/upload/v1786898754/khmeriel/audio/KHMERIEL_POS_SCANNER_AUDIO_FX_SOUNDWARNING_audio_002.wav',
            'warning_buzz' => 'https://res.cloudinary.com/od8t271n/video/upload/v1786898754/khmeriel/audio/KHMERIEL_POS_SCANNER_AUDIO_FX_WARNING_audio_003.wav',
            'currency_primary' => 'USD',
            'currency_secondary' => 'KHR',
            'khr_exchange_rate' => 4100,
        ], 'System audio cues and POS configuration retrieved');
    }
}
