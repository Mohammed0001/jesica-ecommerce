@extends('layouts.app')

@section('title', $collection->title)

@section('content')
<main class="collection-page">
    <!-- Collection Header -->
    <section class="collection-hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 order-lg-2">
                    <div class="collection-image-container">
                        @if($collection->image_path)
                            <img src="{{ asset('images/' . $collection->image_path) }}"
                                 class="collection-hero-image"
                                 alt="{{ $collection->title }}"
                                 loading="lazy">
                        @else
                            <div class="collection-placeholder">
                                <i class="fas fa-images"></i>
                                <span>{{ $collection->title }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6 order-lg-1">
                    <div class="collection-info">
                        <!-- Breadcrumb -->
                        <nav class="breadcrumb-nav">
                            <a href="{{ route('home') }}">Home</a>
                            <span class="separator">→</span>
                            <a href="{{ route('collections.index') }}">Collections</a>
                            <span class="separator">→</span>
                            <span class="current">{{ $collection->title }}</span>
                        </nav>

                        <h1 class="collection-title">{{ $collection->title }}</h1>

                        @if($collection->description)
                            <p class="collection-description">{{ $collection->description }}</p>
                        @endif

                        <div class="collection-meta">
                            <div class="meta-item">
                                <span class="meta-count">{{ $products->total() }}</span>
                                <span class="meta-label">{{ $products->total() === 1 ? 'Piece' : 'Pieces' }}</span>
                            </div>
                            @if($collection->release_date)
                                <div class="meta-item">
                                    <span class="meta-count">{{ $collection->release_date->format('Y') }}</span>
                                    <span class="meta-label">Released</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="container">
            <!-- Section Header -->
            <div class="section-header">
                <h2 class="section-title">Collection Pieces</h2>
                <div class="section-controls">
                    <div class="sort-dropdown">
                        <label for="product-sort">Sort by</label>
                        <select id="product-sort" class="sort-select" onchange="window.location.href = this.value">
                            <option value="{{ route('collections.show', [$collection->slug]) }}">Default</option>
                            <option value="{{ route('collections.show', [$collection->slug, 'sort' => 'name']) }}" {{ request('sort') === 'name' ? 'selected' : '' }}>Name</option>
                            <option value="{{ route('collections.show', [$collection->slug, 'sort' => 'price_low']) }}" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="{{ route('collections.show', [$collection->slug, 'sort' => 'price_high']) }}" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="{{ route('collections.show', [$collection->slug, 'sort' => 'newest']) }}" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            @if($products->count() > 0)
                <div class="products-grid">
                    @foreach($products as $product)
                        <div class="product-card">
                            <div class="product-image-container">
                                @if($product->images && $product->images->count() > 0)
                                    <img src="{{ asset('images/products/' . $product->images->first()->path) }}"
                                         class="product-image"
                                         alt="{{ $product->name }}"
                                         loading="lazy">
                                @else
                                    <div class="product-image-placeholder">
                                        <i class="fas fa-image"></i>
                                        <span>{{ $product->name }}</span>
                                    </div>
                                @endif

                                <!-- Product Badges -->
                                @if($product->is_one_of_a_kind || $product->quantity <= 5)
                                    <div class="product-badges">
                                        @if($product->is_one_of_a_kind)
                                            <span class="badge unique">One of a Kind</span>
                                        @endif
                                        @if($product->quantity <= 5 && $product->quantity > 0)
                                            <span class="badge limited">Limited Stock</span>
                                        @elseif($product->quantity <= 0)
                                            <span class="badge sold-out">Sold Out</span>
                                        @endif
                                    </div>
                                @endif

                                <!-- Product Overlay -->
                                <div class="product-overlay">
                                    <a href="{{ route('products.show', $product->slug) }}" class="view-product-btn">
                                        View Details
                                    </a>
                                </div>
                            </div>

                            <div class="product-info">
                                <h3 class="product-title">
                                    <a href="{{ route('products.show', $product->slug) }}">{{ $product->name }}</a>
                                </h3>

                                @if($product->description)
                                    <p class="product-description">{{ Str::limit($product->description, 80) }}</p>
                                @endif

                                <div class="product-pricing">
                                    <span class="current-price">EGP {{ number_format($product->price, 0) }}</span>
                                    @if($product->compare_price && $product->compare_price > $product->price)
                                        <span class="original-price">EGP {{ number_format($product->compare_price, 0) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                    <div class="pagination-container">
                        {{ $products->appends(request()->query())->links() }}
                    </div>
                @endif
            @else
                <div class="empty-collection">
                    <div class="empty-message">
                        <h3>No pieces available</h3>
                        <p>This collection is currently being curated. Check back soon for new pieces.</p>
                        <a href="{{ route('collections.index') }}" class="btn-back">Browse Other Collections</a>
                    </div>
                </div>
            @endif
        </div>
    </section>
</main>
@push('styles')
<style>
.collection-page {
    font-family: 'futura-pt', sans-serif;
    color: #333;
}

/* Collection Hero Section */
.collection-hero {
    padding: 4rem 0;
    background: white;
}

.collection-image-container {
    position: relative;
    margin-bottom: 2rem;
}

.collection-hero-image {
    width: 100%;
    height: 500px;
    object-fit: cover;
    border-radius: 4px;
}

.collection-placeholder {
    width: 100%;
    height: 500px;
    background: #f8f9fa;
    border-radius: 4px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 1.5rem;
    color: #999;
    gap: 1rem;
}

.collection-placeholder i {
    font-size: 3rem;
    opacity: 0.5;
}

.collection-info {
    padding-left: 3rem;
}

/* Breadcrumb */
.breadcrumb-nav {
    margin-bottom: 2rem;
    font-size: 0.9rem;
    font-weight: 300;
}

.breadcrumb-nav a {
    color: #666;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb-nav a:hover {
    color: #000;
}

.breadcrumb-nav .separator {
    margin: 0 0.75rem;
    color: #ccc;
}

.breadcrumb-nav .current {
    color: #000;
}

/* Collection Title & Description */
.collection-title {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 3rem;
    color: #000;
    margin-bottom: 1.5rem;
    letter-spacing: 0.02em;
    line-height: 1.2;
}

.collection-description {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    font-size: 1.125rem;
    line-height: 1.7;
    color: #555;
    margin-bottom: 2rem;
}

/* Collection Meta */
.collection-meta {
    display: flex;
    gap: 3rem;
    margin-top: 2rem;
}

.meta-item {
    text-align: center;
}

.meta-count {
    display: block;
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 2rem;
    color: #000;
    line-height: 1;
}

.meta-label {
    display: block;
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.9rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-top: 0.5rem;
}

/* Products Section */
.products-section {
    padding: 4rem 0;
    background: #fafafa;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 3rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid #eee;
}

.section-title {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 2rem;
    color: #000;
    margin: 0;
    letter-spacing: 0.02em;
}

.section-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Sort Dropdown */
.sort-dropdown {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.sort-dropdown label {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.9rem;
    color: #666;
    margin: 0;
}

.sort-select {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    border: 1px solid #ddd;
    border-radius: 0;
    padding: 0.5rem 1rem;
    background: white;
    color: #333;
    font-size: 0.9rem;
    min-width: 200px;
}

.sort-select:focus {
    outline: none;
    border-color: #000;
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
    margin-bottom: 3rem;
}

.product-card {
    background: white;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

.product-image-container {
    position: relative;
    overflow: hidden;
    aspect-ratio: 3/4;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.product-image-placeholder {
    width: 100%;
    height: 100%;
    background: #f5f5f5;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    color: #999;
    text-align: center;
    padding: 2rem;
    gap: 1rem;
}

.product-image-placeholder i {
    font-size: 2.5rem;
    opacity: 0.5;
}

.product-image-placeholder span {
    font-size: 0.9rem;
    line-height: 1.4;
}

/* Product Badges */
.product-badges {
    position: absolute;
    top: 1rem;
    left: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.badge {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    font-size: 0.75rem;
    padding: 0.3rem 0.6rem;
    border-radius: 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.badge.unique {
    background: #000;
    color: white;
}

.badge.limited {
    background: #ff6b35;
    color: white;
}

.badge.sold-out {
    background: #999;
    color: white;
}

/* Product Overlay */
.product-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
    padding: 2rem 1.5rem 1.5rem;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

.product-card:hover .product-overlay {
    transform: translateY(0);
}

.view-product-btn {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    color: white;
    background: transparent;
    border: 1px solid white;
    padding: 0.75rem 1.5rem;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    display: block;
    text-align: center;
}

.view-product-btn:hover {
    background: white;
    color: #000;
    text-decoration: none;
}

/* Product Info */
.product-info {
    padding: 1.5rem;
}

.product-title {
    margin: 0 0 0.75rem 0;
}

.product-title a {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    font-size: 1.1rem;
    color: #000;
    text-decoration: none;
    transition: color 0.3s ease;
}

.product-title a:hover {
    color: #666;
}

.product-description {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.9rem;
    color: #666;
    line-height: 1.5;
    margin-bottom: 1rem;
}

.product-pricing {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.current-price {
    font-family: 'futura-pt', sans-serif;
    font-weight: 500;
    font-size: 1.1rem;
    color: #000;
}

.original-price {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.9rem;
    color: #999;
    text-decoration: line-through;
}

/* Empty Collection */
.empty-collection {
    text-align: center;
    padding: 4rem 2rem;
}

.empty-message h3 {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 1.5rem;
    color: #000;
    margin-bottom: 1rem;
}

.empty-message p {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    color: #666;
    margin-bottom: 2rem;
}

.btn-back {
    font-family: 'futura-pt', sans-serif;
    font-weight: 400;
    color: white;
    background: #000;
    border: none;
    padding: 0.75rem 2rem;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.btn-back:hover {
    background: #333;
    color: white;
    text-decoration: none;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
    margin-top: 3rem;
}

/* Responsive Design */
@media (max-width: 992px) {
    .collection-info {
        padding-left: 1.5rem;
    }

    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    }
}

@media (max-width: 768px) {
    .collection-hero {
        padding: 2rem 0;
    }

    .collection-info {
        padding-left: 0;
        margin-top: 2rem;
    }

    .collection-title {
        font-size: 2.5rem;
    }

    .collection-hero-image,
    .collection-placeholder {
        height: 350px;
    }

    .section-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
        text-align: center;
    }

    .section-controls {
        justify-content: center;
    }

    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .collection-meta {
        justify-content: center;
        gap: 2rem;
    }

    .breadcrumb-nav {
        text-align: center;
    }
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .collection-title {
        font-size: 2rem;
    }

    .collection-hero-image,
    .collection-placeholder {
        height: 280px;
    }

    .meta-count {
        font-size: 1.5rem;
    }

    .sort-select {
        min-width: 180px;
        font-size: 0.85rem;
    }
}
</style>
@endpush
@endsection
