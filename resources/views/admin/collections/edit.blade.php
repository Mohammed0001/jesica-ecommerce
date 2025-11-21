@extends('layouts.admin')

@section('title', 'Edit Collection')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">Edit Collection</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.collections.show', $collection->id) }}" class="btn btn-outline-info">
                    <i class="fas fa-eye me-2"></i>View Collection
                </a>
                <a href="{{ route('admin.collections.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Collections
                </a>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6>Collection Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.collections.update', $collection->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-12 mb-4">
                    <label for="title" class="form-label">Collection Title</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                        id="title" name="title" value="{{ old('title', $collection->title) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-4">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4" required>{{ old('description', $collection->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-4">
                    <label for="image" class="form-label">Collection Image</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                       id="image" name="image" accept="image/*">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave empty to keep current image. Supported formats: JPEG, PNG, JPG, GIF</div>
                            </div>

                            @if($collection->image_path)
                            <div class="col-12 mb-4">
                                <label class="form-label">Current Image</label>
                                <div class="current-image-container">
                                    <img src="{{ Storage::url($collection->image_path) }}" alt="{{ $collection->title }}" class="current-image">
                                    <div class="image-actions">
                                        <a href="{{ Storage::url($collection->image_path) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-external-link-alt"></i> View Full Size
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="col-12 mb-4">
                                <div class="form-check">
                     <input class="form-check-input" type="checkbox" id="visible" name="visible" value="1"
                         {{ old('visible', $collection->visible) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_visible">
                                        Publish this collection (make it visible to customers)
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Collection
                                    </button>
                                    <a href="{{ route('admin.collections.show', $collection->id) }}" class="btn btn-outline-secondary">
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
                    <h6>Collection Guidelines</h6>
                </div>
                <div class="card-body">
                    <div class="tip-item">
                        <h6><i class="fas fa-lightbulb text-warning me-2"></i>Collection Purpose</h6>
                        <p>Collections help organize your products into logical groups that make browsing easier for customers.</p>
                    </div>

                    <div class="tip-item">
                        <h6><i class="fas fa-image text-info me-2"></i>Collection Images</h6>
                        <p>Use high-quality images that represent the style and aesthetic of the collection.</p>
                    </div>

                    <div class="tip-item">
                        <h6><i class="fas fa-edit text-success me-2"></i>Descriptions</h6>
                        <p>Write compelling descriptions that tell the story behind the collection and inspire customers.</p>
                    </div>

                    <div class="tip-item">
                        <h6><i class="fas fa-tag text-primary me-2"></i>Naming</h6>
                        <p>Use memorable, brand-appropriate names that reflect the collection's theme or inspiration.</p>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6>Collection Statistics</h6>
                </div>
                <div class="card-body">
                    <div class="stat-item">
                        <span class="stat-label">Products:</span>
                        <span class="stat-value">{{ $collection->products->count() }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Created:</span>
                        <span class="stat-value">{{ $collection->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Last Updated:</span>
                        <span class="stat-value">{{ $collection->updated_at->format('M d, Y') }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Status:</span>
                        @if($collection->visible)
                            <span class="status-badge visible">Visible</span>
                        @else
                            <span class="status-badge hidden">Hidden</span>
                        @endif
                    </div>
                </div>
            </div>

            @if($collection->products->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <h6>Recent Products</h6>
                </div>
                <div class="card-body">
                    @foreach($collection->products->take(3) as $product)
                    <div class="recent-product">
                        <div class="product-thumbnail-small">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}">
                            @else
                                <div class="placeholder-small">
                                    <i class="fas fa-image"></i>
                                </div>
                            @endif
                        </div>
                        <div class="product-info-small">
                            <h6>{{ $product->name }}</h6>
                            <p>${{ number_format($product->price, 2) }}</p>
                        </div>
                        <div class="product-actions-small">
                            <a href="{{ route('admin.products.show', $product->id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach

                    @if($collection->products->count() > 3)
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.collections.show', $collection->id) }}" class="btn btn-sm btn-outline-secondary">
                            View All {{ $collection->products->count() }} Products
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif
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
    width: 200px;
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

.recent-product {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border-light);
}

.recent-product:last-child {
    border-bottom: none;
}

.product-thumbnail-small {
    width: 40px;
    height: 40px;
    border-radius: 4px;
    overflow: hidden;
    flex-shrink: 0;
}

.product-thumbnail-small img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.placeholder-small {
    width: 100%;
    height: 100%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
}

.product-info-small {
    flex: 1;
}

.product-info-small h6 {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.875rem;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.product-info-small p {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-bottom: 0;
}

.product-actions-small {
    flex-shrink: 0;
}

.invalid-feedback {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.75rem;
}
</style>
@endpush
@endsection
