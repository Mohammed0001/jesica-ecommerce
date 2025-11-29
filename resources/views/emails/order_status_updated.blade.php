@extends('layouts.email')

@section('content')
<h2>Order {{ $order->order_number ?? $order->id }} status updated</h2>
<p>Hi {{ $order->user?->name ?? 'Customer' }},</p>
<p>Your order status changed from <strong>{{ $previousStatus ?? 'N/A' }}</strong> to <strong>{{ $order->status }}</strong>.</p>

<h4>Order summary</h4>
<ul>
    @foreach($order->items as $item)
        <li>{{ $item->product_snapshot['title'] ?? 'Product' }} x {{ $item->quantity }} — {{ number_format($item->price, 2) }}</li>
    @endforeach
</ul>

<p>Total: {{ number_format($order->total_amount, 2) }}</p>

@if($order->status === 'shipped')
    <p>Your order has been shipped. Tracking details: {{ $order->tracking_number ?? 'N/A' }}</p>
@endif

<p>Thank you for shopping with us.</p>
@endsection
