@extends('layouts.admin')

@section('title', 'Orders Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">Orders</h1>
            <div class="d-flex gap-2">
                <span class="page-meta">{{ $orders->total() }} total orders</span>
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6>All Orders</h6>
                    <div class="d-flex gap-2">
                        @php
                            // Use the centralized Order::STATUSES but show only admin-facing statuses
                            $adminStatuses = array_values(array_intersect(
                                \App\Models\Order::STATUSES,
                                ['pending', 'processing', 'shipped', 'delivered', 'cancelled']
                            ));
                        @endphp
                        <select class="form-select form-select-sm" style="width: auto;">
                            <option value="">All Status</option>
                            @foreach($adminStatuses as $s)
                                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <div class="input-group" style="width: 250px;">
                            <input type="text" class="form-control form-control-sm" placeholder="Search orders...">
                            <button class="btn btn-outline-secondary btn-sm" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table activity-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <div class="order-number">#{{ $order->id }}</div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="customer-name">{{ $order->user->name ?? 'Guest' }}</div>
                                                    <div class="customer-email">{{ $order->user->email ?? 'N/A' }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="items-count">{{ $order->order_items_count ?? $order->orderItems->count() }} items</span>
                                            </td>
                                            <td>
                                                <span class="order-total">EGP{{ number_format($order->total_amount, 2) }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-{{
                                                        $order->status === 'completed' || $order->status === 'delivered' ? 'success' :
                                                        ($order->status === 'pending' ? 'warning' :
                                                        ($order->status === 'cancelled' ? 'danger' : 'info'))
                                                    }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="dropdown">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            @php
                                                                $statusOptions = array_diff($adminStatuses, ['cancelled']);
                                                            @endphp
                                                            @foreach($statusOptions as $s)
                                                                <li>
                                                                    <button class="dropdown-item" onclick="updateOrderStatus({{ $order->id }}, '{{ $s }}')">{{ ucfirst($s) }}</button>
                                                                </li>
                                                            @endforeach
                                                            <li><hr class="dropdown-divider"></li>
                                                            @if(in_array('cancelled', $adminStatuses))
                                                                <li>
                                                                    <button class="dropdown-item text-danger" onclick="updateOrderStatus({{ $order->id }}, 'cancelled')">Cancel</button>
                                                                </li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $order->created_at->format('M j, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.orders.show', $order) }}"
                                                       class="btn btn-sm btn-outline-primary" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="mailto:{{ $order->user->email ?? '' }}"
                                                       class="btn btn-sm btn-outline-secondary" title="Email Customer">
                                                        <i class="fas fa-envelope"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-info"
                                                            onclick="printOrder({{ $order->id }})" title="Print Invoice">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-clipboard-list"></i>
                            <p>No orders found</p>
                            <p class="text-muted">Orders will appear here when customers start purchasing.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.order-number {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 1rem;
    color: var(--primary-color);
}

.customer-name {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 1rem;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.customer-email {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.items-count {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--primary-color);
    padding: 0.25rem 0.75rem;
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 0;
}

.order-total {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 1.125rem;
    color: var(--primary-color);
}

.dropdown-menu {
    border-radius: 0;
    border: 1px solid var(--border-light);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.dropdown-item {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

.dropdown-item:hover {
    background-color: rgba(0, 0, 0, 0.05);
}
</style>
@endpush

@push('scripts')
<script>
function updateOrderStatus(orderId, status) {
    if (confirm(`Are you sure you want to change this order status to "${status}"?`)) {
        fetch(`/admin/orders/${orderId}/update-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: status })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating order status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating order status');
        });
    }
}

function printOrder(orderId) {
    window.open(`/admin/orders/${orderId}/print`, '_blank');
}
</script>
@endpush
@endsection
