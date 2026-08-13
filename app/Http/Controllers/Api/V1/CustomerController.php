<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Customer;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends BaseApiController
{
    public function index(): JsonResponse
    {
        return $this->successResponse(Customer::all(), 'Customers list');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'required|string|max:150',
            'gender'        => 'nullable|string',
            'phone'         => 'nullable|string',
            'email'         => 'nullable|email',
            'address'       => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        AuditLogService::log('CREATE', 'Customer', $customer->customer_id, null, $customer->toArray());

        return $this->successResponse($customer, 'Customer registered', 201);
    }

    public function show(int $id): JsonResponse
    {
        $customer = Customer::with('sales.details')->findOrFail($id);
        return $this->successResponse($customer, 'Customer details');
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $old = $customer->toArray();

        $validated = $request->validate([
            'customer_name' => 'required|string|max:150',
            'gender'        => 'nullable|string',
            'phone'         => 'nullable|string',
            'email'         => 'nullable|email',
            'address'       => 'nullable|string',
        ]);

        $customer->update($validated);

        AuditLogService::log('UPDATE', 'Customer', $id, $old, $customer->toArray());

        return $this->successResponse($customer, 'Customer details updated');
    }

    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $old = $customer->toArray();
        $customer->delete();

        AuditLogService::log('DELETE', 'Customer', $id, $old, null);

        return $this->successResponse(null, 'Customer record soft deleted');
    }
}
