<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\MarketingBanner;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingBannerController extends BaseApiController
{
    /**
     * List active marketing banners for the storefront.
     * Supports optional ?placement= and ?department= filters.
     * Public - no authentication required.
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

        $banners = $query->orderBy('sort_order', 'asc')->get();

        return $this->successResponse($banners, 'Marketing banners retrieved successfully');
    }

    /**
     * Create a new marketing banner.
     * Restricted to MANAGER or ADMIN.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'subtitle' => 'nullable|string|max:255',
            'image_url' => 'required|url|max:500',
            'image_public_id' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:500',
            'placement' => 'nullable|string|in:HERO_SLIDER,PROMO_CARD,SECTION_BANNER,POPUP',
            'target_department' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $banner = MarketingBanner::create($validated);

        AuditLogService::log('CREATE', 'MarketingBanner', $banner->banner_id, null, $banner->toArray());

        return $this->createdResponse($banner, 'Marketing banner created successfully', '/api/v1/marketing/banners/'.$banner->banner_id);
    }

    /**
     * Delete a marketing banner.
     * Restricted to MANAGER or ADMIN.
     */
    public function destroy(int $bannerId): JsonResponse
    {
        $banner = MarketingBanner::findOrFail($bannerId);

        AuditLogService::log('DELETE', 'MarketingBanner', $bannerId, $banner->toArray(), null);

        $banner->delete();

        return $this->deletedResponse('Marketing banner deleted successfully');
    }
}
