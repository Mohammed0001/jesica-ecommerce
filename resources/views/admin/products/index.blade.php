@extends('layouts.admin')

@section('title', 'Products Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">Products</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Product
                </a>
            </div>
        </div>
    </div>

    <!-- Products List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6>All Products ({{ $products->total() }})</h6>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" style="width: auto;">
                            <option>All Collections</option>
                            <option>Published</option>
                            <option>Draft</option>
                        </select>
                        <div class="input-group" style="width: 250px;">
                            <input type="text" class="form-control form-control-sm" placeholder="Search products...">
                            <button class="btn btn-outline-secondary btn-sm" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($products->count() > 0)
                        <div class="table-responsive">
                            <table class="table activity-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">Image</th>
                                        <th>Product</th>
                                        <th>Collection</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $product)
                                        <tr>
                                            <td>
                                                    @if($product->images && $product->images->count() > 0)
                                                        <img src="{{ optional($product->main_image)->url ?? asset('images/picsum/600x800-1-0.jpg') }}"
                                                         alt="{{ $product->name }}"
                                                         class="product-thumbnail">
                                                @else
                                                    <div class="product-thumbnail-placeholder">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="product-name">{{ $product->name }}</div>
                                                    <div class="product-description">{{ Str::limit($product->description, 50) }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="collection-badge">{{ $product->collection->title ?? 'No Collection' }}</span>
                                            </td>
                                            <td>
                                                <span class="price-value">{!! $product->formatted_price !!}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-{{ $product->visible ? 'success' : 'secondary' }}">
                                                        {{ $product->visible ? 'Published' : 'Draft' }}
                                                    </span>
                                                    <form method="POST" action="{{ route('admin.products.toggle-visibility', $product) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-link p-0" title="Toggle visibility">
                                                            <i class="fas fa-{{ $product->visible ? 'eye-slash' : 'eye' }}"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                            <td>{{ $product->created_at->format('M j, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.products.show', $product) }}"
                                                       class="btn btn-sm btn-outline-primary" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.products.edit', $product) }}"
                                                       class="btn btn-sm btn-outline-secondary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                                          class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer">
                               {{ $products->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-shopping-bag"></i>
                            <p>No products found</p>
                            <a href="{{ route('admin.products.create') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-2"></i>Create Your First Product
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.product-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid var(--border-light);
}

.product-thumbnail-placeholder {
    width: 50px;
    height: 50px;
    background-color: var(--border-light);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
}

.product-name {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 1rem;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.product-description {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--text-muted);
    line-height: 1.4;
}

.collection-badge {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--primary-color);
    padding: 0.25rem 0.75rem;
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 0;
}

.price-value {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 1.125rem;
    color: var(--primary-color);
}

.btn-group .btn {
    border-radius: 0;
    border-right: none;
}

.btn-group .btn:last-child {
    border-right: 1px solid;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.card-footer {
    background-color: var(--secondary-color);
    border-top: 1px solid var(--border-light);
    padding: 1.5rem;
}

.form-select {
    border-radius: 0;
    border: 1px solid var(--border-light);
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
}

.form-control {
    border-radius: 0;
    border: 1px solid var(--border-light);
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
}

.input-group .btn {
    border-radius: 0;
}
</style>
@endpush
@endsection
