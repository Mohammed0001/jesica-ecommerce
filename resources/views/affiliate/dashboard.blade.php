@extends('layouts.app')

@section('title', 'Affiliate Dashboard')

@section('content')
<main class="affiliate-page">
    <section class="affiliate-header">
        <div class="container">
            <h1 class="page-title">Affiliate Dashboard</h1>
            <p class="page-subtitle">Your promo codes and the orders placed with them</p>
        </div>
    </section>

    <section class="affiliate-content">
        <div class="container">

            {{-- Summary stats --}}
            <div class="stats-grid">
                <div class="stat-card">
                    <span class="stat-label">Promo Codes</span>
                    <span class="stat-value">{{ $promoCodes->count() }}</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Orders Placed</span>
                    <span class="stat-value">{{ $totalOrders }}</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Revenue Generated</span>
                    <span class="stat-value">{{ number_format($totalRevenue, 2) }} {{ session('currency', 'EGP') }}</span>
                </div>
                <div class="stat-card">
                    <span class="stat-label">Discount Given</span>
                    <span class="stat-value">{{ number_format($totalDiscountGiven, 2) }} {{ session('currency', 'EGP') }}</span>
                </div>
            </div>

            {{-- Promo codes --}}
            <h2 class="section-heading">Your Promo Codes</h2>

            @if($promoCodes->isEmpty())
                <div class="empty-block">
                    <p>No promo code has been assigned to your account yet. Contact the store admin to get one set up.</p>
                </div>
            @else
                <div class="codes-list">
                    @foreach($promoCodes as $code)
                    <div class="code-card">
                        <div class="code-main">
                            <span class="code-text">{{ $code->code }}</span>
                            <span class="code-badge {{ $code->isUsable() ? 'badge-active' : 'badge-inactive' }}">
                                {{ $code->isUsable() ? 'Active' : ($code->active ? 'Unavailable' : 'Inactive') }}
                            </span>
                        </div>
                        <div class="code-details">
                            <span>{{ $code->type === 'percentage' ? $code->value . '% off' : number_format($code->value, 2) . ' ' . session('currency', 'EGP') . ' off' }}</span>
                            <span>{{ $code->orders_count }} order{{ $code->orders_count === 1 ? '' : 's' }}</span>
                            <span>{{ $code->usage_count }} use{{ $code->usage_count === 1 ? '' : 's' }}{{ $code->max_uses ? ' / ' . $code->max_uses : '' }}</span>
                            @if($code->expires_at)
                                <span>Expires {{ $code->expires_at->format('M d, Y') }}</span>
                            @endif
                        </div>
                        @if($code->description)
                            <p class="code-description">{{ $code->description }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            @endif

            {{-- Orders --}}
            <h2 class="section-heading">Orders Using Your Codes</h2>

            @if($orders->count() > 0)
                <div class="table-wrap">
                    <table class="affiliate-table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Date</th>
                                <th>Code</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Discount</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>#{{ $order->order_number }}</td>
                                <td>{{ $order->created_at->format('M d, Y') }}</td>
                                <td>{{ $order->promoCode->code ?? '-' }}</td>
                                <td>{{ $order->user->name ?? $order->guest_name ?? 'Guest' }}</td>
                                <td>
                                    <span class="status-badge {{ $order->status_badge_class }}">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td>{{ $order->formatted_discount }}</td>
                                <td>{{ $order->formatted_total }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($orders->hasPages())
                    <div class="pagination-wrapper">
                        {{ $orders->links('vendor.pagination.bootstrap-5') }}
                    </div>
                @endif
            @else
                <div class="empty-block">
                    <p>No orders have used your promo code{{ $promoCodes->count() === 1 ? '' : 's' }} yet.</p>
                </div>
            @endif
        </div>
    </section>
</main>

@push('styles')
<style>
.affiliate-page {
    font-family: 'futura-pt', sans-serif;
}

.affiliate-header {
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

.affiliate-content {
    padding: 3rem 0;
}

.section-heading {
    font-weight: 300;
    font-size: 1.5rem;
    color: var(--primary-color);
    letter-spacing: 0.02em;
    margin: 2.5rem 0 1.25rem;
}

.section-heading:first-of-type {
    margin-top: 0;
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.stat-card {
    background: white;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.stat-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-muted);
    font-weight: 300;
}

.stat-value {
    font-size: 1.75rem;
    font-weight: 300;
    color: var(--primary-color);
}

/* Promo codes */
.codes-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.code-card {
    background: white;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    padding: 1.5rem 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.code-main {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.75rem;
}

.code-text {
    font-size: 1.5rem;
    font-weight: 300;
    letter-spacing: 0.08em;
    color: var(--primary-color);
}

.code-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 300;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.badge-active {
    background: #d4edda;
    color: #155724;
}

.badge-inactive {
    background: #f8d7da;
    color: #721c24;
}

.code-details {
    display: flex;
    flex-wrap: wrap;
    gap: 1.25rem;
    color: var(--text-muted);
    font-size: 0.9rem;
    font-weight: 200;
}

.code-description {
    margin-top: 0.75rem;
    margin-bottom: 0;
    color: var(--text-muted);
    font-weight: 200;
    font-size: 0.9rem;
}

/* Orders table */
.table-wrap {
    background: white;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    overflow-x: auto;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.affiliate-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 720px;
}

.affiliate-table th,
.affiliate-table td {
    padding: 1rem 1.25rem;
    text-align: left;
    font-weight: 200;
    font-size: 0.9rem;
    border-bottom: 1px solid var(--border-light);
    white-space: nowrap;
}

.affiliate-table th {
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-size: 0.75rem;
    color: var(--text-muted);
    background: #f8f9fa;
}

.affiliate-table tbody tr:last-child td {
    border-bottom: none;
}

.status-badge {
    padding: 0.3rem 0.7rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 300;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.badge-secondary { background: #e2e3e5; color: #383d41; }
.badge-warning { background: #fff3cd; color: #856404; }
.badge-info { background: #cce5ff; color: #004085; }
.badge-success { background: #d4edda; color: #155724; }
.badge-primary { background: #cce5ff; color: #004085; }
.badge-danger { background: #f8d7da; color: #721c24; }

.empty-block {
    background: #f8f9fa;
    border: 1px dashed var(--border-light);
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    color: var(--text-muted);
}

.pagination-wrapper {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
}

@media (max-width: 768px) {
    .page-title {
        font-size: 2.25rem;
    }

    .code-main {
        flex-wrap: wrap;
    }
}
</style>
@endpush
@endsection
