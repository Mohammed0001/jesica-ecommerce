@extends('layouts.app')

@section('title', 'Special Order #' . $order->id)

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title">Special Order #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</h2>
        <a href="{{ route('special-orders.index') }}" class="btn btn-outline-secondary">Back to Requests</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5>{{ $order->title }}</h5>
            <p class="text-muted">Submitted: {{ $order->created_at->format('M d, Y') }}</p>
            <p>{{ $order->description }}</p>

            @if($order->measurements)
                <hr>
                <h6>Measurements</h6>
                <pre class="small">{{ is_array($order->measurements) ? json_encode($order->measurements, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : $order->measurements }}</pre>
            @endif

            @if($order->estimated_price)
                <p class="mb-0">Budget: <strong>EGP{{ number_format($order->estimated_price, 2) }}</strong></p>
            @endif

            <div class="mt-3">
                <span class="badge bg-secondary">Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
            </div>
        </div>
    </div>

    @if($order->admin_notes)
    <div class="card mb-4">
        <div class="card-header">Admin Notes</div>
        <div class="card-body">
            <p class="text-muted">{{ $order->admin_notes }}</p>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header">Customer Message</div>
        <div class="card-body">
            <p>{{ $order->message }}</p>
        </div>
    </div>
</div>
@endsection
