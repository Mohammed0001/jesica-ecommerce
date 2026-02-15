@extends('layouts.admin')

@section('title', 'Create Product')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Create Product</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Products
            </a>
        </div>
    </div>

    <!-- Create Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6>Product Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-12 mb-4">
                                <label for="title" class="form-label">Product Title</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-4">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-4">
                                <label for="price" class="form-label">Price</label>
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('price') is-invalid @enderror"
                                       id="price" name="price" value="{{ old('price') }}" required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-2 mb-4">
                                <label for="currency" class="form-label">Currency</label>
                                <select id="currency" name="currency" class="form-select @error('currency') is-invalid @enderror">
                                    @foreach(config('currencies.rates') as $code => $rate)
                                        <option value="{{ $code }}" {{ old('currency', 'EGP') === $code ? 'selected' : '' }}>{{ $code }}</option>
                                    @endforeach
                                </select>
                                @error('currency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <label for="collection_id" class="form-label">Collection</label>
                                <select class="form-select @error('collection_id') is-invalid @enderror"
                                        id="collection_id" name="collection_id" required>
                                    <option value="">Select a collection</option>
                                    @foreach($collections as $collection)
                                        <option value="{{ $collection->id }}" {{ old('collection_id') == $collection->id ? 'selected' : '' }}>
                                            {{ $collection->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('collection_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-4">
                                <label for="sku" class="form-label">SKU</label>
                                <input type="text" class="form-control @error('sku') is-invalid @enderror"
                                       id="sku" name="sku" value="{{ old('sku') }}">
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3 mb-4">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror"
                                       id="quantity" name="quantity" value="{{ old('quantity', 1) }}" min="0">
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="d-flex align-items-center">
                                    <label for="size_chart_id" class="form-label mb-0">Size Chart</label>
                                    <a href="{{ Route::has('admin.size-charts.index') ? route('admin.size-charts.index') : url('/admin/size-charts') }}" class="btn btn-sm btn-outline-secondary ms-2" style="height:28px; padding:0 .6rem; line-height:28px;">Manage</a>
                                </div>
                                <select class="form-select @error('size_chart_id') is-invalid @enderror"
                                        id="size_chart_id" name="size_chart_id">
                                    <option value="">No size chart</option>
                                    @foreach(\App\Models\SizeChart::all() as $sizeChart)
                                        <option value="{{ $sizeChart->id }}" {{ old('size_chart_id') == $sizeChart->id ? 'selected' : '' }}>
                                            {{ $sizeChart->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('size_chart_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_one_of_a_kind" name="is_one_of_a_kind" value="1"
                                           {{ old('is_one_of_a_kind') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_one_of_a_kind">
                                        One of a kind product
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 mb-4">
                                <label for="story" class="form-label">Product Story (optional)</label>
                                <textarea class="form-control @error('story') is-invalid @enderror"
                                          id="story" name="story" rows="3">{{ old('story') }}</textarea>
                                @error('story')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-4">
                                <label for="images" class="form-label">Product Images</label>
                                <input type="file" class="form-control @error('images.*') is-invalid @enderror"
                                       id="images" name="images[]" accept="image/*" multiple>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Supported formats: JPEG, PNG, JPG, GIF. Images are automatically compressed.</div>
                            </div>

                            <div class="col-12 mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="visible" name="visible" value="1"
                                           {{ old('visible', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="visible">
                                        Publish this product (make it visible to customers)
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-submit">
                                        <span class="btn-text"><i class="fas fa-save me-2"></i>Create Product</span>
                                        <span class="btn-loading d-none"><i class="fas fa-spinner fa-spin me-2"></i>Creating...</span>
                                    </button>
                                    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="form-loading-overlay d-none">
                        <div class="loading-content">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p>Uploading files & saving...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6>Tips for Creating Products</h6>
                </div>
                <div class="card-body">
                    <div class="tip-item">
                        <h6><i class="fas fa-lightbulb text-warning me-2"></i>Product Name</h6>
                        <p>Use clear, descriptive names that customers can easily search for.</p>
                    </div>

                    <div class="tip-item">
                        <h6><i class="fas fa-image text-info me-2"></i>Product Images</h6>
                        <p>High-quality images increase customer engagement and sales.</p>
                    </div>

                    <div class="tip-item">
                        <h6><i class="fas fa-dollar-sign text-success me-2"></i>Pricing</h6>
                        <p>Research competitor pricing to ensure your products are competitively priced.</p>
                    </div>

                    <div class="tip-item">
                        <h6><i class="fas fa-layer-group text-primary me-2"></i>Collections</h6>
                        <p>Organize products into collections to help customers browse by category.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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

.invalid-feedback {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.75rem;
}

.form-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: inherit;
}

.loading-content {
    text-align: center;
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    color: var(--primary-color);
}

.card {
    position: relative;
}

.btn-submit:disabled {
    opacity: 0.75;
    cursor: not-allowed;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/image-compressor.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form[enctype="multipart/form-data"]');
        if (!form) return;
        form.addEventListener('submit', function () {
            const btn = form.querySelector('.btn-submit');
            const overlay = form.closest('.card-body').querySelector('.form-loading-overlay');
            if (btn) {
                btn.disabled = true;
                btn.querySelector('.btn-text').classList.add('d-none');
                btn.querySelector('.btn-loading').classList.remove('d-none');
            }
            if (overlay) overlay.classList.remove('d-none');
        });
    });
</script>
@endpush
