@extends('emails.layout')

@section('content')
<h2 style="color: #0f172a; margin-top: 0;">OutfitShop Tax Invoice #INV-{{ $invoice_number ?? '001' }}</h2>
<p>Dear <strong>{{ $customer_name ?? 'Customer' }}</strong>,</p>
<p>Here is your digital invoice for your recent transaction at <strong>OutfitShop</strong>.</p>

<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 16px; margin: 20px 0;">
    <p style="margin: 0 0 8px;"><strong>Invoice Date:</strong> {{ date('Y-m-d H:i:s') }}</p>
    <p style="margin: 0 0 8px;"><strong>Payment Method:</strong> {{ $payment_method ?? 'KHQR / Card / Cash' }}</p>
    <p style="margin: 0;"><strong>Status:</strong> <span style="color: #16a34a; font-weight: 600;">PAID</span></p>
</div>

<p style="margin-top: 25px;">
    <a href="{{ url('/api/v1/orders/' . ($order_id ?? 1) . '/invoice-pdf') }}" class="btn">Download Official Receipt (PDF)</a>
</p>
@endsection
