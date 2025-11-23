@extends('layouts.admin')

@section('title', $product->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">{{ $product->name }}</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-2"></i>Edit Product
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Products
                </a>
            </div>
        </div>
    </div>

    <!-- Product Details -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6>Product Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="product-image-container">
                                @if($product->images && $product->images->count() > 0)
                                    <img src="{{ optional($product->main_image)->url ?? asset('images/placeholder-product.jpg') }}" alt="{{ $product->name }}" class="product-image">
                                @else
                                    <div class="placeholder-image">
                                        <i class="fas fa-image"></i>
                                        <span>No Image</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="product-details">
                                <h4 class="product-name">{{ $product->name }}</h4>

                                <div class="detail-row">
                                    <label>Price:</label>
                                    <span class="product-price">{!! $product->formatted_price !!}</span>
                                </div>

                                <div class="detail-row">
                                    <label>Collection:</label>
                                        @if($product->collection)
                                        <span class="collection-badge">{{ $product->collection->title }}</span>
                                    @else
                                        <span class="text-muted">No Collection</span>
                                    @endif
                                </div>

                                <div class="detail-row">
                                    <label>Visibility:</label>
                                    @if($product->visible)
                                        <span class="status-badge visible">Visible</span>
                                    @else
                                        <span class="status-badge hidden">Hidden</span>
                                    @endif
                                </div>

                                <div class="detail-row">
                                    <label>Created:</label>
                                    <span>{{ $product->created_at->format('M d, Y at h:i A') }}</span>
                                </div>

                                <div class="detail-row">
                                    <label>Last Updated:</label>
                                    <span>{{ $product->updated_at->format('M d, Y at h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($product->description)
                    <div class="row mt-4">
                        <div class="col-12">
                            <label>Description:</label>
                            <div class="product-description">
                                {{ $product->description }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Special Orders (if applicable) -->
            @if($product->specialOrders && $product->specialOrders->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h6>Special Orders</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($product->specialOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.special-orders.show', $order->id) }}" class="text-decoration-none">
                                            #{{ $order->id }}
                                        </a>
                                    </td>
                                    <td>{{ $order->customer_name }}</td>
                                    <td>
                                        <span class="status-badge {{ strtolower($order->status) }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.special-orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary">
                                            View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Product
                        </a>

                        <form method="POST" action="{{ route('admin.products.toggle-visibility', $product) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            @if($product->visible)
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-eye-slash me-2"></i>Hide Product
                                </button>
                            @else
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-eye me-2"></i>Show Product
                                </button>
                            @endif
                        </form>

                        <a href="{{ route('products.show', $product->slug) }}" target="_blank" class="btn btn-outline-info">
                            <i class="fas fa-external-link-alt me-2"></i>View on Site
                        </a>

                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                              onsubmit="return confirm('Are you sure you want to delete this product?')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash me-2"></i>Delete Product
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6>Product Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="stat-item">
                        <span class="stat-label">Special Orders:</span>
                        <span class="stat-value">{{ $product->specialOrders ? $product->specialOrders->count() : 0 }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Pending Orders:</span>
                        <span class="stat-value">{{ $product->specialOrders ? $product->specialOrders->where('status', 'pending')->count() : 0 }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Completed Orders:</span>
                        <span class="stat-value">{{ $product->specialOrders ? $product->specialOrders->where('status', 'completed')->count() : 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
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
    font-size: 3rem;
    margin-bottom: 0.5rem;
}

.product-details {
    padding-left: 1rem;
}

.product-name {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
}

.detail-row {
    display: flex;
    align-items: center;
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
}

.product-price {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    font-size: 1.25rem;
    color: var(--primary-color);
}

.collection-badge {
    background: var(--primary-color);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 2px;
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
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

.status-badge.visible {
    background: #28a745;
    color: white;
}

.status-badge.hidden {
    background: #dc3545;
    color: white;
}

.status-badge.pending {
    background: #ffc107;
    color: #000;
}

.status-badge.completed {
    background: #28a745;
    color: white;
}

.product-description {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    line-height: 1.6;
    color: var(--text-muted);
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
    margin-top: 0.5rem;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border-light);
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.875rem;
    color: var(--primary-color);
}

.stat-value {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    color: var(--primary-color);
}
</style>
@endpush
@endsection
