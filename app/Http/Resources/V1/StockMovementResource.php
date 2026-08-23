<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\ApiResource;
use Illuminate\Http\Request;

class StockMovementResource extends ApiResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $variant = $this->variant;
        $product = $variant?->product;
        $employee = $this->employee;

        $productName = $product?->product_name ?? $product?->name ?? 'Unknown Product';
        $sku = $variant?->sku ?? 'N/A';
        $employeeName = $employee?->employee_name
            ?? $employee?->name
            ?? ($employee?->first_name ? trim($employee->first_name.' '.($employee->last_name ?? '')) : null)
            ?? 'System';

        return [
            'id' => (int) ($this->movement_id ?? $this->id),
            'movement_id' => (int) ($this->movement_id ?? $this->id),
            'sku' => $sku,
            'product_name' => $productName,
            'quantity' => (int) $this->quantity,
            'movement_type' => (string) $this->movement_type,
            'stock_before' => (int) $this->stock_before,
            'stock_after' => (int) $this->stock_after,
            'movement_date' => $this->movement_date?->toISOString() ?? (string) $this->movement_date,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'note' => $this->note,
            'store_id' => $this->store_id ? (int) $this->store_id : null,
            'variant_id' => (int) $this->variant_id,
            'employee_id' => $this->employee_id ? (int) $this->employee_id : null,
            'employee_name' => $employeeName,
            'created_by' => $this->created_by ? (int) $this->created_by : null,
            'variant' => $variant ? [
                'variant_id' => (int) $variant->variant_id,
                'sku' => $variant->sku,
                'barcode' => $variant->barcode,
                'sale_price' => (float) $variant->sale_price,
                'cost_price' => (float) $variant->cost_price,
                'quantity' => (int) $variant->quantity,
                'size' => $variant->size?->size_name ?? $variant->size?->name ?? null,
                'color' => $variant->color?->color_name ?? $variant->color?->name ?? null,
                'product' => $product ? [
                    'product_id' => (int) $product->product_id,
                    'product_name' => $product->product_name ?? $product->name,
                    'brand' => $product->brand,
                    'image_url' => $product->image_url,
                ] : null,
            ] : null,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
