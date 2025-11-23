@extends('layouts.admin')

@section('title', $collection->title)

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="page-header mb-5">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title fw-light display-5 mb-0">{{ $collection->title }}</h1>
            <div class="d-flex gap-3">
                <a href="{{ route('admin.collections.edit', $collection) }}" class="btn btn-outline-dark">
                    <i class="fas fa-edit me-2"></i>Edit Collection
                </a>
                <a href="{{ route('admin.collections.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
            </div>
        </div>
    </div>

    <div class="row g-5">
        <!-- Left: Image Gallery -->
        <div class="col-lg-7">
            <div class="sticky-top" style="top: 2rem;">
                @php
                    $images = $collection->images && $collection->images->count() > 0
                        ? $collection->images
                        : collect([$collection->image_path ? (object)['url' => Storage::url($collection->image_path)] : null])
                             ->filter();

                    $firstImage = $images->first()?->url ?? asset('images/placeholder-collection.jpg');
                @endphp

                <!-- Main Image -->
                <div class="main-collection-image-container mb-4 rounded-3 overflow-hidden shadow-lg">
                    <img id="mainCollectionImage" src="{{ $firstImage }}" class="img-fluid w-100"
                         alt="{{ $collection->title }}" style="height: 500px; object-fit: cover;">
                    @if($images->count() > 1)
                        <div class="image-counter">
                            1 / {{ $images->count() }}
                        </div>
                    @endif
                </div>

                <!-- Horizontal Thumbnails -->
                @if($images->count() > 1)
                    <div class="thumbnails-horizontal d-flex gap-3 overflow-x-auto pb-3">
                        @foreach($images as $index => $image)
                            <div class="thumbnail-item flex-shrink-0 {{ $index === 0 ? 'active' : '' }} border"
                                 onclick="changeCollectionImage('{{ $image->url ?? Storage::url($image) }}', this, {{ $index }})"
                                 role="button">
                                <img src="{{ $image->url ?? Storage::url($image) }}"
                                     class="img-fluid rounded" style="width: 110px; height: 110px; object-fit: cover;">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Right: Collection Info & Actions -->
        <div class="col-lg-5">
            <div class="collection-info">

                <!-- Badges -->
                <div class="d-flex gap-2 mb-4">
                    <span class="badge premium-badge {{ $collection->visible ? 'visible' : 'hidden' }} px-4 py-2">
                        {{ $collection->visible ? 'Published' : 'Hidden' }}
                    </span>
                    <span class="badge bg-light text-dark border px-4 py-2">
                        {{ $collection->products->count() }} Products
                    </span>
                </div>

                <!-- Description -->
                @if($collection->description)
                    <div class="collection-description bg-light p-4 rounded mb-4">
                        <p class="mb-0 text-muted">{!! nl2br(e($collection->description)) !!}</p>
                    </div>
                @endif

                <!-- Stats -->
                <div class="stats-grid mb-5">
                    <div class="stat-item">
                        <span class="label">Created</span>
                        <span class="value font-monospace">{{ $collection->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="label">Last Updated</span>
                        <span class="value font-monospace">{{ $collection->updated_at->diffForHumans() }}</span>
                    </div>
                    <div class="stat-item">
                        <span class="label">Visible Products</span>
                        <span class="value">{{ $collection->products->where('visible', true)->count() }} / {{ $collection->products->count() }}</span>
                    </div>
                    @if($collection->products->count() > 0)
                    <div class="stat-item">
                        <span class="label">Price Range</span>
                        <span class="value">
                            EGP {{ number_format($collection->products->min('price')) }} –
                            EGP {{ number_format($collection->products->max('price')) }}
                        </span>
                    </div>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h6 class="fw-semibold mb-3 text-uppercase tracking-wider text-muted small">Quick Actions</h6>
                    <div class="d-grid gap-3">
                        <a href="{{ route('admin.collections.edit', $collection) }}" class="btn btn-dark btn-lg">
                            <i class="fas fa-edit me-2"></i>Edit Collection
                        </a>

                        <form method="POST" action="{{ route('admin.collections.toggle-visibility', $collection) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn {{ $collection->visible ? 'btn-warning' : 'btn-success' }} btn-lg">
                                <i class="fas fa-eye{{ $collection->visible ? '-slash' : '' }} me-2"></i>
                                {{ $collection->visible ? 'Hide from Site' : 'Publish to Site' }}
                            </button>
                        </form>

                        <a href="{{ route('collections.show', $collection->slug) }}" target="_blank"
                           class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-external-link-alt me-2"></i>View Live
                        </a>

                        <form method="(OnSubmit" action="{{ route('admin.collections.destroy', $collection) }}"
                              onsubmit="return confirm('Delete this collection permanently? Products will remain.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-lg">
                                <i class="fas fa-trash me-2"></i>Delete Collection
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Separator -->
    <div class="my-6">
        <hr class="border-2 opacity-25">
    </div>

    <!-- Products in Collection -->
    <div class="products-section">
        <h2 class="h3 fw-light mb-5 text-center">Products in "{{ $collection->title }}"</h2>

        @if($collection->products->count() > 0)
            <div class="row g-4">
                @foreach($collection->products as $product)
                    <div class="col-md-6 col-lg-4 col-xl-3">
                        <div class="product-admin-card rounded overflow-hidden shadow-sm hover-lift">
                            <div class="position-relative">
                                <img src="{{ $product->main_image?->url ?? asset('images/placeholder-product.jpg') }}"
                                     class="img-fluid w-100" alt="{{ $product->name }}"
                                     style="height: 280px; object-fit: cover;">
                                @if(!$product->visible)
                                    <div class="overlay-badge bg-dark bg-opacity-75 text-white">
                                        Hidden
                                    </div>
                                @endif
                            </div>
                            <div class="p-4 bg-white">
                                <h6 class="fw-medium mb-2 text-truncate">{{ $product->name }}</h6>
                                <p class="text-primary fw-bold mb-3">
                                    {!! $product->formatted_price !!}
                                </p>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.products.show', $product) }}"
                                       class="btn btn-sm btn-outline-dark flex-fill">View</a>
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="btn btn-sm btn-dark flex-fill">Edit</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 bg-light rounded-3">
                <i class="fas fa-box-open fa-3x text-muted mb-4"></i>
                <h5 class="fw-light">No products in this collection yet</h5>
                <p class="text-muted">Start adding products to showcase them here.</p>
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-2"></i>Add First Product
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .main-collection-image-container {
        position: relative;
        background: #000;
        transition: all 0.4s ease;
    }
    .main-collection-image-container:hover img {
        opacity: 0.95;
    }

    .image-counter {
        position: absolute;
        bottom: 1rem; right: 1rem;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.9rem;
        backdrop-filter: blur(10px);
        font-weight: 500;
    }

    .thumbnails-horizontal::-webkit-scrollbar {
        height: 6px;
    }
    .thumbnails-horizontal::-webkit-scrollbar-thumb {
        background: #999;
        border-radius: 10px;
    }

    .thumbnail-item {
        width: 110px; height: 110px;
        cursor: pointer;
        opacity: 0.6;
        transition: all 0.3s ease;
        border: 3px solid transparent !important;
    }
    .thumbnail-item:hover,
    .thumbnail-item.active {
        opacity: 1;
        border-color: #000 !important;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .premium-badge {
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 50px;
    }
    .premium-badge.visible { background: #d4edda; color: #155724; }
    .premium-badge.hidden { background: #f8d7da; color: #721c24; }

    .stats-grid {
        background: #f8f9fa;
        padding: 1.5rem;
        border-radius: 12px;
        border-left: 4px solid #333;
    }
    .stat-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid #eee;
    }
    .stat-item:last-child { border-bottom: none; }
    .stat-item .label { color: #666; font-weight: 500; }
    .stat-item .value { font-weight: 600; }

    .product-admin-card {
        transition: all 0.4s ease;
        border: none !important;
    }
    .hover-lift:hover {
        transform: translateY(-12px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12) !important;
    }

    .overlay-badge {
        position: absolute;
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
    function changeCollectionImage(src, element, index) {
        const mainImg = document.getElementById('mainCollectionImage');
        const counter = document.querySelector('.image-counter');

        mainImg.style.opacity = 0.6;
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = 1;
            if(counter) counter.textContent = (index + 1) + ' / ' + {{ $images->count() }};

            document.querySelectorAll('.thumbnail-item').forEach(t => t.classList.remove('active'));
            element.classList.add('active');
        }, 200);
    }
</script>
@endpush
