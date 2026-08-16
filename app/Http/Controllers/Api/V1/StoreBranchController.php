<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\StoreBranch;
use App\Models\StoreInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreBranchController extends Controller
{
    /**
     * List all store branches
     */
    public function index(): JsonResponse
    {
        $branches = StoreBranch::where('is_active', true)->get();

        return response()->json([
            'success' => true,
            'data'    => $branches,
            'message' => 'Store branches retrieved successfully',
        ]);
    }

    /**
     * Get branch inventory levels across all variants
     */
    public function branchStock(int $branchId): JsonResponse
    {
        $branch = StoreBranch::findOrFail($branchId);

        $inventories = StoreInventory::with(['variant.product', 'variant.size', 'variant.color'])
            ->where('branch_id', $branchId)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'branch'      => $branch,
                'inventories' => $inventories,
            ],
            'message' => 'Branch inventory retrieved successfully',
        ]);
    }

    /**
     * Create a new branch (Manager / Admin)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'branch_name'  => 'required|string|max:100',
            'branch_code'  => 'required|string|max:30|unique:store_branches,branch_code',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:100',
            'address'      => 'nullable|string',
            'city'         => 'nullable|string|max:50',
            'is_warehouse' => 'nullable|boolean',
            'is_active'    => 'nullable|boolean',
        ]);

        $branch = StoreBranch::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $branch,
            'message' => 'Store branch created successfully',
        ], 201);
    }
}
