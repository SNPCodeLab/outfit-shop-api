<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Supplier;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::query();

        if ($search = $request->input('q') ?? $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('supplier_name', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', strtoupper($status));
        }

        $perPage = (int) $request->input('per_page', 50);
        $suppliers = $query->orderBy('supplier_id', 'desc')->paginate($perPage);

        return $this->successResponse($suppliers, 'Suppliers retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:150',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'status' => 'nullable|string|in:ACTIVE,INACTIVE',
        ]);

        $supplier = Supplier::create($validated);

        AuditLogService::log('CREATE', 'Supplier', $supplier->supplier_id, null, $supplier->toArray());

        return $this->createdResponse($supplier, 'Supplier created successfully', '/api/v1/suppliers/'.$supplier->supplier_id);
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
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'status' => 'nullable|string|in:ACTIVE,INACTIVE',
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
