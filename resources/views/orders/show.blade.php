@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<main class="order-details-page">
    <!-- Page Header -->
    <section class="order-header">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="header-content">
                        <div class="header-info">
                            <h1 class="page-title">Order #{{ $order->order_number }}</h1>
                            <p class="page-subtitle">Placed on {{ $order->created_at->format('F d, Y \a\t g:i A') }}</p>
                        </div>
                        <div class="header-status">
                            <span class="status-badge status-{{ strtolower($order->status) }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>

                    <nav class="order-nav">
                        <a href="{{ route('orders.index') }}" class="back-link">
                            <i class="fas fa-arrow-left"></i> Back to Orders
                        </a>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Order Content -->
    <section class="order-content">
        <div class="container">
            <div class="row">
                <!-- Order Items -->
                <div class="col-lg-8">
                    <div class="order-section">
                        <h2 class="section-title">Order Items</h2>
                        <div class="items-container">
                            @foreach($order->items as $item)
                            <div class="order-item">
                                <div class="item-image">
                                    @if($item->product && $item->product->thumbnail_path)
                                        <img src="{{ asset('storage/' . $item->product->thumbnail_path) }}"
                                             alt="{{ $item->product->name }}"
                                             class="product-thumbnail">
                                    @else
                                        <div class="placeholder-image">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="item-details">
                                    <h3 class="item-name">{{ $item->product ? $item->product->name : 'Product Unavailable' }}</h3>
                                    @if($item->product && $item->product->description)
                                        <p class="item-description">{{ Str::limit($item->product->description, 100) }}</p>
                                    @endif
                                    <div class="item-meta">
                                        <span class="item-quantity">Quantity: {{ $item->quantity }}</span>
                                        <span class="item-price">Unit Price: ${{ number_format($item->price, 2) }}</span>
                                    </div>
                                </div>
                                <div class="item-total">
                                    <span class="total-amount">${{ number_format($item->quantity * $item->price, 2) }}</span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Shipping Information -->
                    @if($order->shipping_address_snapshot)
                    <div class="order-section">
                        <h2 class="section-title">Shipping Address</h2>
                        <div class="address-card">
                            <div class="address-content">
                                @php
                                    $address = $order->shipping_address_snapshot;
                                @endphp
                                <p class="address-line">{{ $address['street_address'] ?? '' }}</p>
                                @if(isset($address['apartment']))
                                    <p class="address-line">{{ $address['apartment'] }}</p>
                                @endif
                                <p class="address-line">
                                    {{ $address['city'] ?? '' }}, {{ $address['state'] ?? '' }} {{ $address['postal_code'] ?? '' }}
                                </p>
                                @if(isset($address['country']))
                                    <p class="address-line">{{ $address['country'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Order Timeline -->
                    <div class="order-section">
                        <h2 class="section-title">Order Timeline</h2>
                        <div class="timeline">
                            <div class="timeline-item completed">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-title">Order Placed</h4>
                                    <p class="timeline-date">{{ $order->created_at->format('M d, Y \a\t g:i A') }}</p>
                                </div>
                            </div>

                            @if($order->status !== 'cancelled')
                            <div class="timeline-item {{ in_array($order->status, ['processing', 'shipped', 'completed']) ? 'completed' : '' }}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-title">Order Processing</h4>
                                    @if(in_array($order->status, ['processing', 'shipped', 'completed']))
                                        <p class="timeline-date">Processing started</p>
                                    @else
                                        <p class="timeline-date">Pending</p>
                                    @endif
                                </div>
                            </div>

                            <div class="timeline-item {{ in_array($order->status, ['shipped', 'completed']) ? 'completed' : '' }}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-title">Order Shipped</h4>
                                    @if($order->shipped_at)
                                        <p class="timeline-date">{{ $order->shipped_at->format('M d, Y \a\t g:i A') }}</p>
                                    @else
                                        <p class="timeline-date">Not yet shipped</p>
                                    @endif
                                </div>
                            </div>

                            <div class="timeline-item {{ $order->status === 'completed' ? 'completed' : '' }}">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-title">Order Delivered</h4>
                                    @if($order->completed_at)
                                        <p class="timeline-date">{{ $order->completed_at->format('M d, Y \a\t g:i A') }}</p>
                                    @else
                                        <p class="timeline-date">Not yet delivered</p>
                                    @endif
                                </div>
                            </div>
                            @else
                            <div class="timeline-item cancelled">
                                <div class="timeline-marker"></div>
                                <div class="timeline-content">
                                    <h4 class="timeline-title">Order Cancelled</h4>
                                    <p class="timeline-date">{{ $order->updated_at->format('M d, Y \a\t g:i A') }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="col-lg-4">
                    <div class="order-summary-card">
                        <h2 class="summary-title">Order Summary</h2>

                        <div class="summary-details">
                            <div class="summary-row">
                                <span class="summary-label">Subtotal:</span>
                                <span class="summary-value">${{ number_format($order->items->sum(function($item) { return $item->quantity * $item->price; }), 2) }}</span>
                            </div>

                            <div class="summary-row">
                                <span class="summary-label">Shipping:</span>
                                <span class="summary-value">Free</span>
                            </div>

                            <div class="summary-row">
                                <span class="summary-label">Tax:</span>
                                <span class="summary-value">$0.00</span>
                            </div>

                            <hr class="summary-divider">

                            <div class="summary-row total-row">
                                <span class="summary-label">Total:</span>
                                <span class="summary-value">${{ number_format($order->total_amount, 2) }}</span>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        @if($order->payments && $order->payments->count() > 0)
                        <div class="payment-info">
                            <h3 class="payment-title">Payment Information</h3>
                            @foreach($order->payments as $payment)
                            <div class="payment-item">
                                <div class="payment-method">{{ ucfirst($payment->method) }}</div>
                                <div class="payment-status">{{ ucfirst($payment->status) }}</div>
                                <div class="payment-amount">${{ number_format($payment->amount, 2) }}</div>
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <!-- Order Actions -->
                        <div class="order-actions">
                            @if($order->status === 'pending')
                                <button class="btn btn-danger btn-block" onclick="cancelOrder({{ $order->id }})">
                                    Cancel Order
                                </button>
                            @endif

                            @if($order->status === 'completed')
                                <a href="{{ route('orders.reorder', $order) }}" class="btn btn-primary btn-block">
                                    Reorder Items
                                </a>
                            @endif

                            <a href="{{ route('orders.invoice', $order) }}" class="btn btn-outline-primary btn-block">
                                Download Invoice
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

@push('styles')
<style>
.order-details-page {
    font-family: 'futura-pt', sans-serif;
}

/* Order Header */
.order-header {
    padding: 3rem 0 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1.5rem;
}

.page-title {
    font-weight: 200;
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    letter-spacing: 0.02em;
}

.page-subtitle {
    font-weight: 200;
    font-size: 1.125rem;
    color: var(--text-muted);
    margin-bottom: 0;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-size: 1rem;
    font-weight: 300;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.back-link {
    color: var(--text-muted);
    text-decoration: none;
    font-weight: 200;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: var(--primary-color);
    text-decoration: none;
}

/* Order Content */
.order-content {
    padding: 3rem 0;
}

.order-section {
    background: white;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.section-title {
    font-weight: 300;
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    letter-spacing: 0.02em;
}

/* Order Items */
.items-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-item {
    display: grid;
    grid-template-columns: 80px 1fr auto;
    gap: 1.5rem;
    align-items: center;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border-light);
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
}

.product-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-image {
    width: 100%;
    height: 100%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 1.5rem;
}

.item-name {
    font-weight: 300;
    font-size: 1.125rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    line-height: 1.3;
}

.item-description {
    color: var(--text-muted);
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.item-meta {
    display: flex;
    gap: 2rem;
    color: var(--text-muted);
    font-size: 0.875rem;
    font-weight: 200;
}

.total-amount {
    font-weight: 300;
    font-size: 1.25rem;
    color: var(--primary-color);
}

/* Address Card */
.address-card {
    background: #f8f9fa;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    padding: 1.5rem;
}

.address-line {
    margin-bottom: 0.5rem;
    color: var(--text-color);
    font-weight: 200;
}

.address-line:last-child {
    margin-bottom: 0;
}

/* Timeline */
.timeline {
    position: relative;
    padding-left: 2rem;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 0.75rem;
    top: 1rem;
    bottom: 1rem;
    width: 2px;
    background: var(--border-light);
}

.timeline-item {
    position: relative;
    margin-bottom: 2rem;
    padding-left: 2rem;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -2rem;
    top: 0.25rem;
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 50%;
    background: var(--border-light);
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.timeline-item.completed .timeline-marker {
    background: #28a745;
}

.timeline-item.cancelled .timeline-marker {
    background: #dc3545;
}

.timeline-title {
    font-weight: 300;
    font-size: 1rem;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.timeline-date {
    color: var(--text-muted);
    font-size: 0.875rem;
    font-weight: 200;
    margin-bottom: 0;
}

/* Order Summary Card */
.order-summary-card {
    background: white;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 2rem;
}

.summary-title {
    font-weight: 300;
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    letter-spacing: 0.02em;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
}

.summary-label {
    color: var(--text-muted);
    font-weight: 200;
}

.summary-value {
    color: var(--primary-color);
    font-weight: 300;
}

.summary-divider {
    border: none;
    border-top: 1px solid var(--border-light);
    margin: 1rem 0;
}

.total-row {
    font-size: 1.125rem;
    font-weight: 300;
    margin-bottom: 0;
}

.total-row .summary-value {
    font-weight: 400;
}

/* Payment Info */
.payment-info {
    margin: 2rem 0;
    padding-top: 2rem;
    border-top: 1px solid var(--border-light);
}

.payment-title {
    font-weight: 300;
    font-size: 1.125rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.payment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.payment-method {
    font-weight: 200;
    color: var(--text-color);
}

.payment-status {
    font-size: 0.875rem;
    color: var(--text-muted);
}

.payment-amount {
    font-weight: 300;
    color: var(--primary-color);
}

/* Order Actions */
.order-actions {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-light);
}

.order-actions .btn {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 0.75rem 1.5rem;
    border-radius: 0;
    transition: all 0.3s ease;
    margin-bottom: 0.75rem;
}

.btn-block {
    width: 100%;
}

/* Status Badges */
.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-processing {
    background: #cce5ff;
    color: #004085;
}

.status-shipped {
    background: #d4edda;
    color: #155724;
}

.status-completed {
    background: #d1ecf1;
    color: #0c5460;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-title {
        font-size: 2.5rem;
    }

    .header-content {
        flex-direction: column;
        gap: 1rem;
    }

    .order-item {
        grid-template-columns: 60px 1fr;
        gap: 1rem;
    }

    .total-amount {
        grid-column: 2;
        text-align: right;
        margin-top: 0.5rem;
    }

    .item-meta {
        flex-direction: column;
        gap: 0.25rem;
    }

    .order-summary-card {
        position: static;
        margin-top: 2rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        fetch(`/orders/${orderId}/cancel`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to cancel order. Please try again.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
        });
    }
}
</script>
@endpush
@endsection
