@extends('layouts.admin')

@section('title', $collection->title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">{{ $collection->title }}</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.collections.edit', $collection->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit me-2"></i>Edit Collection
                </a>
                <a href="{{ route('admin.collections.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Collections
                </a>
            </div>
        </div>
    </div>

    <!-- Collection Details -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h6>Collection Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="collection-image-container">
                                @if($collection->image_path)
                                    <img src="{{ asset('images/' . $collection->image_path) }}" alt="{{ $collection->title }}" class="collection-image">
                                @else
                                    <div class="placeholder-image">
                                        <i class="fas fa-images"></i>
                                        <span>No Image</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="collection-details">
                                <h4 class="collection-name">{{ $collection->title }}</h4>

                                <div class="detail-row">
                                    <label>Products:</label>
                                    <span class="product-count">{{ $collection->products->count() }} items</span>
                                </div>

                                <div class="detail-row">
                                    <label>Visibility:</label>
                                    @if($collection->visible)
                                        <span class="status-badge visible">Visible</span>
                                    @else
                                        <span class="status-badge hidden">Hidden</span>
                                    @endif
                                </div>

                                <div class="detail-row">
                                    <label>Created:</label>
                                    <span>{{ $collection->created_at->format('M d, Y at h:i A') }}</span>
                                </div>

                                <div class="detail-row">
                                    <label>Last Updated:</label>
                                    <span>{{ $collection->updated_at->format('M d, Y at h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($collection->description)
                    <div class="row mt-4">
                        <div class="col-12">
                            <label>Description:</label>
                            <div class="collection-description">
                                {{ $collection->description }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Products in this Collection -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6>Products in this Collection</h6>
                    @if($collection->products->count() > 0)
                        <span class="badge bg-primary">{{ $collection->products->count() }} products</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($collection->products->count() > 0)
                        <div class="row">
                            @foreach($collection->products as $product)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="product-card">
                                    <div class="product-image-wrapper">
                                        @if($product->image)
                                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="product-thumbnail">
                                        @else
                                            <div class="product-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                        @if(!$product->visible)
                                            <div class="product-overlay">
                                                <span class="hidden-badge">Hidden</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="product-info">
                                        <h6 class="product-title">{{ $product->name }}</h6>
                                        <p class="product-price">${{ number_format($product->price, 2) }}</p>
                                        <div class="product-actions">
                                            <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-outline-secondary">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <h5>No Products Yet</h5>
                            <p>This collection doesn't have any products assigned to it yet.</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add First Product
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6>Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.collections.edit', $collection->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Collection
                        </a>

                        <form method="POST" action="{{ route('admin.collections.toggleVisibility', $collection->id) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            @if($collection->visible)
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="fas fa-eye-slash me-2"></i>Hide Collection
                                </button>
                            @else
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-eye me-2"></i>Show Collection
                                </button>
                            @endif
                        </form>

                        <a href="{{ route('collections.show', $collection->slug) }}" target="_blank" class="btn btn-outline-info">
                            <i class="fas fa-external-link-alt me-2"></i>View on Site
                        </a>

                        <form method="POST" action="{{ route('admin.collections.destroy', $collection->id) }}"
                              onsubmit="return confirm('Are you sure you want to delete this collection? Products will not be deleted.')" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-trash me-2"></i>Delete Collection
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6>Collection Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="stat-item">
                        <span class="stat-label">Total Products:</span>
                        <span class="stat-value">{{ $collection->products->count() }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Visible Products:</span>
                        <span class="stat-value">{{ $collection->products->where('visible', true)->count() }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Price Range:</span>
                        @if($collection->products->count() > 0)
                            <span class="stat-value">
                                ${{ number_format($collection->products->min('price'), 2) }} -
                                ${{ number_format($collection->products->max('price'), 2) }}
                            </span>
                        @else
                            <span class="stat-value text-muted">N/A</span>
                        @endif
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Average Price:</span>
                        @if($collection->products->count() > 0)
                            <span class="stat-value">${{ number_format($collection->products->avg('price'), 2) }}</span>
                        @else
                            <span class="stat-value text-muted">N/A</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.collection-image-container {
    position: relative;
    background: #f8f9fa;
    border-radius: 4px;
    overflow: hidden;
    aspect-ratio: 16/9;
    display: flex;
    align-items: center;
    justify-content: center;
}

.collection-image {
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

.collection-details {
    padding-left: 1rem;
}

.collection-name {
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

.product-count {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    color: var(--primary-color);
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

.collection-description {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    line-height: 1.6;
    color: var(--text-muted);
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 4px;
    margin-top: 0.5rem;
}

.product-card {
    border: 1px solid var(--border-light);
    border-radius: 4px;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.product-image-wrapper {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
}

.product-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    color: var(--text-muted);
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
}

.hidden-badge {
    background: #dc3545;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 2px;
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.product-info {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-title {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.product-price {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    color: var(--primary-color);
    margin-bottom: 1rem;
    flex: 1;
}

.product-actions {
    display: flex;
    gap: 0.5rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
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
