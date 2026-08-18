<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ProductVariant;
use App\Models\SaleHeader;
use Illuminate\Http\JsonResponse;

class BarcodePrintController extends BaseApiController
{
    /**
     * Generate a 50mm x 30mm thermal barcode sticker label specification.
     * Public - no authentication required.
     */
    public function barcodeLabel(int $variantId): JsonResponse
    {
        $variant = ProductVariant::with(['product.brandRef', 'size', 'color'])->findOrFail($variantId);
        $khrRate = 4100;
        $priceUsd = (float) $variant->sale_price;
        $priceKhr = number_format($priceUsd * $khrRate, 0, '.', ',');
        $barcodeData = $variant->barcode ?: $variant->sku;

        return $this->successResponse([
            'label_dimensions' => '50mm x 30mm',
            'brand_name' => $variant->product->brandRef->brand_name ?? ($variant->product->brand ?: 'KhmeRiel'),
            'product_name' => $variant->product->product_name,
            'sku' => $variant->sku,
            'barcode' => $barcodeData,
            'size_name' => $variant->size->size_name ?? 'STD',
            'color_name' => $variant->color->color_name ?? 'Default',
            'price_usd' => '$'.number_format($priceUsd, 2),
            'price_khr' => $priceKhr.' KHR',
            'barcode_svg_url' => 'https://barcode.tec-it.com/barcode.ashx?data='.urlencode($barcodeData).'&code=Code128&dpi=96',
        ], '50x30mm thermal barcode label data generated successfully');
    }

    /**
     * Generate a formatted 80mm ESC/POS receipt payload for thermal printers.
     * Public - no authentication required.
     */
    public function receiptThermal(int $saleId): JsonResponse
    {
        $sale = SaleHeader::with([
            'customer',
            'employee',
            'details.variant.product',
            'details.variant.size',
            'details.variant.color',
            'payments',
        ])->findOrFail($saleId);

        $khrRate = 4100;
        $totalUsd = (float) $sale->grand_total;
        $totalKhr = number_format($totalUsd * $khrRate, 0, '.', ',');

        return $this->successResponse([
            'sale_id' => $sale->sale_id,
            'invoice_no' => 'INV-'.str_pad((string) $sale->sale_id, 6, '0', STR_PAD_LEFT),
            'store_name' => 'KhmeRiel Flagship Store',
            'store_address' => 'St. 214, Daun Penh, Phnom Penh, Cambodia',
            'phone' => '+855 23 888 999',
            'cashier_name' => $sale->employee->employee_name ?? 'Staff Cashier',
            'customer_name' => $sale->customer->customer_name ?? 'General Guest',
            'sale_date' => $sale->sale_date
                ? $sale->sale_date->toISOString()
                : now()->toISOString(),
            'items' => $sale->details->map(fn ($d) => [
                'item_name' => ($d->variant->product->product_name ?? 'Item')
                    .' ('.($d->variant->size->size_name ?? 'STD').')',
                'quantity' => $d->quantity,
                'unit_price' => (float) $d->unit_price,
                'sub_total' => (float) $d->sub_total,
            ]),
            'subtotal_usd' => (float) $sale->total_amount,
            'discount_usd' => (float) $sale->discount,
            'tax_amount_usd' => (float) $sale->tax_amount,
            'grand_total_usd' => $totalUsd,
            'grand_total_khr' => $totalKhr.' KHR',
            'exchange_rate' => "1 USD = {$khrRate} KHR",
            'payment_method' => $sale->payments->first()?->payment_method ?? 'CASH',
        ], '80mm thermal receipt payload generated successfully');
    }
}
