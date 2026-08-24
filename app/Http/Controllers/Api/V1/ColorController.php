<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\UpdateColorRequest;
use App\Models\Color;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ColorController extends BaseApiController
{
    public function index(): JsonResponse
    {
        $colors = Color::orderBy('color_name', 'asc')->get();

        return $this->successResponse($colors, 'Colors retrieved successfully.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'color_name' => 'required|string|max:50|unique:colors,color_name',
            'hex_code' => ['required', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'pantone' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $color = Color::create($validated);

        AuditLogService::log('CREATE', 'Color', $color->color_id, null, $color->toArray());

        return $this->createdResponse($color, 'Color created successfully.', '/api/v1/colors/'.$color->color_id);
    }

    public function show(int $id): JsonResponse
    {
        $color = Color::findOrFail($id);

        return $this->successResponse($color, 'Color details');
    }

    public function update(UpdateColorRequest $request, int $id): JsonResponse
    {
        $color = Color::findOrFail($id);
        $old = $color->toArray();

        $color->update($request->validated());

        AuditLogService::log('UPDATE', 'Color', $id, $old, $color->toArray());

        return $this->successResponse($color, 'Color updated successfully.');
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

        return $this->successResponse(null, 'Color deleted successfully.');
    }
}
