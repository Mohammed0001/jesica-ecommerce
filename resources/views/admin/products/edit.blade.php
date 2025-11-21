@extends('layouts.admin')

@section('title', 'Edit Product')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">Edit Product</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-outline-info">
                    <i class="fas fa-eye me-2"></i>View Product
                </a>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Products
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6>Product Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.products.update', $product->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8 mb-4">
                                <label for="name" class="form-label">Product Name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-4">
                                <label for="price" class="form-label">Price ($)</label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror"
                                       id="price" name="price" value="{{ old('price', $product->price) }}"
                                       step="0.01" min="0" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-4">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4" required>{{ old('description', $product->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="collection_id" class="form-label">Collection</label>
                                <select class="form-select @error('collection_id') is-invalid @enderror"
                                        id="collection_id" name="collection_id">
                                    <option value="">Select a collection (optional)</option>
                                    @foreach($collections as $collection)
                                        <option value="{{ $collection->id }}"
                                                {{ old('collection_id', $product->collection_id) == $collection->id ? 'selected' : '' }}>
                                            {{ $collection->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('collection_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="image" class="form-label">Product Image</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                       id="image" name="image" accept="image/*">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave empty to keep current image. Supported formats: JPEG, PNG, JPG, GIF</div>
                            </div>

                            @if($product->image)
                            <div class="col-12 mb-4">
                                <label class="form-label">Current Image</label>
                                <div class="current-image-container">
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="current-image">
                                    <div class="image-actions">
                                        <a href="{{ Storage::url($product->image) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-external-link-alt"></i> View Full Size
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="col-12 mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_visible" name="is_visible" value="1"
                                           {{ old('is_visible', $product->is_visible) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_visible">
                                        Publish this product (make it visible to customers)
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Product
                                    </button>
                                    <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-outline-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6>Product Tips</h6>
                </div>
                <div class="card-body">
                    <div class="tip-item">
                        <h6><i class="fas fa-tag text-primary me-2"></i>Product Names</h6>
                        <p>Use descriptive, searchable names that clearly identify the product.</p>
                    </div>

                    <div class="tip-item">
                        <h6><i class="fas fa-dollar-sign text-success me-2"></i>Pricing</h6>
                        <p>Ensure pricing reflects the quality and uniqueness of your handmade items.</p>
                    </div>

                    <div class="tip-item">
                        <h6><i class="fas fa-image text-info me-2"></i>Images</h6>
                        <p>High-quality images showcase your work and help customers make purchasing decisions.</p>
                    </div>

                    <div class="tip-item">
                        <h6><i class="fas fa-list text-warning me-2"></i>Collections</h6>
                        <p>Organize products into collections to help customers find related items.</p>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6>Product Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="stat-item">
                        <span class="stat-label">Created:</span>
                        <span class="stat-value">{{ $product->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Last Updated:</span>
                        <span class="stat-value">{{ $product->updated_at->format('M d, Y') }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Special Orders:</span>
                        <span class="stat-value">{{ $product->specialOrders->count() }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Status:</span>
                        @if($product->is_visible)
                            <span class="status-badge visible">Visible</span>
                        @else
                            <span class="status-badge hidden">Hidden</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
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
    padding: 0.75rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
}

.form-text {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.75rem;
    color: var(--text-muted);
}

.form-check-label {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    color: var(--primary-color);
}

.current-image-container {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.current-image {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid var(--border-light);
}

.image-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.tip-item {
    margin-bottom: 1.5rem;
}

.tip-item:last-child {
    margin-bottom: 0;
}

.tip-item h6 {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.875rem;
    letter-spacing: 0.05em;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.tip-item p {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--text-muted);
    margin-bottom: 0;
    line-height: 1.5;
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

.invalid-feedback {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.75rem;
}
</style>
@endpush
@endsection
