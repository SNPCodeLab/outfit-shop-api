<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Supplier;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends BaseApiController
{
    public function index(): JsonResponse
    {
        return $this->successResponse(Supplier::all(), 'Suppliers list');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:150',
            'phone'         => 'nullable|string',
            'email'         => 'nullable|email',
            'address'       => 'nullable|string',
            'status'        => 'nullable|string|in:ACTIVE,INACTIVE',
        ]);

        $supplier = Supplier::create($validated);

        AuditLogService::log('CREATE', 'Supplier', $supplier->supplier_id, null, $supplier->toArray());

        return $this->successResponse($supplier, 'Supplier created', 201);
    }

    public function show(int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        return $this->successResponse($supplier, 'Supplier details');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        $old = $supplier->toArray();

        $validated = $request->validate([
            'supplier_name' => 'required|string|max:150',
            'phone'         => 'nullable|string',
            'email'         => 'nullable|email',
            'address'       => 'nullable|string',
            'status'        => 'nullable|string|in:ACTIVE,INACTIVE',
        ]);

        $supplier->update($validated);

        AuditLogService::log('UPDATE', 'Supplier', $id, $old, $supplier->toArray());

        return $this->successResponse($supplier, 'Supplier updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $supplier = Supplier::findOrFail($id);
        $old = $supplier->toArray();
        $supplier->delete();

        AuditLogService::log('DELETE', 'Supplier', $id, $old, null);

        return $this->successResponse(null, 'Supplier deleted');
    }
}
