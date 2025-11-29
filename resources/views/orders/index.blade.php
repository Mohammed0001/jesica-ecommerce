@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<main class="orders-page">
    <!-- Page Header -->
    <section class="orders-header">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="page-title">My Orders</h1>
                    <p class="page-subtitle">Track your purchase history and order status</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Orders Content -->
    <section class="orders-content">
        <div class="container">
            @if($orders->count() > 0)
                <div class="orders-list">
                    @foreach($orders as $order)
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <h3 class="order-number">#{{ $order->order_number }}</h3>
                                <p class="order-date">{{ $order->created_at->format('M d, Y') }}</p>
                            </div>
                            <div class="order-status">
                                <span class="status-badge status-{{ strtolower($order->status) }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>

                        <div class="order-body">
                            <div class="order-items">
                                <h4 class="items-title">Items ({{ $order->items->count() }})</h4>
                                <div class="items-list">
                                    @foreach($order->items->take(3) as $item)
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
                                            <h5 class="item-name">{{ $item->product ? $item->product->name : 'Product Unavailable' }}</h5>
                                            <p class="item-meta">
                                                Qty: {{ $item->quantity }} × {{ $item->formattedPrice }}
                                            </p>
                                        </div>
                                    </div>
                                    @endforeach

                                    @if($order->items->count() > 3)
                                    <div class="more-items">
                                        <p class="text-muted">+{{ $order->items->count() - 3 }} more items</p>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <div class="order-summary">
                                <div class="summary-row">
                                    <span class="summary-label">Subtotal:</span>
                                    <span class="summary-value">{{ $order->formattedSubtotal }}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Discount:</span>
                                    <span class="summary-value">- {{ $order->formattedDiscount }}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Service:</span>
                                    <span class="summary-value">{{ $order->formattedServiceFee }}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Shipping:</span>
                                    <span class="summary-value">{{ ($order->shipping_amount ?? 0) <= 0 ? 'Free' : $order->formattedShipping }}</span>
                                </div>
                                <div class="summary-row">
                                    <span class="summary-label">Tax:</span>
                                    <span class="summary-value">{{ $order->formattedTax }}</span>
                                </div>

                                <div class="summary-row">
                                    <span class="summary-label">Total Amount:</span>
                                    <span class="summary-value">{{ $order->formattedTotal }}</span>
                                </div>

                                @if($order->shipped_at)
                                <div class="summary-row">
                                    <span class="summary-label">Shipped:</span>
                                    <span class="summary-value">{{ $order->shipped_at->format('M d, Y') }}</span>
                                </div>
                                @endif

                                @if($order->completed_at)
                                <div class="summary-row">
                                    <span class="summary-label">Completed:</span>
                                    <span class="summary-value">{{ $order->completed_at->format('M d, Y') }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="order-actions">
                            <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-primary">
                                View Details
                            </a>

                            @if($order->status === 'pending')
                            <button class="btn btn-secondary" onclick="cancelOrder({{ $order->id }})">
                                Cancel Order
                            </button>
                            @endif

                            @if($order->status === 'completed')
                            <a href="{{ route('orders.reorder', $order) }}" class="btn btn-primary">
                                Reorder
                            </a>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($orders->hasPages())
                <div class="pagination-wrapper">
                    {{ $orders->links('vendor.pagination.bootstrap-5') }}
                </div>
                @endif

            @else
                <!-- No Orders -->
                <div class="no-orders">
                    <div class="no-orders-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h3 class="no-orders-title">No Orders Yet</h3>
                    <p class="no-orders-message">
                        You haven't placed any orders yet. Start shopping to see your orders here.
                    </p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary">
                        Start Shopping
                    </a>
                </div>
            @endif
        </div>
    </section>
</main>

@push('styles')
<style>
.orders-page {
    font-family: 'futura-pt', sans-serif;
}

/* Orders Header */
.orders-header {
    padding: 3rem 0 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
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

/* Orders Content */
.orders-content {
    padding: 3rem 0;
}

.orders-list {
    margin-bottom: 2rem;
}

/* Order Card */
.order-card {
    background: white;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    margin-bottom: 2rem;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.order-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem 2rem;
    background: #f8f9fa;
    border-bottom: 1px solid var(--border-light);
}

.order-number {
    font-weight: 300;
    font-size: 1.25rem;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
    letter-spacing: 0.02em;
}

.order-date {
    color: var(--text-muted);
    margin-bottom: 0;
    font-weight: 200;
}

.status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 300;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

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

.order-body {
    padding: 2rem;
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

/* Order Items */
.items-title {
    font-weight: 300;
    font-size: 1.125rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
    letter-spacing: 0.02em;
}

.items-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.item-image {
    width: 60px;
    height: 60px;
    border-radius: 4px;
    overflow: hidden;
    flex-shrink: 0;
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
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: 300;
    font-size: 1rem;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

.item-meta {
    color: var(--text-muted);
    margin-bottom: 0;
    font-size: 0.875rem;
    font-weight: 200;
}

.more-items {
    text-align: center;
    padding: 0.5rem;
    border-top: 1px solid var(--border-light);
    margin-top: 0.5rem;
}

/* Order Summary */
.order-summary {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.summary-label {
    color: var(--text-muted);
    font-weight: 200;
}

.summary-value {
    color: var(--primary-color);
    font-weight: 300;
}

/* Order Actions */
.order-actions {
    padding: 1.5rem 2rem;
    background: #f8f9fa;
    border-top: 1px solid var(--border-light);
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.order-actions .btn {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 0.5rem 1.5rem;
    border-radius: 0;
    transition: all 0.3s ease;
}

/* No Orders */
.no-orders {
    text-align: center;
    padding: 4rem 2rem;
}

.no-orders-icon {
    font-size: 4rem;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

.no-orders-title {
    font-weight: 300;
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.no-orders-message {
    color: var(--text-muted);
    font-size: 1.125rem;
    margin-bottom: 2rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

/* Pagination */
.pagination-wrapper {
    margin-top: 2rem;
    display: flex;
    justify-content: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-title {
        font-size: 2.5rem;
    }

    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
    }

    .order-body {
        grid-template-columns: 1fr;
        gap: 1.5rem;
        padding: 1.5rem;
    }

    .order-actions {
        flex-direction: column;
        padding: 1rem;
    }

    .order-actions .btn {
        width: 100%;
    }

    .item-image {
        width: 50px;
        height: 50px;
    }
}
</style>
@endpush

@push('scripts')
<script>
function cancelOrder(orderId) {
    if (confirm('Are you sure you want to cancel this order?')) {
        // Add AJAX call to cancel order
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
