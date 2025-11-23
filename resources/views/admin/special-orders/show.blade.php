@extends('layouts.admin')

@section('title', 'Special Order #' . $order->id)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">Special Order #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <div class="d-flex gap-2">
                <a href="mailto:{{ $order->customer_email }}?subject=Re: Special Order #{{ $order->id }}" class="btn btn-outline-success">
                    <i class="fas fa-envelope me-2"></i>Email Customer
                </a>
                @if($order->customer_phone)
                    <a href="tel:{{ $order->customer_phone }}" class="btn btn-outline-info">
                        <i class="fas fa-phone me-2"></i>Call Customer
                    </a>
                @endif
                <a href="{{ route('admin.special-orders.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Orders
                </a>
            </div>
        </div>
    </div>

    <!-- Order Details -->
    <div class="row">
        <div class="col-lg-8">
            <!-- Customer Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6>Customer Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-row">
                                <label>Name:</label>
                                <span>{{ $order->customer_name }}</span>
                            </div>
                            <div class="detail-row">
                                <label>Email:</label>
                                <span>
                                    <a href="mailto:{{ $order->customer_email }}" class="text-decoration-none">
                                        {{ $order->customer_email }}
                                    </a>
                                </span>
                            </div>
                            @if($order->customer_phone)
                            <div class="detail-row">
                                <label>Phone:</label>
                                <span>
                                    <a href="tel:{{ $order->customer_phone }}" class="text-decoration-none">
                                        {{ $order->customer_phone }}
                                    </a>
                                </span>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="detail-row">
                                <label>Submitted:</label>
                                <span>{{ $order->created_at->format('M d, Y at h:i A') }}</span>
                            </div>
                            @if($order->budget)
                            <div class="detail-row">
                                <label>Budget:</label>
                                <span class="budget-amount">${{ number_format($order->budget, 2) }}</span>
                            </div>
                            @endif
                            @if($order->deadline)
                            <div class="detail-row">
                                <label>Deadline:</label>
                                <span class="deadline-date">{{ $order->deadline->format('M d, Y') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Information -->
            @if($order->product)
            <div class="card mb-4">
                <div class="card-header">
                    <h6>Product Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="product-image-container">
                                @if(optional($order->product->main_image)->url)
                                    <img src="{{ optional($order->product->main_image)->url ?? asset('images/placeholder-product.jpg') }}" alt="{{ $order->product->name }}" class="product-image">
                                @else
                                    <div class="placeholder-image">
                                        <i class="fas fa-image"></i>
                                        <span>No Image</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h5 class="product-name">{{ $order->product->name }}</h5>
                            <div class="detail-row">
                                <label>Collection:</label>
                                <span>{{ $order->product->collection->title ?? 'No Collection' }}</span>
                            </div>
                            <div class="detail-row">
                                <label>Base Price:</label>
                                <span class="product-price">${{ number_format($order->product->price, 2) }}</span>
                            </div>
                            <div class="detail-row">
                                <label>Description:</label>
                                <span>{{ $order->product->description }}</span>
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('admin.products.show', $order->product) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-2"></i>View Product Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Customer Message -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6>Customer Message</h6>
                </div>
                <div class="card-body">
                    <div class="customer-message">
                        {{ $order->message }}
                    </div>
                </div>
            </div>

            <!-- Admin Notes -->
            <div class="card">
                <div class="card-header">
                    <h6>Admin Notes</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.special-orders.updateNotes', $order->id) }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <textarea name="admin_notes" class="form-control" rows="4"
                                      placeholder="Add internal notes about this order...">{{ $order->admin_notes }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Notes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Order Status -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6>Order Status</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.special-orders.updateStatus', $order->id) }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">Current Status</label>
                            <select name="status" class="form-select" id="status-select">
                                <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                <option value="in_progress" {{ $order->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Update Status
                        </button>
                    </form>

                    <div class="status-info mt-3">
                        <div class="status-badge-large {{ $order->status }}">
                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                        </div>
                        @if($order->updated_at != $order->created_at)
                            <div class="status-updated">
                                Last updated: {{ $order->updated_at->format('M d, Y at h:i A') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="mailto:{{ $order->customer_email }}?subject=Re: Special Order #{{ $order->id }}" class="btn btn-success">
                            <i class="fas fa-envelope me-2"></i>Email Customer
                        </a>

                        @if($order->customer_phone)
                            <a href="tel:{{ $order->customer_phone }}" class="btn btn-info">
                                <i class="fas fa-phone me-2"></i>Call Customer
                            </a>
                        @endif

                        <button onclick="window.print()" class="btn btn-outline-secondary">
                            <i class="fas fa-print me-2"></i>Print Order
                        </button>

                        @if($order->product)
                            <a href="{{ route('admin.products.show', $order->product) }}" class="btn btn-outline-primary">
                                <i class="fas fa-box me-2"></i>View Product
                            </a>
                        @endif

                        <form method="POST" action="{{ route('admin.special-orders.destroy', $order->id) }}"
                              onsubmit="return confirm('Are you sure you want to delete this order?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash me-2"></i>Delete Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Timeline -->
            <div class="card">
                <div class="card-header">
                    <h6>Order Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6>Order Submitted</h6>
                                <p>{{ $order->created_at->format('M d, Y at h:i A') }}</p>
                            </div>
                        </div>

                        @if($order->status !== 'pending')
                        <div class="timeline-item">
                            <div class="timeline-marker active"></div>
                            <div class="timeline-content">
                                <h6>Status: {{ ucfirst(str_replace('_', ' ', $order->status)) }}</h6>
                                <p>{{ $order->updated_at->format('M d, Y at h:i A') }}</p>
                            </div>
                        </div>
                        @endif

                        @if($order->deadline)
                        <div class="timeline-item future">
                            <div class="timeline-marker future"></div>
                            <div class="timeline-content">
                                <h6>Deadline</h6>
                                <p>{{ $order->deadline->format('M d, Y') }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.detail-row {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.detail-row label {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.875rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--primary-color);
    width: 120px;
    margin-bottom: 0;
    flex-shrink: 0;
}

.detail-row span {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    color: var(--text-dark);
    flex: 1;
}

.budget-amount {
    font-weight: 400;
    color: var(--primary-color);
}

.deadline-date {
    font-weight: 400;
    color: var(--primary-color);
}

.product-image-container {
    position: relative;
    background: #f8f9fa;
    border-radius: 4px;
    overflow: hidden;
    aspect-ratio: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-image {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
}

.placeholder-image i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.product-name {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.product-price {
    font-weight: 400;
    color: var(--primary-color);
}

.customer-message {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    line-height: 1.6;
    color: var(--text-dark);
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
    border-left: 4px solid var(--primary-color);
}

.status-badge-large {
    padding: 0.75rem 1rem;
    border-radius: 4px;
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 1rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    text-align: center;
}

.status-badge-large.pending {
    background: #ffc107;
    color: #000;
}

.status-badge-large.confirmed {
    background: #17a2b8;
    color: white;
}

.status-badge-large.in_progress {
    background: #fd7e14;
    color: white;
}

.status-badge-large.completed {
    background: #28a745;
    color: white;
}

.status-badge-large.cancelled {
    background: #dc3545;
    color: white;
}

.status-updated {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--text-muted);
    text-align: center;
    margin-top: 0.5rem;
}

.timeline {
    position: relative;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: var(--border-light);
}

.timeline-item {
    position: relative;
    padding-left: 50px;
    margin-bottom: 2rem;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: 8px;
    top: 0;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: var(--border-light);
    border: 2px solid white;
}

.timeline-marker.active {
    background: var(--primary-color);
}

.timeline-marker.future {
    background: #ffc107;
}

.timeline-content h6 {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
}

.timeline-content p {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 0;
}

.form-label {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.875rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border-radius: 0;
    border: 1px solid var(--border-light);
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
}

@media print {
    .page-header .btn,
    .card:last-child,
    .btn {
        display: none !important;
    }

    .container-fluid {
        padding: 0 !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
        margin-bottom: 1rem !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status-select');

    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            const newStatus = this.value;

            // Confirm for certain status changes
            if (newStatus === 'cancelled' || newStatus === 'completed') {
                const confirmMessage = newStatus === 'cancelled'
                    ? 'Are you sure you want to cancel this order?'
                    : 'Are you sure this order is completed?';

                if (!confirm(confirmMessage)) {
                    this.value = "{{ $order->status }}";
                    return false;
                }
            }
        });
    }
});
</script>
@endpush
@endsection
