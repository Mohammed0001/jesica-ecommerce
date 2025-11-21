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
                @if($product->collection)
                    <span class="separator">→</span>
                    <a href="{{ route('collections.show', $product->collection->slug) }}">
                        {{ $product->collection->name }}
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
            <div class="row align-items-start">
                <!-- Product Images -->
                <div class="col-lg-6 order-lg-1">
                    <div class="product-gallery">
                        @if($product->images && $product->images->count() > 0)
                            <!-- Main Image -->
                            <div class="main-image-container">
                                <img id="mainImage"
                                     src="{{ asset('images/products/' . $product->images->first()->path) }}"
                                     class="main-image"
                                     alt="{{ $product->name }}"
                                     loading="lazy">
                            </div>

                            <!-- Thumbnail Images -->
                            @if($product->images->count() > 1)
                                <div class="thumbnails-container">
                                    @foreach($product->images as $index => $image)
                                        <div class="thumbnail-item">
                                            <img src="{{ asset('images/products/' . $image->path) }}"
                                                 class="thumbnail {{ $index === 0 ? 'active' : '' }}"
                                                 alt="{{ $product->name }}"
                                                 onclick="changeMainImage('{{ asset('images/products/' . $image->path) }}', this)">
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @else
                            <div class="main-image-placeholder">
                                <i class="fas fa-image"></i>
                                <span>{{ $product->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Product Information -->
                <div class="col-lg-6 order-lg-2">
                    <div class="product-info">
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

                        <!-- Product Title -->
                        <h1 class="product-title">{{ $product->name }}</h1>

                        <!-- Price -->
                        <div class="product-pricing">
                            <span class="current-price">EGP {{ number_format($product->price, 0) }}</span>
                            @if($product->compare_price && $product->compare_price > $product->price)
                                <span class="original-price">EGP {{ number_format($product->compare_price, 0) }}</span>
                                <span class="discount-badge">
                                    {{ round((($product->compare_price - $product->price) / $product->compare_price) * 100) }}% OFF
                                </span>
                            @endif
                        </div>

                        <!-- Description -->
                        @if($product->description)
                            <div class="product-description">
                                <p>{{ $product->description }}</p>
                            </div>
                        @endif

                        <!-- Story -->
                        @if($product->story)
                            <div class="product-story">
                                <h3>The Story</h3>
                                <p>{{ $product->story }}</p>
                            </div>
                        @endif

                        <!-- Size Selection -->
                        @if($product->sizes && $product->sizes->where('quantity', '>', 0)->count() > 0)
                            <div class="size-selection">
                                <h3>Size</h3>
                                <div class="size-dropdown-container">
                                    <select id="size-select" name="size" class="size-dropdown" required>
                                        <option value="">Select a size</option>
                                        @foreach($product->sizes->where('quantity', '>', 0) as $size)
                                            <option value="{{ $size->size_label }}">
                                                {{ $size->size_label }}
                                                @if($size->quantity <= 5)
                                                    ({{ $size->quantity }} left)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="size-guide-link">
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#sizeGuideModal">
                                        <i class="fas fa-ruler me-1"></i>Size Guide
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Add to Cart Section -->
                        @if($product->quantity > 0)
                            <div class="cart-section">
                                <form id="addToCartForm" class="cart-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" id="quantity" name="quantity" value="1">

                                    <div class="cart-actions">
                                        <button type="submit" class="btn-add-to-cart">
                                            Add to Collection
                                        </button>
                                        <button type="button" class="btn-wishlist" onclick="toggleWishlist({{ $product->id }})">
                                            <i class="fas fa-heart"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <div class="out-of-stock">
                                <button class="btn-out-of-stock" disabled>
                                    Currently Unavailable
                                </button>
                            </div>
                        @endif

                        <!-- Product Details -->
                        <div class="product-details">
                            <div class="detail-item">
                                <h4>Product Details</h4>
                                <div class="detail-content">
                                    <div class="detail-row">
                                        <span class="detail-label">SKU</span>
                                        <span class="detail-value">{{ $product->sku }}</span>
                                    </div>
                                    @if($product->collection)
                                        <div class="detail-row">
                                            <span class="detail-label">Collection</span>
                                            <span class="detail-value">{{ $product->collection->name }}</span>
                                        </div>
                                    @endif
                                    <div class="detail-row">
                                        <span class="detail-label">Availability</span>
                                        <span class="detail-value">{{ $product->quantity > 0 ? $product->quantity . ' in stock' : 'Out of stock' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="detail-item">
                                <h4>Care & Shipping</h4>
                                <div class="detail-content">
                                    <div class="care-instructions">
                                        <p>• Professional care recommended</p>
                                        <p>• Free shipping within Egypt</p>
                                        <p>• 14-day return policy</p>
                                        <p>• Authentic craftsmanship guarantee</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products -->
    @if(isset($relatedProducts) && $relatedProducts->count() > 0)
        <section class="related-products-section">
            <div class="container">
                <h2 class="section-title">You May Also Like</h2>
                <div class="related-products-grid">
                    @foreach($relatedProducts as $relatedProduct)
                        <div class="related-product-card">
                            <div class="related-product-image">
                                @if($relatedProduct->images && $relatedProduct->images->count() > 0)
                                    <img src="{{ asset('images/products/' . $relatedProduct->images->first()->path) }}"
                                         alt="{{ $relatedProduct->name }}"
                                         loading="lazy">
                                @else
                                    <div class="related-product-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="related-product-info">
                                <h3>
                                    <a href="{{ route('products.show', $relatedProduct->slug) }}">
                                        {{ $relatedProduct->name }}
                                    </a>
                                </h3>
                                <span class="related-product-price">EGP {{ number_format($relatedProduct->price, 0) }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</main>

<!-- Size Guide Modal -->
@if($product->sizes && $product->sizes->count() > 0)
    <div class="modal fade" id="sizeGuideModal" tabindex="-1" aria-labelledby="sizeGuideModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sizeGuideModalLabel">
                        <i class="fas fa-ruler me-2"></i>Size Guide
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="mb-3">Standard Sizing Chart</h6>
                            <div class="size-guide-table">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Size</th>
                                            <th>Chest/Bust (cm)</th>
                                            <th>Waist (cm)</th>
                                            <th>Hip (cm)</th>
                                            <th>Length (cm)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>XS</strong></td>
                                            <td>86-89</td>
                                            <td>68-71</td>
                                            <td>92-95</td>
                                            <td>58-60</td>
                                        </tr>
                                        <tr>
                                            <td><strong>S</strong></td>
                                            <td>90-93</td>
                                            <td>72-75</td>
                                            <td>96-99</td>
                                            <td>60-62</td>
                                        </tr>
                                        <tr>
                                            <td><strong>M</strong></td>
                                            <td>94-97</td>
                                            <td>76-79</td>
                                            <td>100-103</td>
                                            <td>62-64</td>
                                        </tr>
                                        <tr>
                                            <td><strong>L</strong></td>
                                            <td>98-101</td>
                                            <td>80-83</td>
                                            <td>104-107</td>
                                            <td>64-66</td>
                                        </tr>
                                        <tr>
                                            <td><strong>XL</strong></td>
                                            <td>102-105</td>
                                            <td>84-87</td>
                                            <td>108-111</td>
                                            <td>66-68</td>
                                        </tr>
                                        <tr>
                                            <td><strong>XXL</strong></td>
                                            <td>106-109</td>
                                            <td>88-91</td>
                                            <td>112-115</td>
                                            <td>68-70</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-3">How to Measure</h6>
                            <div class="measurement-guide">
                                <div class="measurement-item">
                                    <strong><i class="fas fa-arrows-alt-h text-primary"></i> Chest/Bust:</strong>
                                    <p class="small mb-2">Measure around the fullest part of your chest/bust, keeping the tape horizontal.</p>
                                </div>
                                <div class="measurement-item">
                                    <strong><i class="fas fa-arrows-alt-h text-primary"></i> Waist:</strong>
                                    <p class="small mb-2">Measure around your natural waistline, the narrowest part of your torso.</p>
                                </div>
                                <div class="measurement-item">
                                    <strong><i class="fas fa-arrows-alt-h text-primary"></i> Hip:</strong>
                                    <p class="small mb-2">Measure around the fullest part of your hips, about 8 inches below your waist.</p>
                                </div>
                                <div class="measurement-item">
                                    <strong><i class="fas fa-arrows-alt-v text-primary"></i> Length:</strong>
                                    <p class="small mb-2">Measure from the highest point of your shoulder down to your desired length.</p>
                                </div>
                            </div>

                            <div class="sizing-tips mt-3">
                                <h6>Sizing Tips</h6>
                                <ul class="small">
                                    <li>All measurements are in centimeters</li>
                                    <li>Measurements are taken flat across the garment</li>
                                    <li>For the best fit, measure over fitted undergarments</li>
                                    <li>If between sizes, we recommend sizing up</li>
                                    <li>Each piece is handcrafted and may vary slightly</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Available Sizes for this Product -->
                    @if($product->sizes->where('quantity', '>', 0)->count() > 0)
                        <div class="available-sizes mt-4">
                            <h6>Available Sizes for This Item</h6>
                            <div class="size-availability">
                                @foreach($product->sizes->where('quantity', '>', 0) as $size)
                                    <span class="badge bg-success me-2 mb-2">
                                        {{ $size->size_label }}
                                        <small>({{ $size->quantity }} available)</small>
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary" data-bs-dismiss="modal" onclick="scrollToSizeSelection()">
                        Select Size
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@push('styles')
<style>
/* Product Page Styles */
.product-page {
    font-family: 'Futura PT', Arial, sans-serif;
    line-height: 1.6;
    color: #333;
}

/* Breadcrumb */
.breadcrumb-section {
    background: #f8f9fa;
    padding: 1rem 0;
    margin-bottom: 2rem;
}

.breadcrumb-nav {
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.breadcrumb-nav a {
    color: #666;
    text-decoration: none;
    transition: color 0.3s ease;
}

.breadcrumb-nav a:hover {
    color: #333;
}

.breadcrumb-nav .separator {
    color: #ccc;
    font-size: 0.8rem;
}

.breadcrumb-nav .current {
    color: #333;
    font-weight: 500;
}

/* Product Details Section */
.product-details-section {
    padding: 2rem 0 4rem;
}

/* Product Gallery */
.product-gallery {
    position: sticky;
    top: 2rem;
}

.main-image-container {
    position: relative;
    margin-bottom: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
    overflow: hidden;
}

.main-image {
    width: 100%;
    height: 600px;
    object-fit: cover;
    display: block;
}

.main-image-placeholder {
    width: 100%;
    height: 600px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 1.1rem;
}

.main-image-placeholder i {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.thumbnails-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: 0.5rem;
}

.thumbnail-item {
    aspect-ratio: 1;
    border-radius: 4px;
    overflow: hidden;
    background: #f8f9fa;
}

.thumbnail {
    width: 100%;
    height: 100%;
    object-fit: cover;
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 0.7;
}

.thumbnail:hover,
.thumbnail.active {
    opacity: 1;
    transform: scale(1.05);
}

/* Product Information */
.product-info {
    padding-left: 2rem;
}

.product-badges {
    margin-bottom: 1rem;
}

.badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 500;
    margin-right: 0.5rem;
}

.badge.unique {
    background: #fff3cd;
    color: #856404;
}

.badge.limited {
    background: #f8d7da;
    color: #721c24;
}

.badge.sold-out {
    background: #d6d8db;
    color: #6c757d;
}

.product-title {
    font-size: 2.5rem;
    font-weight: 300;
    letter-spacing: -0.02em;
    margin-bottom: 1.5rem;
    line-height: 1.2;
}

.product-pricing {
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.current-price {
    font-size: 1.8rem;
    font-weight: 600;
    color: #333;
}

.original-price {
    font-size: 1.2rem;
    color: #999;
    text-decoration: line-through;
}

.discount-badge {
    background: #e74c3c;
    color: white;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.product-description {
    margin-bottom: 2rem;
}

.product-description p {
    font-size: 1.1rem;
    color: #666;
    line-height: 1.6;
}

.product-story {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.product-story h3 {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 0.8rem;
    color: #333;
}

.product-story p {
    font-size: 1rem;
    color: #666;
    margin: 0;
}

/* Size Selection */
.size-selection {
    margin-bottom: 2rem;
}

.size-selection h3 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 1rem;
    color: #333;
}

.size-dropdown-container {
    margin-bottom: 0.8rem;
}

.size-dropdown {
    width: 100%;
    max-width: 300px;
    padding: 1rem 1.2rem;
    border: 2px solid #e5e5e5;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    font-family: 'Futura PT', Arial, sans-serif;
    background: white;
    color: #333;
    cursor: pointer;
    transition: all 0.3s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23666666' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 1rem center;
    background-repeat: no-repeat;
    background-size: 1.2em 1.2em;
    padding-right: 3rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.size-dropdown:hover {
    border-color: #333;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.size-dropdown:focus {
    outline: none;
    border-color: #333;
    box-shadow: 0 0 0 3px rgba(51, 51, 51, 0.1);
}

.size-dropdown option {
    padding: 0.8rem;
    font-weight: 500;
    font-family: 'Futura PT', Arial, sans-serif;
    background: white;
    color: #333;
}

.size-guide-link a {
    color: #666;
    text-decoration: underline;
    font-size: 0.9rem;
}

.size-guide-link a:hover {
    color: #333;
}

/* Cart Section */
.cart-section {
    margin-bottom: 2rem;
}

.cart-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.btn-add-to-cart {
    flex: 1;
    padding: 1.2rem 2.5rem;
    background: #333;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 1rem;
    font-family: 'Futura PT', Arial, sans-serif;
    cursor: pointer;
    transition: all 0.3s ease;
    max-width: 320px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-add-to-cart:hover {
    background: #1a1a1a;
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
}

.btn-wishlist {
    width: 55px;
    height: 55px;
    border: 2px solid #e5e5e5;
    background: white;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.btn-wishlist:hover {
    border-color: #e74c3c;
    color: #e74c3c;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.out-of-stock .btn-out-of-stock {
    width: 100%;
    padding: 1rem;
    background: #6c757d;
    color: white;
    border: none;
    border-radius: 4px;
    font-weight: 600;
    cursor: not-allowed;
}

/* Product Details */
.product-details {
    border-top: 1px solid #eee;
    padding-top: 2rem;
}

.detail-item {
    margin-bottom: 1.5rem;
}

.detail-item h4 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.8rem;
    color: #333;
}

.detail-content {
    color: #666;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 0.3rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.detail-label {
    font-weight: 500;
}

.care-instructions p {
    margin: 0.3rem 0;
    font-size: 0.95rem;
}

/* Related Products */
.related-products-section {
    padding: 4rem 0;
    background: #f8f9fa;
}

.section-title {
    text-align: center;
    font-size: 2rem;
    font-weight: 300;
    margin-bottom: 3rem;
    color: #333;
}

.related-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
}

.related-product-card {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.related-product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

.related-product-image {
    aspect-ratio: 1;
    overflow: hidden;
    background: #f8f9fa;
}

.related-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.related-product-card:hover .related-product-image img {
    transform: scale(1.05);
}

.related-product-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ccc;
    font-size: 2rem;
}

.related-product-info {
    padding: 1.5rem;
}

.related-product-info h3 {
    margin: 0 0 0.5rem;
    font-size: 1.1rem;
    font-weight: 500;
}

.related-product-info a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.related-product-info a:hover {
    color: #666;
}

.related-product-price {
    color: #666;
    font-weight: 600;
}

/* Size Guide Modal */
.modal-content {
    border: none;
    border-radius: 8px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-header {
    border-bottom: 1px solid #eee;
    background: #f8f9fa;
}

.modal-header .modal-title {
    font-weight: 600;
    color: #333;
}

.size-guide-table table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0;
}

.size-guide-table th,
.size-guide-table td {
    padding: 0.8rem;
    text-align: center;
    border: 1px solid #dee2e6;
    vertical-align: middle;
}

.size-guide-table th {
    font-weight: 600;
    background: #f8f9fa;
    font-size: 0.9rem;
}

.size-guide-table td {
    font-size: 0.9rem;
}

.measurement-guide .measurement-item {
    margin-bottom: 1rem;
    padding: 0.8rem;
    background: #f8f9fa;
    border-radius: 6px;
}

.measurement-guide .measurement-item strong {
    display: block;
    margin-bottom: 0.3rem;
    color: #333;
}

.sizing-tips {
    background: #e3f2fd;
    padding: 1rem;
    border-radius: 6px;
    border-left: 4px solid #2196f3;
}

.sizing-tips h6 {
    color: #1976d2;
    margin-bottom: 0.5rem;
}

.sizing-tips ul {
    margin-bottom: 0;
}

.available-sizes {
    background: #f0f9ff;
    padding: 1rem;
    border-radius: 6px;
    border-left: 4px solid #10b981;
}

.available-sizes h6 {
    color: #059669;
    margin-bottom: 0.8rem;
}

.size-availability .badge {
    font-size: 0.85rem;
    padding: 0.5rem 0.8rem;
}

.size-guide-table th {
    font-weight: 600;
    background: #f8f9fa;
}

/* Responsive Design */
@media (max-width: 768px) {
    .product-info {
        padding-left: 0;
        margin-top: 2rem;
    }

    .product-title {
        font-size: 2rem;
    }

    .current-price {
        font-size: 1.5rem;
    }

    .cart-actions {
        flex-direction: column;
        gap: 1rem;
    }

    .btn-add-to-cart {
        max-width: none;
    }

    .related-products-grid {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Image Gallery
function changeMainImage(imageSrc, thumbnail) {
    document.getElementById('mainImage').src = imageSrc;

    // Update active thumbnail
    document.querySelectorAll('.thumbnail').forEach(thumb => {
        thumb.classList.remove('active');
    });
    thumbnail.classList.add('active');
}

// Size Guide Functions
function scrollToSizeSelection() {
    const sizeSelection = document.querySelector('.size-selection');
    if (sizeSelection) {
        sizeSelection.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

// Add to Cart
document.addEventListener('DOMContentLoaded', function() {
    const addToCartForm = document.getElementById('addToCartForm');

    if (addToCartForm) {
        addToCartForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Get form data
            const formData = new FormData(this);

            // Check if size is required and selected
            const sizeDropdown = document.getElementById('size-select');
            if (sizeDropdown && sizeDropdown.options.length > 1) {
                if (!sizeDropdown.value) {
                    alert('Please select a size');
                    return;
                }
                formData.append('size', sizeDropdown.value);
            }

            // Add to cart via AJAX
            fetch('{{ route("cart.add") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert('Added to cart successfully!');

                    // Update cart count if exists
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount && data.cartCount) {
                        cartCount.textContent = data.cartCount;
                    }
                } else {
                    alert(data.message || 'Failed to add to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    }
});

// Wishlist Toggle
function toggleWishlist(productId) {
    fetch(`/wishlist/toggle/${productId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const wishlistBtn = event.target.closest('.btn-wishlist');
            if (data.added) {
                wishlistBtn.classList.add('active');
                wishlistBtn.style.color = '#e74c3c';
                wishlistBtn.style.borderColor = '#e74c3c';
            } else {
                wishlistBtn.classList.remove('active');
                wishlistBtn.style.color = '';
                wishlistBtn.style.borderColor = '';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
</script>
@endpush
