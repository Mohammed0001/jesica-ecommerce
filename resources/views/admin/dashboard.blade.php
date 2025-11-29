@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">Dashboard</h1>
            <div class="page-meta">
                <span class="date-badge">{{ now()->format('l, F j, Y') }}</span>
                <span class="time-badge">{{ now()->format('g:i A') }}</span>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Users -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stats-title">Total Users</div>
                            <div class="stats-value">{{ number_format($stats['total_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users stats-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stats-title">Total Products</div>
                            <div class="stats-value">{{ number_format($stats['total_products']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-bag stats-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Collections -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stats-title">Collections</div>
                            <div class="stats-value">{{ number_format($stats['total_collections']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-layer-group stats-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stats-card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="stats-title">Total Orders</div>
                            <div class="stats-value">{{ number_format($stats['total_orders']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list stats-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue and Order Status Row -->
    <div class="row mb-4">
        <!-- Monthly Revenue Comparison -->
        <div class="col-lg-6 mb-4">
            <div class="card revenue-card">
                <div class="card-header">
                    <h6>Monthly Revenue</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center">
                            <div class="revenue-label">This Month</div>
                            <div class="revenue-value">EGP{{ number_format($stats['revenue_this_month'], 2) }}</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="revenue-label">Last Month</div>
                            <div class="revenue-value">EGP{{ number_format($stats['revenue_last_month'], 2) }}</div>
                        </div>
                    </div>
                    @php
                        $revenue_change = $stats['revenue_last_month'] > 0
                            ? (($stats['revenue_this_month'] - $stats['revenue_last_month']) / $stats['revenue_last_month']) * 100
                            : 0;
                    @endphp
                    <div class="mt-4 text-center">
                        @if($revenue_change >= 0)
                            <span class="badge bg-success">
                                <i class="fas fa-arrow-up"></i> {{ number_format($revenue_change, 1) }}% Increase
                            </span>
                        @else
                            <span class="badge bg-danger">
                                <i class="fas fa-arrow-down"></i> {{ number_format(abs($revenue_change), 1) }}% Decrease
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status -->
        <div class="col-lg-6 mb-4">
            <div class="card order-status-card">
                <div class="card-header">
                    <h6>Order Status</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center">
                            <div class="order-label">Pending Orders</div>
                            <div class="order-value">{{ number_format($stats['pending_orders']) }}</div>
                        </div>
                        <div class="col-6 text-center">
                            <div class="order-label">Completed Orders</div>
                            <div class="order-value">{{ number_format($stats['completed_orders']) }}</div>
                        </div>
                    </div>
                    @php
                        $completion_rate = $stats['total_orders'] > 0
                            ? ($stats['completed_orders'] / $stats['total_orders']) * 100
                            : 0;
                    @endphp
                    <div class="mt-4">
                        <div class="order-label text-center mb-2">Completion Rate</div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ $completion_rate }}%"
                                 aria-valuenow="{{ $completion_rate }}"
                                 aria-valuemin="0"
                                 aria-valuemax="100">
                            </div>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">{{ number_format($completion_rate, 1) }}%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>    <!-- Recent Activity -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-6 mb-4">
            <div class="card activity-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6>Recent Orders</h6>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table activity-table">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.orders.show', $order) }}" class="text-decoration-none text-dark">
                                                    #{{ $order->id }}
                                                </a>
                                            </td>
                                            <td>{{ $order->user->name ?? 'Guest' }}</td>
                                            <td>EGP{{ number_format($order->total_amount, 2) }}</td>
                                            <td>
                                                <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $order->created_at->diffForHumans() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-shopping-cart"></i>
                            <p>No orders yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="col-lg-6 mb-4">
            <div class="card activity-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6>Recent Users</h6>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentUsers->count() > 0)
                        <div class="table-responsive">
                            <table class="table activity-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Joined</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentUsers as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </div>
                                                    {{ $user->name }}
                                                </div>
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->created_at->diffForHumans() }}</td>
                                            <td>
                                                <span class="badge bg-{{ $user->email_verified_at ? 'success' : 'warning' }}">
                                                    {{ $user->email_verified_at ? 'Verified' : 'Pending' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-users"></i>
                            <p>No users yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card quick-actions-card">
                <div class="card-header">
                    <h6>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('admin.products.create') }}" class="btn btn-success btn-block">
                                <i class="fas fa-plus me-2"></i>Add Product
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('admin.collections.create') }}" class="btn btn-info btn-block">
                                <i class="fas fa-layer-group me-2"></i>Add Collection
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('admin.orders.index') }}" class="btn btn-warning btn-block">
                                <i class="fas fa-clipboard-list me-2"></i>Manage Orders
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('admin.analytics.index') }}" class="btn btn-primary btn-block">
                                <i class="fas fa-chart-bar me-2"></i>View Analytics
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.stats-card {
    border: 1px solid var(--border-light);
    border-radius: 0;
    background-color: var(--secondary-color);
    transition: all 0.3s ease;
    height: 100%;
}

.stats-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.stats-card .card-body {
    padding: 2rem;
}

.stats-title {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.75rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.stats-value {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 2.5rem;
    letter-spacing: 0.05em;
    color: var(--primary-color);
    margin-bottom: 0;
    line-height: 1;
}

.stats-icon {
    font-size: 2.5rem;
    color: var(--text-muted);
    opacity: 0.3;
}

.revenue-card, .order-status-card {
    border: 1px solid var(--border-light);
    border-radius: 0;
    background-color: var(--secondary-color);
}

.revenue-value, .order-value {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 1.75rem;
    letter-spacing: 0.05em;
    color: var(--primary-color);
    margin-bottom: 0;
}

.revenue-label, .order-label {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.75rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.activity-card {
    border: 1px solid var(--border-light);
    border-radius: 0;
    background-color: var(--secondary-color);
}

.activity-table {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    margin-bottom: 0;
}

.activity-table thead th {
    border-top: none;
    border-bottom: 1px solid var(--border-light);
    color: var(--primary-color);
    font-weight: 300;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.1em;
    padding: 1rem 0.75rem;
    background-color: transparent;
}

.activity-table tbody td {
    border-top: 1px solid var(--border-light);
    padding: 1rem 0.75rem;
    color: var(--primary-color);
    vertical-align: middle;
}

.activity-table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: var(--secondary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.875rem;
    margin-right: 0.75rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: var(--text-muted);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.empty-state p {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 1rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin: 0;
}

.quick-actions-card {
    border: 1px solid var(--border-light);
    border-radius: 0;
    background-color: var(--secondary-color);
}

.progress {
    height: 8px;
    background-color: var(--border-light);
    border-radius: 0;
}

.progress-bar {
    border-radius: 0;
}

.btn-block {
    width: 100%;
}

.page-header {
    border-bottom: 1px solid var(--border-light);
    padding-bottom: 2rem;
    margin-bottom: 2rem;
}

.page-title {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 2.5rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--primary-color);
    margin: 0;
}

.page-meta {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.date-badge, .time-badge {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 0.5rem 1rem;
    border: 1px solid var(--border-light);
    background-color: var(--secondary-color);
    color: var(--primary-color);
}
</style>
@endpush
@endsection
