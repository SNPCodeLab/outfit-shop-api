<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\ApiResource;
use Illuminate\Http\Request;

class OrderResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $customer = $this->customer;
        $employee = $this->employee;

        // Resolve customer name (always a non-empty string fallback)
        $customerName = $customer?->customer_name
            ?? $customer?->name
            ?? ($customer && ($customer->first_name || $customer->last_name)
                ? trim(($customer->first_name ?? '').' '.($customer->last_name ?? ''))
                : null)
            ?? 'Walk-in Customer';

        // Resolve cashier / employee name (always a non-empty string fallback)
        $cashierName = $employee?->employee_name
            ?? $employee?->name
            ?? ($employee && ($employee->first_name || $employee->last_name)
                ? trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''))
                : null)
            ?? 'System Cashier';

        $grandTotal = (float) ($this->grand_total ?? $this->total_amount ?? 0.0);
        $subTotal = (float) ($this->sub_total ?? $this->total_amount ?? 0.0);
        $taxAmount = (float) ($this->tax_amount ?? 0.0);
        $discountAmount = (float) ($this->discount ?? $this->discount_amount ?? 0.0);
        $taxRate = (float) ($this->tax_rate ?? 0.0);

        $items = $this->details ? $this->details->map(function ($detail) {
            $variant = $detail->variant;
            $product = $variant?->product;

            return [
                'detail_id' => (int) ($detail->detail_id ?? $detail->id),
                'variant_id' => (int) $detail->variant_id,
                'product_name' => $product?->product_name ?? $product?->name ?? 'Unknown Product',
                'sku' => $variant?->sku ?? 'N/A',
                'barcode' => $variant?->barcode ?? null,
                'size' => $variant?->size?->size_name ?? $variant?->size?->name ?? null,
                'color' => $variant?->color?->color_name ?? $variant?->color?->name ?? null,
                'quantity' => (int) $detail->quantity,
                'unit_price' => (float) ($detail->unit_price ?? 0.0),
                'discount' => (float) ($detail->discount ?? 0.0),
                'subtotal' => (float) ($detail->sub_total ?? 0.0),
                'sub_total' => (float) ($detail->sub_total ?? 0.0),
            ];
        })->values() : [];

        return [
            'id' => (int) ($this->sale_id ?? $this->id),
            'sale_id' => (int) ($this->sale_id ?? $this->id),
            'order_id' => (int) ($this->sale_id ?? $this->id),
            'invoice_no' => (string) ($this->invoice_no ?? ''),
            'store_id' => $this->store_id ? (int) $this->store_id : null,
            'customer_id' => $this->customer_id ? (int) $this->customer_id : null,
            'customer_name' => $customerName,
            'employee_id' => $this->employee_id ? (int) $this->employee_id : null,
            'cashier_name' => $cashierName,
            'employee_name' => $cashierName,

            // Floating point casts for monetary values
            'total' => $grandTotal,
            'grand_total' => $grandTotal,
            'total_amount' => (float) ($this->total_amount ?? $grandTotal),
            'subtotal' => $subTotal,
            'sub_total' => $subTotal,
            'tax' => $taxAmount,
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'discount' => $discountAmount,
            'discount_amount' => $discountAmount,

            'status' => (string) ($this->status ?? 'COMPLETED'),
            'payment_status' => (string) ($this->payment_status ?? 'PAID'),
            'sale_date' => $this->sale_date?->toISOString() ?? (string) $this->sale_date,
            'notes' => $this->notes,
            'idempotency_key' => $this->idempotency_key,

            'customer' => $customer ? [
                'customer_id' => (int) $customer->customer_id,
                'name' => $customerName,
                'phone' => $customer->phone ?? null,
                'email' => $customer->email ?? null,
                'loyalty_points' => (int) ($customer->loyalty_points ?? 0),
            ] : null,

            'employee' => $employee ? [
                'employee_id' => (int) $employee->employee_id,
                'name' => $cashierName,
                'role' => $employee->role ?? null,
            ] : null,

            'items' => $items,
            'details' => $items,

            'payments' => $this->payments ? $this->payments->map(function ($payment) {
                return [
                    'payment_id' => (int) ($payment->payment_id ?? $payment->id),
                    'payment_method' => (string) $payment->payment_method,
                    'amount' => (float) $payment->amount,
                    'amount_tendered' => (float) ($payment->amount_tendered ?? $payment->amount),
                    'change_due' => (float) ($payment->change_due ?? 0.0),
                    'payment_status' => (string) ($payment->payment_status ?? 'PAID'),
                    'transaction_ref' => $payment->transaction_ref ?? null,
                    'reference_number' => $payment->reference_number ?? null,
                    'payment_date' => $payment->payment_date?->toISOString() ?? (string) $payment->payment_date,
                ];
            })->values() : [],

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
