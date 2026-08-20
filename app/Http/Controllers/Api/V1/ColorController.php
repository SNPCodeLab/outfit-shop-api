<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Color;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ColorController extends BaseApiController
{
    public function index(): JsonResponse
    {
        return $this->successResponse(Color::all(), 'Colors retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'color_name' => 'required|string|unique:colors,color_name',
            'hex_code' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        $color = Color::create($validated);

        AuditLogService::log('CREATE', 'Color', $color->color_id, null, $color->toArray());

        return $this->createdResponse($color, 'Color created successfully', '/api/v1/colors/'.$color->color_id);
    }

    public function show(int $id): JsonResponse
    {
        $color = Color::findOrFail($id);

        return $this->successResponse($color, 'Color details');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $color = Color::findOrFail($id);
        $old = $color->toArray();

        $validated = $request->validate([
            'color_name' => 'required|string|unique:colors,color_name,'.$id.',color_id',
            'description' => 'nullable|string',
        ]);

        $color->update($validated);

        AuditLogService::log('UPDATE', 'Color', $id, $old, $color->toArray());

        return $this->successResponse($color, 'Color updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $color = Color::withCount('variants')->findOrFail($id);

        if ($color->variants_count > 0) {
            return $this->conflictResponse(
                'Cannot delete color that is assigned to product variants.',
                'COLOR_HAS_VARIANTS'
            );
        }

        $old = $color->toArray();
        $color->delete();

        AuditLogService::log('DELETE', 'Color', $id, $old, null);

        return $this->successResponse(null, 'Color deleted');
    }
}
