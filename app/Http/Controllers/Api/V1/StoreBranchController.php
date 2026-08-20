<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\StoreBranch;
use App\Models\StoreInventory;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreBranchController extends BaseApiController
{
    /**
     * List all active store branches.
     * Public - no authentication required.
     */
    public function index(): JsonResponse
    {
        $branches = StoreBranch::whereRaw('is_active is true')->get();

        return $this->successResponse($branches, 'Store branches retrieved successfully');
    }

    /**
     * Get branch inventory levels across all variants.
     * Restricted to MANAGER or ADMIN.
     */
    public function branchStock(int $branchId): JsonResponse
    {
        $branch = StoreBranch::findOrFail($branchId);

        $inventories = StoreInventory::with([
            'variant.product',
            'variant.size',
            'variant.color',
        ])
            ->where('branch_id', $branchId)
            ->get();

        return $this->successResponse([
            'branch' => $branch,
            'inventories' => $inventories,
        ], 'Branch inventory retrieved successfully');
    }

    /**
     * Create a new store branch.
     * Restricted to MANAGER or ADMIN.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_name' => 'required|string|max:100',
            'branch_code' => 'required|string|max:30',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:50',
            'is_warehouse' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $existing = StoreBranch::where('branch_code', $validated['branch_code'])->first();
        if ($existing) {
            return $this->successResponse($existing, 'Store branch already exists');
        }

        $branch = StoreBranch::create($validated);

        AuditLogService::log('CREATE', 'StoreBranch', $branch->branch_id, null, $branch->toArray());

        return $this->createdResponse($branch, 'Store branch created successfully', '/api/v1/branches/'.$branch->branch_id);
    }
}
