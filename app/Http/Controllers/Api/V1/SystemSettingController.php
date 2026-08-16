<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class SystemSettingController extends Controller
{
    /**
     * Get system sound effects and audio cues for POS & UI
     */
    public function audioCues(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'scanner_beep'   => 'https://res.cloudinary.com/od8t271n/video/upload/v1786898754/khmeriel/audio/KHMERIEL_POS_SCANNER_AUDIO_FX_SMS_audio_001.wav',
                'success_chime'  => 'https://res.cloudinary.com/od8t271n/video/upload/v1786898754/khmeriel/audio/KHMERIEL_POS_SCANNER_AUDIO_FX_SOUNDWARNING_audio_002.wav',
                'warning_buzz'   => 'https://res.cloudinary.com/od8t271n/video/upload/v1786898754/khmeriel/audio/KHMERIEL_POS_SCANNER_AUDIO_FX_WARNING_audio_003.wav',
                'brand_name'     => 'KhmeRiel',
                'brand_tagline'  => 'KhmeRiel • Clothing & POS MIS',
                'currency'       => 'USD',
                'currency_khmer' => 'KHR',
                'khr_exchange_rate' => 4100,
            ],
            'message' => 'System audio cues and configuration retrieved',
        ]);
    }
}
