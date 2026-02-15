@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <main class="product-page">
        <!-- Breadcrumb -->
        <section class="breadcrumb-section">
            <div class="container">
                <nav class="breadcrumb-nav">
                    <a href="{{ route('home') }}">Home</a>
                    <span class="separator">→</span>
                    <a href="{{ route('collections.index') }}">Collections</a>
                    @if ($product->collection)
                        <span class="separator">→</span>
                        <a href="{{ route('collections.show', $product->collection->slug) }}">
                            {{ $product->collection->title }}
                        </a>
                    @endif
                    <span class="separator">→</span>
                    <span class="current">{{ $product->name }}</span>
                </nav>
            </div>
        </section>

        <!-- Product Details -->
        <section class="product-details-section">
            <div class="container">
                <div class="row g-5">
                    <!-- Left: Image Gallery -->
                    <div class="col-lg-6">
                        <div class="product-gallery sticky-top" style="top: 2rem;">
                            @php
                                $mainImageUrl = $product->main_image
                                    ? $product->main_image->url
                                    : asset('images/picsum/600x800-1-0.jpg');
                                $images = $product->images && $product->images->count() > 0
                                    ? $product->images->prepend($product->main_image)
                                    : collect([$product->main_image ?? (object)['url' => $mainImageUrl]]);
                            @endphp

                            <!-- Main Image -->
                            <div class="main-image-container mb-4">
                                <img id="mainImage" src="{{ $mainImageUrl }}" class="main-image img-fluid" alt="{{$product->name }}" loading="lazy">
                                @if($images->count() > 1)
                                    <div class="image-counter">
                                        1 / {{ $images->count() }}
                                    </div>
                                @endif
                            </div>

                            <!-- Horizontal Thumbnails -->
                            @if($images->count() > 1)
                                <div class="thumbnails-horizontal d-flex gap-3 overflow-x-auto pb-2">
                                    @foreach($images as $index => $image)
                                        <div class="thumbnail-item flex-shrink-0 {{ $index === 0 ? 'active' : '' }}"
                                             onclick="changeMainImage('{{ $image->url }}', this, {{ $index }})"
                                             role="button" tabindex="0">
                                            <img src="{{ $image->url }}"
                                                 class="thumbnail-img img-fluid"
                                                 style="height: 200px; width: 200px; object-fit: cover;"
                                                 alt="{{ $product->name }} - View {{ $index + 1 }}"
                                                 loading="lazy">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Product Info -->
                    <div class="col-lg-6">
                        <div class="product-info ps-lg-4">

                            <!-- Badges -->
                            @if ($product->is_one_of_a_kind || $product->quantity <= 5)
                                <div class="product-badges mb-3">
                                    @if($product->is_one_of_a_kind)
                                        <span class="badge premium-badge unique" style="border-radius:0;">One of a Kind</span>

                                    @elseif($product->quantity <= 5 && $product->quantity > 0)
                                        <span class="badge premium-badge limited" style="border-radius:0;">Limited Stock</span>
                                    @elseif($product->quantity <= 0)
                                        <span class="badge premium-badge sold-out" style="border-radius:0;">Sold Out</span>
                                    @endif
                                </div>
                            @endif

                            <h1 class="product-title display-5 fw-light mb-4">{{ $product->name }}</h1>

                            <!-- Price -->
                            <div class="product-pricing mb-4 d-flex align-items-baseline gap-3">
                                <span class="current-price h3 fw-semibold text-dark">{!! $product->formatted_price !!}</span>
                                @if($product->compare_price && $product->compare_price > $product->price)
                                    <span class="original-price text-muted text-decoration-line-through">
                                        EGP {{ number_format($product->compare_price, 0) }}
                                    </span>
                                    <span class="discount-badge bg-danger text-white px-3 py-1 rounded-pill small fw-bold">
                                        {{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}% OFF
                                    </span>
                                @endif
                            </div>

                            <!-- Description -->
                            @if($product->description)
                                <div class="product-description mb-4 text-muted">
                                    {!! nl2br(e($product->description)) !!}
                                </div>
                            @endif

                            <!-- Story -->
                            @if($product->story)
                                <div class="product-story bg-light p-4 rounded mb-4">
                                    <h3 class="h5 fw-semibold mb-3">The Story</h3>
                                    <p class="small text-muted mb-0">{{ $product->story }}</p>
                                </div>
                            @endif

                            <!-- Size Selection -->
                            @if($product->sizes && $product->sizes->where('quantity', '>', 0)->count() > 0)
                                <div class="size-selection mb-4">
                                    <h3 class="h6 fw-semibold mb-3">Select Size</h3>
                                    <div class="d-flex gap-3 align-items-center">
                                        <select id="size-select" class="form-select premium-select" style="width: auto;" required>
                                            <option value="">Choose size</option>
                                            @foreach($product->sizes->where('quantity', '>', 0) as $size)
                                                <option value="{{ $size->size_label }}">
                                                    {{ $size->size_label }}
                                                    @if($size->quantity <= 5) ({{ $size->quantity }} left) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#sizeGuideModal"
                                           class="text-decoration-underline small text-muted">Size Guide</a>
                                    </div>
                                </div>
                            @elseif($product->sizeChart)
                                <div class="mb-4">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#sizeGuideModal"
                                       class="text-decoration-underline small text-muted">Size Guide</a>
                                </div>
                            @endif

                            <!-- Add to Cart -->
                            @if($product->quantity > 0)
                                <div class="cart-section mb-5">
                                    <form id="addToCartForm" action="{{ route('cart.add') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="size_label" id="size_label" value="">

                                        <div class="d-flex gap-3 align-items-center">
                                            <button type="submit" style="border-radius:0;" class="btn-premium-add-to-cart">
                                                <span>Add to Cart</span>
                                            </button>
                                            <button type="button" style="display: none;" class="btn-wishlist-outline"
                                                    onclick="toggleWishlist({{ $product->id }})">
                                                <i class="far fa-heart"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                <button class="btn-premium-sold-out w-100" style="border-radius:0;" disabled>Sold Out</button>
                            @endif

                            <!-- Product Details -->
                            <div class="product-details-accordion">
                                <div class="detail-item border-top pt-4">
                                    <h4 class="h6 fw-semibold mb-3">Product Details</h4>
                                    <div class="small text-muted">
                                        <div class="d-flex justify-content-between py-1"><span>SKU</span><span>{{ $product->sku }}</span></div>
                                        @if($product->collection)
                                            <div class="d-flex justify-content-between py-1"><span>Collection</span><span>{{ $product->collection->title }}</span></div>
                                        @endif
                                        <div class="d-flex justify-content-between py-1"><span>Availability</span>
                                            <span>{{ $product->quantity > 0 ? $product->quantity . ' in stock' : 'Out of stock' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="detail-item border-top pt-4">
                                    <h4 class="h6 fw-semibold mb-3">Care & Shipping</h4>
                                    <ul class="small text-muted list-unstyled">
                                        <li class="mb-2">• Professional dry clean recommended</li>

                                        <li class="mb-2">• 14-day return policy</li>
                                        <li>• Authentic handcrafted piece</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Horizontal Line Separator -->
        <div class="container my-5">
            <hr class="border-2 opacity-50">
        </div>

        <!-- Featured / Related Products -->
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <section class="featured-products-section py-5 bg-light">
                <div class="container">
                    <h2 class="section-title text-center mb-5 fw-light display-6">You May Also Like</h2>
                    <div class="row g-4">
                        @foreach($relatedProducts->take(6) as $relatedProduct)
                            <div class="col-6 col-md-4 col-lg-3">
                                <a href="{{ route('products.show', $relatedProduct->slug) }}" class="text-decoration-none">
                                    <div class="featured-product-card bg-white overflow-hidden rounded shadow-sm hover-lift">
                                        <div class="position-relative">
                                            <img src="{{ $relatedProduct->main_image?->url ?? asset('images/placeholder.jpg') }}"
                                                 class="img-fluid w-100" alt="{{ $relatedProduct->name }}"
                                                 style="height: 320px; object-fit: cover;">
                                        </div>
                                        <div class="p-3 text-center">
                                            <h3 class="h6 fw-medium text-dark mb-1">{{ Str::limit($relatedProduct->name, 40) }}</h3>
                                            <p class="text-muted small mb-0">{!! $relatedProduct->formatted_price !!}</p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </main>

    <!-- Size Guide / Size Chart Modal -->
    @if($product->sizeChart)
        <div class="modal fade" id="sizeGuideModal" tabindex="-1" aria-labelledby="sizeGuideModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="sizeGuideModalLabel">Size Guide</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if($product->sizeChart)
                            <div class="row">
                                <div class="col-md-6">
                                    @if($product->sizeChart->image_url)
                                        <img src="{{ $product->sizeChart->image_url }}" alt="{{ $product->sizeChart->name }}" class="img-fluid mb-3" />
                                    @endif
                                    @if($product->sizeChart->name)
                                        <h6 class="mb-2">{{ $product->sizeChart->name }}</h6>
                                    @endif
                                    @if(!empty($product->sizeChart->measurements))
                                        <p class="small text-muted mb-2">Measurements (size → key/value)</p>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    @php
                                        $measurements = $product->sizeChart->measurements ?? [];
                                    @endphp

                                    @if(is_array($measurements) && count($measurements) > 0)
                                        @php
                                            // Determine if measurements are keyed by size (associative) or list of objects
                                            $isAssoc = array_keys($measurements) !== range(0, count($measurements) - 1);
                                        @endphp

                                        @if($isAssoc)
                                            @php
                                                // Collect all measurement keys
                                                $allKeys = [];
                                                foreach($measurements as $sizeLabel => $vals) {
                                                    if(is_array($vals)) {
                                                        $allKeys = array_unique(array_merge($allKeys, array_keys($vals)));
                                                    }
                                                }
                                            @endphp

                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Size</th>
                                                            @foreach($allKeys as $k)
                                                                <th class="text-uppercase small">{{ $k }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($measurements as $sizeLabel => $vals)
                                                            <tr>
                                                                <td class="align-middle">{{ $sizeLabel }}</td>
                                                                @foreach($allKeys as $k)
                                                                    <td>{{ isset($vals[$k]) ? $vals[$k] : '—' }}</td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            {{-- measurements are a list (array) of objects with size/keys --}}
                                            <div class="table-responsive">
                                                <table class="table table-sm">
                                                    <thead>
                                                        <tr>
                                                            @php
                                                                $first = $measurements[0];
                                                                $keys = is_array($first) ? array_keys($first) : [];
                                                            @endphp
                                                            @foreach($keys as $k)
                                                                <th class="text-uppercase small">{{ $k }}</th>
                                                            @endforeach
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($measurements as $row)
                                                            <tr>
                                                                @foreach($keys as $k)
                                                                    <td>{{ $row[$k] ?? '—' }}</td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    @else
                                        <p class="text-muted">No measurements available for this size chart.</p>
                                    @endif
                                </div>
                            </div>
                        @else
                            <p class="text-muted">Size guide is not available for this product.</p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('styles')
<style>
    /* Premium Product Page - Clean & Luxe */
    .product-page { font-family: 'Futura PT', 'Helvetica Neue', sans-serif; background: #fff; color: #1a1a1a; }

    .main-image-container {
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        background: #000;
    }
    .main-image { transition: transform 0.6s ease; }
    .main-image:hover { transform: scale(1.02); }

    .image-counter {
        position: absolute;
        bottom: 16px; right: 16px;
        background: rgba(0,0,0,0.7);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        backdrop-filter: blur(8px);
    }

    .thumbnails-horizontal::-webkit-scrollbar { height: 4px; }
    .thumbnails-horizontal::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
    .thumbnails-horizontal::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }

    .thumbnail-item {
        width: 100px; height: 100px;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid transparent;
        cursor: pointer;
        transition: all 0.3s ease;
        opacity: 0.6;
    }
    .thumbnail-item.active,
    .thumbnail-item:hover {
        opacity: 1;
        border-color: #000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .thumbnail-img { width: 100%; height: 100%; object-fit: cover; }

    /* Premium Add to Cart Button */
    .btn-premium-add-to-cart {
        background: #000;
        color: white;
        border: none;
        padding: 16px 48px;
        font-size: 1.1rem;
        font-weight: 600;
        letter-spacing: 1px;
        border-radius: 6px;
        transition: all 0.4s ease;
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        text-transform: uppercase;
    }
    .btn-premium-add-to-cart:hover {
        background: #1a1a1a;
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.3);
    }

    .btn-wishlist-outline {
        width: 56px; height: 56px;
        border: 2px solid #ddd;
        background: white;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.3s ease;
    }
    .btn-wishlist-outline:hover { border-color: #000; color: #e74c3c; }

    .premium-badge {
        font-size: 0.8rem;
        padding: 6px 14px;
        border-radius: 50px;
        font-weight: 600;
    }
    .premium-badge.unique { background: #fff8e1; color: #b8860b; }
    .premium-badge.limited { background: #ffebee; color: #c62828; }
    .premium-badge.sold-out { background: #eeeeee; color: #777; }

    .premium-select {
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        padding: 12px 16px;
        min-width: 200px;
    }

    .featured-product-card {
        transition: transform 0.4s ease, box-shadow 0.4s ease;
    }
    .featured-product-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.12) !important;
    }

    .hover-lift { transition: all 0.3s ease; }
    .hover-lift:hover { transform: translateY(-8px); }
</style>
@endpush

@push('scripts')
<script>
    function changeMainImage(src, element, index) {
        const mainImg = document.getElementById('mainImage');
        const counter = document.querySelector('.image-counter');

        mainImg.style.opacity = 0.5;
        setTimeout(() => {
            mainImg.src = src;
            mainImg.style.opacity = 1;
            if(counter) counter.textContent = (index + 1) + ' / ' + {{ $images->count() }};
            document.querySelectorAll('.thumbnail-item').forEach(t => t.classList.remove('active'));
            element.classList.add('active');
        }, 200);
    }

    // Auto-select first thumbnail
    document.addEventListener('DOMContentLoaded', () => {
        const firstThumb = document.querySelector('.thumbnail-item');
        if(firstThumb) firstThumb.classList.add('active');

        // Handle size selection
        const sizeSelect = document.getElementById('size-select');
        if (sizeSelect) {
            sizeSelect.addEventListener('change', function() {
                document.getElementById('size_label').value = this.value;
            });
        }

        // Handle add to cart form submission
        const addToCartForm = document.getElementById('addToCartForm');
        if (addToCartForm) {
            addToCartForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Check if size is required and selected
                const sizeSelect = document.getElementById('size-select');
                if (sizeSelect && !document.getElementById('size_label').value) {
                    showNotification('Please select a size', 'error');
                    return;
                }

                const formData = new FormData(this);

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification(data.message, 'success');
                        updateCartBadge(data.cartCount);
                    } else {
                        showNotification(data.message || 'Error adding to cart', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error adding to cart', 'error');
                });
            });
        }
    });

    function updateCartBadge(count) {
        const cartBadge = document.querySelector('.cart-badge');
        if (cartBadge) {
            cartBadge.textContent = count;
            cartBadge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    }

    function showNotification(message, type = 'info') {
        const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 80px; right: 20px; z-index: 9999; max-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }
</script>
@endpush
