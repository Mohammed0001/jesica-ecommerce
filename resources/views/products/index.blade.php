@extends('layouts.app')

@section('title', 'All Products')

@section('content')
<main class="products-page">
    <!-- Page Header -->
    <section class="products-header">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="page-title">All Products</h1>
                    <p class="page-subtitle">Discover our complete collection of fashion and style</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Filters and Search -->
    <section class="products-filters">
        <div class="container">
            <form method="GET" action="{{ route('products.index') }}" class="filters-form">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="form-group">
                            <input
                                type="text"
                                name="search"
                                class="form-control search-input"
                                placeholder="Search products..."
                                value="{{ request('search') }}"
                            >
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="collection" class="form-control">
                                <option value="">All Collections</option>
                                @foreach($collections as $collection)
                                    <option value="{{ $collection->slug }}"
                                            {{ request('collection') === $collection->slug ? 'selected' : '' }}>
                                        {{ $collection->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <select name="sort" class="form-control">
                                <option value="newest" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest First</option>
                                <option value="name" {{ request('sort') === 'name' ? 'selected' : '' }}>Name A-Z</option>
                                <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block">Apply</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="products-content">
        <div class="container">
            @if($products->count() > 0)
                <div class="products-meta">
                    <p class="results-count">
                        Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of {{ $products->total() }} products
                    </p>
                </div>

                <div class="products-grid">
                    @foreach($products as $product)
                    <div class="product-card">
                        <div class="product-image">
                            <a href="{{ route('products.show', $product->slug) }}">
                                @if(optional($product->main_image)->url)
                                    <img src="{{ optional($product->main_image)->url ?? asset('images/picsum/600x800-1-0.jpg') }}"
                                         alt="{{ $product->name }}"
                                         class="product-thumbnail"
                                         loading="lazy">
                                @else
                                    <div class="placeholder-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </a>

                            @if($product->sale_price)
                                <span class="sale-badge">Sale</span>
                            @endif

                                <div class="product-overlay">
                                    <button class="btn btn-primary btn-sm add-to-cart"
                                            data-product-id="{{ $product->id }}">
                                        Add to Cart
                                    </button>
                                </div>
                        </div>

                        <div class="product-info">
                            <div class="product-collection">
                                <a href="{{ route('collections.show', $product->collection->slug) }}">
                                    {{ $product->collection->title }}
                                </a>
                            </div>
                            <h3 class="product-name">
                                <a href="{{ route('products.show', $product->slug) }}">
                                    {{ $product->name }}
                                </a>
                            </h3>
                            <p class="product-description">
                                {{ Str::limit($product->description, 80) }}
                            </p>
                            <div class="product-price">
                                @if($product->sale_price)
                                 <span class="sale-price">${{ number_format($product->sale_price, 2) }}</span>
                                 <span class="original-price">{!! $product->formatted_price !!}</span>
                                @else
                                 <span class="current-price">{!! $product->formatted_price !!}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($products->hasPages())
                <div class="pagination-wrapper">
                    {{ $products->appends(request()->query())->links() }}
                </div>
                @endif

            @else
                <!-- No Products -->
                <div class="no-products">
                    <div class="no-products-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="no-products-title">No Products Found</h3>
                    <p class="no-products-message">
                        @if(request()->filled('search') || request()->filled('collection'))
                            No products match your current filters. Try adjusting your search criteria.
                        @else
                            No products are currently available.
                        @endif
                    </p>
                    @if(request()->filled('search') || request()->filled('collection'))
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            Clear Filters
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>
</main>

@push('styles')
<style>
.products-page {
    font-family: 'futura-pt', sans-serif;
}

/* Products Header */
.products-header {
    padding: 3rem 0 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.page-title {
    font-weight: 200;
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    letter-spacing: 0.02em;
}

.page-subtitle {
    font-weight: 200;
    font-size: 1.125rem;
    color: var(--text-muted);
    margin-bottom: 0;
}

/* Filters */
.products-filters {
    padding: 2rem 0;
    background: white;
    border-bottom: 1px solid var(--border-light);
}

.filters-form .form-control {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    border: 1px solid var(--border-light);
    border-radius: 0;
    padding: 0.75rem;
    transition: all 0.3s ease;
}

.filters-form .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
}

.search-input {
    background: #f8f9fa;
}

.filters-form .btn {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 0.75rem 1.5rem;
    border-radius: 0;
    transition: all 0.3s ease;
}

/* Products Content */
.products-content {
    padding: 3rem 0;
}

.products-meta {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-light);
}

.results-count {
    color: var(--text-muted);
    font-weight: 200;
    margin-bottom: 0;
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
    border: 1px solid var(--border-light);
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.product-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.product-image {
    position: relative;
    aspect-ratio: 1;
    overflow: hidden;
}

.product-thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
    border-radius: 8px;
    box-shadow: 0 12px 24px rgba(0,0,0,0.06);
}

.product-card:hover .product-thumbnail {
    transform: scale(1.05);
}

.placeholder-image {
    width: 100%;
    height: 100%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
    font-size: 2rem;
}

.sale-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: #dc3545;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 300;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    z-index: 2;
}

