@extends('emails.layout')

@section('content')
<h2 style="color: #0f172a; margin-top: 0;">Welcome to OutfitShop!</h2>
<p>Hello <strong>{{ $name ?? 'Shopper' }}</strong>,</p>
<p>Welcome to <strong>OutfitShop</strong> — your premier destination for modern clothing, high-fashion apparel, and seamless shopping experiences.</p>

<p>Your account is now active. You can browse the complete catalog, save favorites to your wishlist, and manage your orders online.</p>

<p style="margin-top: 25px;">
    <a href="{{ config('app.url', 'https://api.kesararamwithdigital.tech') }}/api/v1/products" class="btn">Explore New Arrivals</a>
</p>
@endsection
