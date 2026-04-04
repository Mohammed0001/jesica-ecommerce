@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="container py-4" style="max-width:700px;">
    <h1 class="h3 fw-bold mb-1">Site Settings</h1>
    <p class="text-muted small mb-4">Manage global fees, taxes, and homepage assets.</p>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ───── Fees & Taxes ───── --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Fees &amp; Taxes</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.update') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Delivery Fee <small class="text-muted">(display currency)</small></label>
                        <div class="input-group">
                            <span class="input-group-text">EGP</span>
                            <input name="delivery_fee" type="number" step="0.01" min="0"
                                   class="form-control" required value="{{ old('delivery_fee', $delivery_fee) }}">
                        </div>
                        <div class="form-text">Global fallback for Egypt cities not in Delivery Areas.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">International Delivery Fee <small class="text-muted">(display currency)</small></label>
                        <div class="input-group">
                            <span class="input-group-text">EGP</span>
                            <input name="international_delivery_fee" type="number" step="0.01" min="0"
                                   class="form-control" required value="{{ old('international_delivery_fee', $international_delivery_fee ?? 50) }}">
                        </div>
                        <div class="form-text">Unified fee for all countries outside Egypt.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Free Delivery Threshold <small class="text-muted">(display currency)</small></label>
                        <div class="input-group">
                            <span class="input-group-text">EGP</span>
                            <input name="delivery_threshold" type="number" step="0.01" min="0"
                                   class="form-control" required value="{{ old('delivery_threshold', $delivery_threshold) }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tax Percentage</label>
                        <div class="input-group">
                            <input name="tax_percentage" type="number" step="0.01" min="0"
                                   class="form-control" required value="{{ old('tax_percentage', $tax_percentage) }}">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Service Fee Percentage</label>
                        <div class="input-group">
                            <input name="service_fee_percentage" type="number" step="0.01" min="0"
                                   class="form-control" required value="{{ old('service_fee_percentage', $service_fee_percentage) }}">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Settings</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ───── Hero Image ───── --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"><i class="fas fa-image me-2"></i>Homepage Hero Image</h5>
        </div>
        <div class="card-body">

            {{-- Current preview --}}
            <div class="mb-4">
                <p class="form-label fw-semibold mb-2">Current Hero Image</p>
                @php
                    $heroSrc = $hero_image
                        ? asset($hero_image)
                        : asset('images/hero-background.jpeg');
                @endphp
                <div class="position-relative" style="max-width:100%; border:1px solid #dee2e6; border-radius:6px; overflow:hidden;">
                    <img id="heroPreview"
                         src="{{ $heroSrc }}"
                         alt="Hero background"
                         style="width:100%; max-height:320px; object-fit:cover; display:block;">
                    @if(!$hero_image)
                        <span class="badge bg-secondary position-absolute top-0 start-0 m-2">Default image</span>
                    @else
                        <span class="badge bg-success position-absolute top-0 start-0 m-2">Custom image</span>
                    @endif
                </div>
            </div>

            {{-- Upload form --}}
            <form method="POST"
                  action="{{ route('admin.settings.hero.upload') }}"
                  enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="hero_image_input" class="form-label fw-semibold">Upload New Hero Image</label>
                    <input type="file"
                           name="hero_image"
                           id="hero_image_input"
                           class="form-control @error('hero_image') is-invalid @enderror"
                           accept="image/jpeg,image/png,image/webp"
                           onchange="previewHero(this)">
                    <div class="form-text">
                        JPG, PNG, or WebP &bull; Max 5 MB &bull; Recommended: landscape, 1920×1080 or wider.
                    </div>
                    @error('hero_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Live preview of selected file --}}
                <div id="heroNewPreview" class="mb-3" style="display:none;">
                    <p class="small text-muted mb-1">Preview of selected file:</p>
                    <img id="heroNewImg"
                         src=""
                         alt="New hero preview"
                         style="max-width:100%; max-height:200px; border-radius:4px; border:1px solid #dee2e6; object-fit:cover;">
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Upload &amp; Set Hero
                    </button>
                </div>
            </form>
            @if($hero_image)
                <form method="POST" action="{{ route('admin.settings.hero.reset') }}" class="mt-2"
                      onsubmit="return confirm('Reset to the default hero image?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-undo me-1"></i> Reset to Default
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="mt-2">
        <a href="{{ route('admin.delivery-areas.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-map-marker-alt me-1"></i> Manage Delivery Areas
        </a>
        <a href="{{ route('home') }}" target="_blank" class="btn btn-sm btn-outline-secondary ms-2">
            <i class="fas fa-external-link-alt me-1"></i> View Homepage
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
function previewHero(input) {
    const preview = document.getElementById('heroNewPreview');
    const img = document.getElementById('heroNewImg');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            img.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}
</script>
@endpush
