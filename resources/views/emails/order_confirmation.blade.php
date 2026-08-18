@extends('emails.layout')

@section('content')
<h2 style="color: #0f172a; margin-top: 0;">Order Confirmation #{{ $order->sale_id ?? '1001' }}</h2>
<p>Hello <strong>{{ $customer->name ?? 'Valued Customer' }}</strong>,</p>
<p>Thank you for shopping with <strong>OutfitShop</strong>! We have received your order and are preparing your items for delivery/pickup.</p>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
    <thead>
        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left;">
            <th style="padding: 10px;">Item</th>
            <th style="padding: 10px; text-align: center;">Qty</th>
            <th style="padding: 10px; text-align: right;">Price</th>
            <th style="padding: 10px; text-align: right;">Total</th>
        </tr>
    </thead>
    <tbody>
        @if(isset($items))
            @foreach($items as $item)
            <tr style="border-bottom: 1px solid #f1f5f9;">
                <td style="padding: 10px;">{{ $item['product_name'] ?? 'Product' }}</td>
                <td style="padding: 10px; text-align: center;">{{ $item['quantity'] ?? 1 }}</td>
                <td style="padding: 10px; text-align: right;">${{ number_format($item['price'] ?? 0, 2) }}</td>
                <td style="padding: 10px; text-align: right;">${{ number_format(($item['price'] ?? 0) * ($item['quantity'] ?? 1), 2) }}</td>
            </tr>
            @endforeach
        @endif
    </tbody>
    <tfoot>
        <tr>
            <td colspan="3" style="padding: 10px; text-align: right; font-weight: 600;">Grand Total:</td>
            <td style="padding: 10px; text-align: right; font-weight: 700; color: #2563eb;">${{ number_format($total ?? 0, 2) }}</td>
        </tr>
    </tfoot>
</table>

<p style="margin-top: 25px;">
    <a href="{{ url('/api/v1/orders/' . ($order->sale_id ?? 1)) }}" class="btn">View Order Status</a>
</p>
@endsection
