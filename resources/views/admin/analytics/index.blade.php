@extends('layouts.admin')

@section('title', 'Analytics Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">Analytics Dashboard</h1>
            <div class="d-flex gap-2">
                <select class="form-select" id="dateRange" style="width: auto;">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="365">Last year</option>
                </select>
                <button class="btn btn-outline-secondary" onclick="refreshAnalytics()">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <div class="metric-content">
                    <h3 class="metric-value">{{ $analytics['total_orders'] ?? 0 }}</h3>
                    <p class="metric-label">Total Orders</p>
                    <div class="metric-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+12% from last period</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="metric-content">
                    <h3 class="metric-value">${{ number_format($analytics['total_revenue'] ?? 0, 2) }}</h3>
                    <p class="metric-label">Total Revenue</p>
                    <div class="metric-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+8% from last period</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-box"></i>
                </div>
                <div class="metric-content">
                    <h3 class="metric-value">{{ $analytics['total_products'] ?? 0 }}</h3>
                    <p class="metric-label">Active Products</p>
                    <div class="metric-change neutral">
                        <i class="fas fa-minus"></i>
                        <span>No change</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="metric-card">
                <div class="metric-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="metric-content">
                    <h3 class="metric-value">{{ $analytics['total_customers'] ?? 0 }}</h3>
                    <p class="metric-label">Customers</p>
                    <div class="metric-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <span>+5% from last period</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6>Revenue Trends</h6>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6>Order Status Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Tables -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6>Top Products</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analytics['top_products'] ?? [] as $product)
                                <tr>
                                    <td>
                                        <div class="product-item">
                                            @if($product->images && $product->images->count() > 0)
                                                <img src="{{ optional($product->main_image)->url ?? asset('images/placeholder-product.jpg') }}" alt="{{ $product->name }}" class="product-thumb">
                                            @else
                                                <div class="product-thumb-placeholder">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            @endif
                                            <span>{{ $product->name }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $product->orders_count ?? 0 }}</td>
                                    <td>${{ number_format($product->total_revenue ?? 0, 2) }}</td>
                                </tr>
                                @endforeach
                                @if(empty($analytics['top_products']))
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No data available</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6>Top Collections</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Collection</th>
                                    <th>Products</th>
                                    <th>Orders</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($analytics['top_collections'] ?? [] as $stat)
                                <tr>
                                    <td>
                                        <div class="collection-item">
                                            @if($stat->collection && $stat->collection->images && $stat->collection->images->count() > 0)
                                                <img src="{{ optional($stat->collection->images->first())->url ?? asset('images/picsum/600x800-1-0.jpg') }}" alt="{{ $stat->collection->title }}" class="collection-thumb">
                                            @elseif($stat->collection && $stat->collection->image_path)
                                                <img src="{{ Storage::url($stat->collection->image_path) }}" alt="{{ $stat->collection->title }}" class="collection-thumb">
                                            @else
                                                <div class="collection-thumb-placeholder">
                                                    <i class="fas fa-images"></i>
                                                </div>
                                            @endif
                                            <span>{{ $stat->collection->title ?? 'Unknown' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $stat->products_count ?? 0 }}</td>
                                    <td>{{ $stat->orders_count ?? 0 }}</td>
                                </tr>
                                @endforeach
                                @if(empty($analytics['top_collections']))
                                <tr>
                                    <td colspan="3" class="text-center text-muted">No data available</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6>Recent Special Orders</h6>
                </div>
                <div class="card-body">
                    @if(!empty($analytics['recent_orders']))
                        @foreach($analytics['recent_orders'] as $order)
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    Order #{{ str_pad($order->id, 4, '0', STR_PAD_LEFT) }} from {{ $order->customer_name }}
                                </div>
                                <div class="activity-meta">
                                    {{ $order->created_at->diffForHumans() }}
                                    @if($order->product)
                                        • {{ $order->product->name }}
                                    @endif
                                </div>
                            </div>
                            <div class="activity-status">
                                <span class="status-badge {{ $order->status }}">{{ ucfirst($order->status) }}</span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="empty-state-small">
                            <i class="fas fa-inbox"></i>
                            <p>No recent orders</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h6>Performance Summary</h6>
                </div>
                <div class="card-body">
                    <div class="performance-metrics">
                        <div class="performance-item">
                            <div class="performance-label">Average Order Value</div>
                            <div class="performance-value">${{ number_format($analytics['avg_order_value'] ?? 0, 2) }}</div>
                        </div>

                        <div class="performance-item">
                            <div class="performance-label">Order Completion Rate</div>
                            <div class="performance-value">{{ number_format($analytics['completion_rate'] ?? 0, 1) }}%</div>
                        </div>

                        <div class="performance-item">
                            <div class="performance-label">Most Popular Product</div>
                            <div class="performance-value">{{ $analytics['popular_product'] ?? 'N/A' }}</div>
                        </div>

                        <div class="performance-item">
                            <div class="performance-label">Customer Retention</div>
                            <div class="performance-value">{{ number_format($analytics['retention_rate'] ?? 0, 1) }}%</div>
                        </div>

                        <div class="performance-item">
                            <div class="performance-label">Response Time</div>
                            <div class="performance-value">{{ $analytics['avg_response_time'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.metric-card {
    background: white;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    padding: 1.5rem;
    height: 100%;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.metric-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), #333);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
    flex-shrink: 0;
}

.metric-icon i {
    font-size: 1.5rem;
    color: white;
}

.metric-content {
    flex: 1;
}

.metric-value {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    line-height: 1;
}

.metric-label {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.875rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.metric-change {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.metric-change.positive {
    color: #28a745;
}

.metric-change.negative {
    color: #dc3545;
}

.metric-change.neutral {
    color: var(--text-muted);
}

.product-item, .collection-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.product-thumb, .collection-thumb {
    width: 32px;
    height: 32px;
    border-radius: 4px;
    object-fit: cover;
    flex-shrink: 0;
}

.product-thumb-placeholder, .collection-thumb-placeholder {
    width: 32px;
    height: 32px;
    border-radius: 4px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    flex-shrink: 0;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid var(--border-light);
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.activity-icon i {
    color: white;
    font-size: 0.875rem;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.activity-meta {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.activity-status {
    flex-shrink: 0;
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 2px;
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.status-badge.pending {
    background: #ffc107;
    color: #000;
}

.status-badge.confirmed {
    background: #17a2b8;
    color: white;
}

.status-badge.in_progress {
    background: #fd7e14;
    color: white;
}

.status-badge.completed {
    background: #28a745;
    color: white;
}

.status-badge.cancelled {
    background: #dc3545;
    color: white;
}

.performance-metrics {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.performance-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-light);
}

.performance-item:last-child {
    border-bottom: none;
}

.performance-label {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    color: var(--primary-color);
}

.performance-value {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    color: var(--text-dark);
}

.empty-state-small {
    text-align: center;
    padding: 2rem;
    color: var(--text-muted);
}

.empty-state-small i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: var(--border-light);
}

.empty-state-small p {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    margin-bottom: 0;
}

.form-select {
    border-radius: 0;
    border: 1px solid var(--border-light);
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
}

.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
}

.table th {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.875rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--primary-color);
    border-bottom: 2px solid var(--border-light);
}

.table td {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    color: var(--text-dark);
    vertical-align: middle;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function refreshAnalytics() {
    window.location.reload();
}

// Revenue Chart
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(revenueCtx, {
    type: 'line',
    data: {
        labels: @json($analytics['revenue_chart']['labels'] ?? []),
        datasets: [{
            label: 'Revenue',
            data: @json($analytics['revenue_chart']['data'] ?? []),
            borderColor: 'rgb(0, 0, 0)',
            backgroundColor: 'rgba(0, 0, 0, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: @json($analytics['status_chart']['labels'] ?? []),
        datasets: [{
            data: @json($analytics['status_chart']['data'] ?? []),
            backgroundColor: [
                '#ffc107',
                '#17a2b8',
                '#fd7e14',
                '#28a745',
                '#dc3545'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Date range change handler
document.getElementById('dateRange').addEventListener('change', function() {
    const days = this.value;
    window.location.href = `{{ route('admin.analytics.index') }}?days=${days}`;
});
</script>
@endpush
@endsection
