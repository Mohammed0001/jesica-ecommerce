@extends('layouts.app')

@section('title', 'My Special Orders')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="section-title">My Special Orders</h2>
        <a href="{{ route('special-orders.create') }}" class="btn btn-primary">Request a Special Order</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($orders->count())
        <div class="list-group">
            @foreach($orders as $order)
                <a href="{{ route('special-orders.show', $order) }}" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">{{ $order->title }}</h5>
                        <small>{{ $order->created_at->format('M d, Y') }}</small>
                    </div>
                    <p class="mb-1 text-muted">Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                    @if($order->estimated_price)
                        <small class="text-muted">Budget: EGP{{ number_format($order->estimated_price, 2) }}</small>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @else
        <div class="empty-state text-center py-5">
            <h5>No special orders yet</h5>
            <p class="text-muted">Use the button above to request a custom piece.</p>
        </div>
    @endif
</div>
@endsection
