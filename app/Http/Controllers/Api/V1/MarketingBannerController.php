<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MarketingBanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingBannerController extends Controller
{
    /**
     * List active marketing banners for storefront
     */
    public function index(Request $request): JsonResponse
    {
        $query = MarketingBanner::active();

        if ($placement = $request->input('placement')) {
            $query->where('placement', strtoupper($placement));
        }

        if ($dept = $request->input('department')) {
            $query->where(function ($q) use ($dept) {
                $q->where('target_department', strtoupper($dept))
                  ->orWhereNull('target_department')
                  ->orWhere('target_department', 'ALL');
            });
        }

        $banners = $query->get();

        return response()->json([
            'success' => true,
            'data'    => $banners,
            'message' => 'Marketing banners retrieved successfully',
        ]);
    }

    /**
     * Create a new marketing banner (Manager / Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title'             => 'required|string|max:150',
            'subtitle'          => 'nullable|string|max:255',
            'image_url'         => 'required|url|max:500',
            'image_public_id'   => 'nullable|string|max:255',
            'link_url'          => 'nullable|string|max:500',
            'placement'         => 'nullable|string|in:HERO_SLIDER,PROMO_CARD,SECTION_BANNER,POPUP',
            'target_department' => 'nullable|string|max:50',
            'sort_order'        => 'nullable|integer',
            'is_active'         => 'nullable|boolean',
        ]);

        $banner = MarketingBanner::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $banner,
            'message' => 'Marketing banner created successfully',
        ], 201);
    }

    /**
     * Delete a marketing banner
     */
    public function destroy(int $bannerId): JsonResponse
    {
        $banner = MarketingBanner::findOrFail($bannerId);
        $banner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Marketing banner deleted successfully',
        ]);
    }
}
