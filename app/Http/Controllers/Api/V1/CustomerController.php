<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Customer;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::query();

        if ($search = $request->input('q') ?? $request->input('search')) {
            $search = $this->escapeLike((string) $search);
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'ILIKE', "%{$search}%")
                    ->orWhere('phone', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        $perPage = $this->perPage($request, 50);
        $customers = $query->orderBy('customer_id', 'desc')->paginate($perPage);

        return $this->successResponse($customers, 'Customers retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_name' => 'sometimes|required|string|max:150',
            'gender' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'loyalty_points' => 'nullable|integer|min:0',
            'loyalty_tier' => 'nullable|string|max:50',
            'vip_tier' => 'nullable|string|max:50',
            'total_spent_lifetime' => 'nullable|numeric|min:0',
            'store_credit_balance' => 'nullable|numeric|min:0',
        ]);

        $customer = Customer::create($validated);

        AuditLogService::log('CREATE', 'Customer', $customer->customer_id, null, $customer->toArray());

        return $this->createdResponse($customer, 'Customer registered successfully', '/api/v1/customers/'.$customer->customer_id);
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
            'customer_name' => 'sometimes|required|string|max:150',
            'gender' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'address' => 'nullable|string',
            'loyalty_points' => 'nullable|integer|min:0',
            'loyalty_tier' => 'nullable|string|max:50',
            'vip_tier' => 'nullable|string|max:50',
            'total_spent_lifetime' => 'nullable|numeric|min:0',
            'store_credit_balance' => 'nullable|numeric|min:0',
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
