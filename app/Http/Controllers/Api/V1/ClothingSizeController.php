<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ClothingSize;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClothingSizeController extends BaseApiController
{
    public function index(): JsonResponse
    {
        return $this->successResponse(ClothingSize::all(), 'Clothing sizes retrieved');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'size_name' => 'required|string|unique:clothing_sizes,size_name',
            'size_code' => 'nullable|string|max:20',
            'description' => 'nullable|string',
        ]);

        $size = ClothingSize::create($validated);

        AuditLogService::log('CREATE', 'ClothingSize', $size->size_id, null, $size->toArray());

        return $this->createdResponse($size, 'Clothing size created successfully', '/api/v1/clothing-sizes/'.$size->size_id);
    }

    public function show(int $id): JsonResponse
    {
        $size = ClothingSize::findOrFail($id);

        return $this->successResponse($size, 'Clothing size details');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $size = ClothingSize::findOrFail($id);
        $old = $size->toArray();

        $validated = $request->validate([
            'size_name' => 'sometimes|required|string|unique:clothing_sizes,size_name,'.$id.',size_id',
            'description' => 'nullable|string',
        ]);

        $size->update($validated);

        AuditLogService::log('UPDATE', 'ClothingSize', $id, $old, $size->toArray());

        return $this->successResponse($size, 'Clothing size updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $size = ClothingSize::withCount('variants')->findOrFail($id);

        if ($size->variants_count > 0) {
            return $this->conflictResponse(
                'Cannot delete size that is assigned to product variants.',
                'SIZE_HAS_VARIANTS'
            );
        }

        $old = $size->toArray();
        $size->delete();

        AuditLogService::log('DELETE', 'ClothingSize', $id, $old, null);

        return $this->successResponse(null, 'Clothing size deleted');
    }
}
