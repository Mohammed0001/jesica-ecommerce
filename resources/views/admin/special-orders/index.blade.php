@extends('layouts.admin')

@section('title', 'Special Orders')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">Special Orders</h1>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" onclick="refreshPage()">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.special-orders.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Customer name, email, or message..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Filter
                    </button>
                    <a href="{{ route('admin.special-orders.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6>Special Orders</h6>
            @if($orders->count() > 0)
                <span class="badge bg-primary">{{ $orders->total() }} total orders</span>
            @endif
        </div>
        <div class="card-body">
            @if($orders->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Product</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Budget</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.special-orders.show', $order->id) }}" class="order-link">
                                        #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }}
                                    </a>
                                </td>
                                <td>
                                    <div class="customer-info">
                                        <div class="customer-name">{{ $order->customer_name }}</div>
                                        <div class="customer-email">{{ $order->customer_email }}</div>
                                    </div>
                                </td>
                                <td>
                                    <div class="product-info">
                                        @if($order->product)
                                            <div class="product-name">{{ $order->product->name }}</div>
                                            <div class="product-collection">{{ $order->product->collection->name ?? 'No Collection' }}</div>
                                        @else
                                            <span class="text-muted">General Inquiry</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <form method="POST" action="{{ route('admin.special-orders.updateStatus', $order->id) }}" class="status-form">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm status-select"
                                                onchange="this.form.submit()" data-order-id="{{ $order->id }}">
                                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                            <option value="in_progress" {{ $order->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <div class="date-info">
                                        <div class="date">{{ $order->created_at->format('M d, Y') }}</div>
                                        <div class="time">{{ $order->created_at->format('h:i A') }}</div>
                                    </div>
                                </td>
                                <td>
                                    @if($order->budget)
                                        <span class="budget-amount">${{ number_format($order->budget, 2) }}</span>
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.special-orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="mailto:{{ $order->customer_email }}?subject=Re: Special Order #{{ $order->id }}"
                                           class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                        @if($order->customer_phone)
                                            <a href="tel:{{ $order->customer_phone }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-phone"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h5>No Special Orders</h5>
                    @if(request()->hasAny(['status', 'search', 'date_from', 'date_to']))
                        <p>No special orders match your current filters.</p>
                        <a href="{{ route('admin.special-orders.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    @else
                        <p>No special orders have been submitted yet.</p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
.order-link {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    color: var(--primary-color);
    text-decoration: none;
}

.order-link:hover {
    text-decoration: underline;
}

.customer-info .customer-name {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.customer-info .customer-email {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.product-info .product-name {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.product-info .product-collection {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.status-form {
    margin: 0;
}

.status-select {
    border: none;
    background: transparent;
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    cursor: pointer;
    min-width: 120px;
}

.status-select:focus {
    box-shadow: none;
    border: 1px solid var(--primary-color);
}

.status-select option {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
}

.date-info .date {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.date-info .time {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.budget-amount {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    color: var(--primary-color);
}

.empty-state {
    text-align: center;
    padding: 4rem 1rem;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: var(--border-light);
}

.empty-state h5 {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.empty-state p {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    margin-bottom: 1.5rem;
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

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 2px;
    border-bottom-left-radius: 2px;
}

.btn-group .btn:last-child {
    border-top-right-radius: 2px;
    border-bottom-right-radius: 2px;
}
</style>
@endpush

@push('scripts')
<script>
function refreshPage() {
    window.location.reload();
}

// Auto-submit status changes with confirmation for important statuses
document.addEventListener('DOMContentLoaded', function() {
    const statusSelects = document.querySelectorAll('.status-select');

    statusSelects.forEach(select => {
        const originalValue = select.value;

        select.addEventListener('change', function(e) {
            const newStatus = this.value;
            const orderId = this.dataset.orderId;

            // Confirm for certain status changes
            if (newStatus === 'cancelled' || newStatus === 'completed') {
                const confirmMessage = newStatus === 'cancelled'
                    ? 'Are you sure you want to cancel this order?'
                    : 'Are you sure this order is completed?';

                if (!confirm(confirmMessage)) {
                    this.value = originalValue;
                    e.preventDefault();
                    return false;
                }
            }
        });
    });
});
</script>
@endpush
@endsection
