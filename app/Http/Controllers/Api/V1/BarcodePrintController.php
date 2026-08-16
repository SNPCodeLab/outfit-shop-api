<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\SaleHeader;
use Illuminate\Http\JsonResponse;

class BarcodePrintController extends Controller
{
    /**
     * Generate 50mm x 30mm Thermal Barcode Sticker Label Specification
     */
    public function barcodeLabel(int $variantId): JsonResponse
    {
        $variant = ProductVariant::with(['product.brandRef', 'size', 'color'])->findOrFail($variantId);
        $khrRate = 4100;
        $priceUsd = (float) $variant->sale_price;
        $priceKhr = number_format($priceUsd * $khrRate, 0, '.', ',');

        $barcodeData = $variant->barcode ?: $variant->sku;

        return response()->json([
            'success' => true,
            'data'    => [
                'label_dimensions' => '50mm x 30mm',
                'brand_name'       => $variant->product->brandRef->brand_name ?? ($variant->product->brand ?: 'KhmeRiel'),
                'product_name'     => $variant->product->product_name,
                'sku'              => $variant->sku,
                'barcode'          => $barcodeData,
                'size_name'        => $variant->size->size_name ?? 'STD',
                'color_name'       => $variant->color->color_name ?? 'Default',
                'price_usd'        => '$' . number_format($priceUsd, 2),
                'price_khr'        => $priceKhr . ' ៛',
                'barcode_svg_url'  => 'https://barcode.tec-it.com/barcode.ashx?data=' . urlencode($barcodeData) . '&code=Code128&dpi=96',
                'html_template'    => "<div style='width:189px;height:113px;border:1px dashed #000;padding:6px;font-family:sans-serif;box-sizing:border-box;text-align:center;'>
                    <div style='font-size:10px;font-weight:bold;text-transform:uppercase;'>{$variant->product->product_name}</div>
                    <div style='font-size:8px;color:#555;'>Size: {$variant->size->size_name} | Color: {$variant->color->color_name}</div>
                    <img src='https://barcode.tec-it.com/barcode.ashx?data={$barcodeData}&code=Code128' style='height:36px;margin:2px 0;'/>
                    <div style='font-size:12px;font-weight:bold;'>\${$priceUsd} / {$priceKhr} ៛</div>
                </div>",
            ],
            'message' => '50x30mm thermal barcode label generated successfully',
        ]);
    }

    /**
     * Generate formatted 80mm ESC/POS receipt payload
     */
    public function receiptThermal(int $saleId): JsonResponse
    {
        $sale = SaleHeader::with(['customer', 'employee', 'details.variant.product', 'details.variant.size', 'details.variant.color', 'payments'])
            ->findOrFail($saleId);

        $khrRate = 4100;
        $totalUsd = (float) $sale->grand_total;
        $totalKhr = number_format($totalUsd * $khrRate, 0, '.', ',');

        return response()->json([
            'success' => true,
            'data'    => [
                'sale_id'         => $sale->sale_id,
                'invoice_no'      => 'INV-' . str_pad((string)$sale->sale_id, 6, '0', STR_PAD_LEFT),
                'store_name'      => 'KhmeRiel Flagship Store',
                'store_address'   => 'St. 214, Daun Penh, Phnom Penh, Cambodia',
                'phone'           => '+855 23 888 999',
                'cashier_name'    => $sale->employee->employee_name ?? 'Staff Cashier',
                'customer_name'   => $sale->customer->customer_name ?? 'General Guest',
                'sale_date'       => $sale->sale_date ? $sale->sale_date->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                'items'           => $sale->details->map(fn($d) => [
                    'item_name'  => ($d->variant->product->product_name ?? 'Item') . ' (' . ($d->variant->size->size_name ?? '') . ')',
                    'quantity'   => $d->quantity,
                    'unit_price' => (float)$d->unit_price,
                    'sub_total'  => (float)$d->sub_total,
                ]),
                'subtotal_usd'    => (float) $sale->total_amount,
                'discount_usd'    => (float) $sale->discount,
                'grand_total_usd' => $totalUsd,
                'grand_total_khr' => $totalKhr . ' ៛',
                'exchange_rate'   => "1 USD = {$khrRate} KHR",
                'payment_method'  => $sale->payments->first()->payment_method ?? 'CASH',
            ],
            'message' => '80mm thermal receipt payload generated',
        ]);
    }
}
