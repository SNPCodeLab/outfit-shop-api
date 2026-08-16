<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SaleHeader;
use App\Models\ShippingOrder;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShippingOrderController extends Controller
{
    /**
     * List all shipping & click-and-collect orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = ShippingOrder::with(['sale.customer', 'branch']);

        if ($status = $request->input('status')) {
            $query->where('status', strtoupper($status));
        }

        if ($type = $request->input('type')) {
            $query->where('fulfillment_type', strtoupper($type));
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data'    => $orders,
            'message' => 'Fulfillment & shipping orders retrieved successfully',
        ]);
    }

    /**
     * Create shipping order or in-store pickup for a sale
     */
    public function create(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sale_id'          => 'required|exists:sale_headers,sale_id',
            'branch_id'        => 'nullable|exists:store_branches,branch_id',
            'fulfillment_type' => 'required|string|in:IN_STORE_PICKUP,COURIER_DELIVERY',
            'courier_name'     => 'nullable|string|in:VIRAK_BUNTHAM,J_AND_T,GRAB_EXPRESS,OWN_DELIVERY',
            'tracking_number'  => 'nullable|string|max:100',
            'recipient_name'   => 'required|string|max:100',
            'recipient_phone'  => 'required|string|max:30',
            'shipping_address' => 'nullable|string',
            'shipping_city'    => 'nullable|string|max:50',
            'shipping_cost'    => 'nullable|numeric|min:0',
        ]);

        $order = ShippingOrder::create($validated);

        return response()->json([
            'success' => true,
            'data'    => $order,
            'message' => 'Shipping / Click-and-Collect order created',
        ], 201);
    }

    /**
     * Update courier shipping status (PACKED, DISPATCHED, DELIVERED)
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $order = ShippingOrder::findOrFail($id);

        $validated = $request->validate([
            'status'          => 'required|string|in:PENDING,PACKED,DISPATCHED,DELIVERED,RETURNED',
            'tracking_number' => 'nullable|string|max:100',
        ]);

        $updateData = ['status' => $validated['status']];

        if (isset($validated['tracking_number'])) {
            $updateData['tracking_number'] = $validated['tracking_number'];
        }

        if ($validated['status'] === 'DISPATCHED') {
            $updateData['dispatched_at'] = Carbon::now();
        } elseif ($validated['status'] === 'DELIVERED') {
            $updateData['delivered_at'] = Carbon::now();
        }

        $order->update($updateData);

        return response()->json([
            'success' => true,
            'data'    => $order,
            'message' => "Order status updated to {$validated['status']}",
        ]);
    }
}
