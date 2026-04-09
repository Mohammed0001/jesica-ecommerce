@extends('layouts.app')

@section('title', 'Order Confirmed — ' . $order->order_number)

@push('styles')
<style>
.guest-confirm {
    max-width: 680px;
    margin: 4rem auto;
    padding: 0 1.5rem 4rem;
    font-family: 'futura-pt', sans-serif;
}
.guest-confirm__icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #000;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
}
.guest-confirm__tag {
    font-size: 0.8rem;
    font-weight: 300;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #888;
    margin-bottom: 0.5rem;
}
.guest-confirm__heading {
    font-size: 2rem;
    font-weight: 200;
    letter-spacing: 0.04em;
    color: #000;
    margin-bottom: 0.5rem;
}
.guest-confirm__sub {
    font-size: 1rem;
    font-weight: 300;
    color: #555;
    margin-bottom: 2rem;
    line-height: 1.6;
}
.confirm-card {
    border: 1px solid #e8e4e0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    background: #fff;
}
.confirm-card h3 {
    font-size: 0.85rem;
    font-weight: 300;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #888;
    margin-bottom: 1rem;
}
.confirm-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    font-size: 0.9rem;
    font-weight: 300;
    color: #444;
    margin-bottom: 0.5rem;
}
.confirm-row .label { color: #888; }
.confirm-row.total {
    font-size: 1rem;
    font-weight: 400;
    color: #000;
    border-top: 1px solid #e8e4e0;
    padding-top: 0.75rem;
    margin-top: 0.5rem;
}
.confirm-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0ede9;
}
.confirm-item:last-child { border-bottom: none; }
.confirm-item-img {
    width: 56px;
    height: 70px;
    object-fit: cover;
    border-radius: 4px;
    background: #e8e4e0;
    flex-shrink: 0;
}
.confirm-item-name {
    font-size: 0.9rem;
    font-weight: 300;
    color: #000;
    flex: 1;
}
.confirm-item-qty {
    font-size: 0.8rem;
    color: #888;
}
.confirm-item-price {
    font-size: 0.9rem;
    font-weight: 300;
    color: #000;
    white-space: nowrap;
}
.confirm-address {
    font-size: 0.9rem;
    font-weight: 300;
    color: #333;
    line-height: 1.7;
}
.btn-continue {
    display: inline-block;
    padding: 0.875rem 2.5rem;
    background: #000;
    color: #fff;
    text-decoration: none;
    font-family: 'futura-pt', sans-serif;
    font-size: 0.9rem;
    font-weight: 300;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    border-radius: 6px;
    transition: background 0.2s;
}
.btn-continue:hover { background: #222; color: #fff; text-decoration: none; }
</style>
@endpush

@section('content')
@php
    $addr = $order->shipping_address_snapshot ?? [];
    $currSym = config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP');
@endphp

<div class="guest-confirm">

    {{-- Header --}}
    <div class="guest-confirm__icon">
        <i class="fas fa-check"></i>
    </div>
    <div class="guest-confirm__tag">Order #{{ $order->order_number }}</div>
    <h1 class="guest-confirm__heading">Thank you{{ !empty($addr['first_name']) ? ', ' . $addr['first_name'] : '' }}!</h1>
    <p class="guest-confirm__sub">
        Your order has been placed successfully.
        @if($order->guest_email)
            A confirmation will be sent to <strong>{{ $order->guest_email }}</strong>.
        @endif
    </p>

    {{-- Order items --}}
    <div class="confirm-card">
        <h3>Items ordered</h3>
        @foreach($order->items as $item)
        <div class="confirm-item">
            @php
                $snap = $item->product_snapshot ?? [];
                $imgUrl = $snap['main_image_url'] ?? null;
            @endphp
            @if($imgUrl)
                <img src="{{ $imgUrl }}" alt="{{ $snap['title'] ?? '' }}" class="confirm-item-img">
            @else
                <div class="confirm-item-img"></div>
            @endif
            <div class="confirm-item-name">
                {{ $snap['title'] ?? 'Product' }}
                @if($item->size_label)
                    <div class="confirm-item-qty">Size: {{ $item->size_label }}</div>
                @endif
                <div class="confirm-item-qty">Qty: {{ $item->quantity }}</div>
            </div>
            <div class="confirm-item-price">
                {{ $currSym }} {{ number_format($item->subtotal, 2) }}
            </div>
        </div>
        @endforeach
    </div>

    {{-- Order summary --}}
    <div class="confirm-card">
        <h3>Order summary</h3>
        <div class="confirm-row">
            <span class="label">Subtotal</span>
            <span>{{ $currSym }} {{ number_format($order->subtotal, 2) }}</span>
        </div>
        @if(($order->discount_amount ?? 0) > 0)
        <div class="confirm-row">
            <span class="label">Discount</span>
            <span>− {{ $currSym }} {{ number_format($order->discount_amount, 2) }}</span>
        </div>
        @endif
        @if(($order->service_fee ?? 0) > 0)
        <div class="confirm-row">
            <span class="label">Service fee</span>
            <span>{{ $currSym }} {{ number_format($order->service_fee, 2) }}</span>
        </div>
        @endif
        <div class="confirm-row">
            <span class="label">Shipping</span>
            <span>{{ ($order->shipping_amount ?? 0) <= 0 ? 'Free' : $currSym . ' ' . number_format($order->shipping_amount, 2) }}</span>
        </div>
        @if(($order->tax_amount ?? 0) > 0)
        <div class="confirm-row">
            <span class="label">Tax</span>
            <span>{{ $currSym }} {{ number_format($order->tax_amount, 2) }}</span>
        </div>
        @endif
        <div class="confirm-row total">
            <span>Total</span>
            <span>{{ $currSym }} {{ number_format($order->total_amount, 2) }}</span>
        </div>
    </div>

    {{-- Delivery address --}}
    @if(!empty($addr))
    <div class="confirm-card">
        <h3>Shipping to</h3>
        <div class="confirm-address">
            {{ trim(($addr['first_name'] ?? '') . ' ' . ($addr['last_name'] ?? '')) }}<br>
            {{ $addr['address_line_1'] ?? '' }}
            @if(!empty($addr['address_line_2'])), {{ $addr['address_line_2'] }}@endif<br>
            {{ $addr['city'] ?? '' }}{{ !empty($addr['state_province']) ? ', ' . $addr['state_province'] : '' }}
            {{ !empty($addr['postal_code']) ? ' ' . $addr['postal_code'] : '' }}<br>
            {{ $addr['country'] ?? '' }}
            @if(!empty($addr['phone']))<br>{{ $addr['phone'] }}@endif
        </div>
    </div>
    @endif

    {{-- CTA --}}
    <a href="{{ route('home') }}" class="btn-continue">Continue shopping</a>

</div>
@endsection
