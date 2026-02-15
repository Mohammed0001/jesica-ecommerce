@extends('layouts.admin')

@section('title', 'Create Collection')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-title">Create Collection</h1>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.collections.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Collections
            </a>
        </div>
    </div>

    <!-- Create Form -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h6>Collection Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.collections.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-12 mb-4">
                                <label for="title" class="form-label">Collection Title</label>
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

                            <div class="col-md-6 mb-4">
                                <label for="release_date" class="form-label">Release Date</label>
                                <input type="date" class="form-control @error('release_date') is-invalid @enderror"
                                       id="release_date" name="release_date" value="{{ old('release_date', now()->format('Y-m-d')) }}" required>
                                @error('release_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-4">
                                <label for="images" class="form-label">Collection Images</label>
                                <input type="file" class="form-control @error('images.*') is-invalid @enderror"
                                       id="images" name="images[]" accept="image/*" multiple>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Supported formats: JPEG, PNG, JPG, GIF. Images are automatically compressed.</div>
                            </div>

                            <div class="col-12 mb-4">
                                <label for="pdf" class="form-label">Collection PDF (optional)</label>
                                <input type="file" class="form-control @error('pdf') is-invalid @enderror"
                                       id="pdf" name="pdf" accept="application/pdf">
                                @error('pdf')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Upload a PDF lookbook or catalog for the collection. Max size: 10MB</div>
                            </div>

                            <div class="col-12 mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="visible" name="visible" value="1"
                                           {{ old('visible', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="visible">
                                        Publish this collection (make it visible to customers)
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-submit">
                                        <span class="btn-text"><i class="fas fa-save me-2"></i>Create Collection</span>
                                        <span class="btn-loading d-none"><i class="fas fa-spinner fa-spin me-2"></i>Creating...</span>
                                    </button>
                                    <a href="{{ route('admin.collections.index') }}" class="btn btn-outline-secondary">
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