.product-overlay {
    position: absolute;
    bottom: 1rem;
    left: 1rem;
    right: 1rem;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.add-to-cart {
    width: 100%;
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    border-radius: 0;
}

.product-info {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.product-collection {
    margin-bottom: 0.5rem;
}

.product-collection a {
    color: var(--text-muted);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 200;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    transition: color 0.3s ease;
}

.product-collection a:hover {
    color: var(--primary-color);
    text-decoration: none;
}

.product-name {
    font-weight: 300;
    font-size: 1.25rem;
    margin-bottom: 0.75rem;
    line-height: 1.3;
}

.product-name a {
    color: var(--primary-color);
    text-decoration: none;
    transition: color 0.3s ease;
}

.product-name a:hover {
    color: var(--primary-hover);
    text-decoration: none;
}

.product-description {
    color: var(--text-muted);
    font-weight: 200;
    line-height: 1.5;
    margin-bottom: 1rem;
    flex: 1;
}

.product-price {
    font-size: 1.125rem;
    font-weight: 300;
}

.current-price {
    color: var(--primary-color);
}

.sale-price {
    color: #dc3545;
    margin-right: 0.5rem;
}

.original-price {
    color: var(--text-muted);
    text-decoration: line-through;
    font-size: 1rem;
}

/* No Products */
.no-products {
    text-align: center;
    padding: 4rem 2rem;
}

.no-products-icon {
    font-size: 4rem;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
}

.no-products-title {
    font-weight: 300;
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.no-products-message {
    color: var(--text-muted);
    font-size: 1.125rem;
    margin-bottom: 2rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

/* Pagination */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-title {
        font-size: 2.5rem;
    }

    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .filters-form .form-group {
        margin-bottom: 1rem;
    }

    .product-overlay {
        opacity: 1;
        position: static;
        padding: 1rem;
        background: rgba(0, 0, 0, 0.05);
    }

    .product-info {
        padding: 1rem;
    }
}

@media (max-width: 576px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart functionality
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            const btn = this;
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', 1);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            btn.textContent = 'Adding...';
            btn.disabled = true;

            fetch('{{ route('cart.add') }}', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.textContent = 'Added';
                    // Update the cart count UI if present
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount && data.cartCount) cartCount.textContent = data.cartCount;
                } else {
                    alert(data.message || 'Failed to add to cart');
                    btn.textContent = 'Add to Cart';
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred. Please try again.');
                btn.textContent = 'Add to Cart';
            })
            .finally(() => {
                setTimeout(() => {
                    btn.textContent = 'Add to Cart';
                    btn.disabled = false;
                }, 1200);
            });
        });
    });
});
</script>
@endpush
@endsection
