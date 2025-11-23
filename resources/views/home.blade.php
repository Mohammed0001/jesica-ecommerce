@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <!-- Hero Section -->
    <section class="hero-section hero-background" style="margin-top:-90px;">
        <div class="container">
            <div class="row justify-content-center" style="background-color: hsla(0, 0%, 100%, 0.384);padding:30px;">
                <div class="col-lg-8 text-center">
                    <h1 class="hero-title" style="background-color: transparent;">
                        Jesica Riad
                    </h1>
                    <h2 class="hero-subtitle" style="background-color: transparent; color: #000;">
                        Luxury Fashion
                    </h2>
                    <p class="hero-description mx-auto" style="background-color: transparent;color: #000;">
                        Discover exquisite fashion pieces that blend contemporary design with timeless elegance.
                        Each creation embodies sophistication and artistic vision.
                    </p>
                    <a href="{{ route('collections.index') }}" class="btn-primary">
                        Explore Collections
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Collections -->
    @if ($featuredCollections->isNotEmpty())
        <section class="section">
            <div class="container">
                <h2 class="section-title">Featured Collections</h2>

                <div class="collections-grid">
                    @foreach ($featuredCollections as $collection)
                        <div class="collection-card">
                            @if ($collection->images && $collection->images->count() > 0)
                                <img src="{{ optional($collection->images->first())->url ?? asset('images/picsum/600x800-1-0.jpg') }}"
                                    class="collection-image" alt="{{ $collection->title }}">
                            @elseif($collection->image_path)
                                <img src="{{ Storage::url($collection->image_path) }}" class="collection-image"
                                    alt="{{ $collection->title }}">
                            @else
                                <div class="collection-image placeholder">
                                    <span>{{ $collection->title }}</span>
                                </div>
                            @endif
                            <div class="collection-content">
                                <h3 class="collection-title">
                                    <a href="{{ route('collections.show', $collection) }}">{{ $collection->title }}</a>
                                </h3>
                                <p class="collection-description">{{ Str::limit($collection->description, 100) }}</p>
                                <a href="{{ route('collections.show', $collection) }}" class="btn-outline">
                                    View Collection
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Featured Products -->
    @if ($featuredProducts->isNotEmpty())
        <section class="section bg-light">
            <div class="container">
                <h2 class="section-title">Featured Products</h2>

                <div class="products-grid">
                    @foreach ($featuredProducts as $product)
                        <div class="product-card">
                            @if ($product->main_image)
                                <img src="{{ optional($product->main_image)->url ?? asset('images/picsum/600x800-1-0.jpg') }}"
                                    class="product-image" alt="{{ $product->title }}">
                            @else
                                <div class="product-image placeholder">
                                    <span>{{ $product->title }}</span>
                                </div>
                            @endif
                            <div class="product-content">
                                <h3 class="product-title">{{ $product->title }}</h3>
                                <p class="product-price">{!! $product->formatted_price !!}</p>
                                <a href="{{ route('products.show', $product) }}" class="btn-outline">
                                    View Details
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- About Section -->
    <section class="section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h2 class="section-title text-start">About Jesica Riad</h2>
                    <p class="text-large">
                        Jesica Riad represents the pinnacle of luxury fashion, where innovative design meets
                        exceptional craftsmanship. Each piece is meticulously crafted to create wearable art
                        that transcends traditional fashion boundaries.
                    </p>
                    <p>
                        Our collections celebrate the intersection of technology, nature, and human creativity,
                        offering unique pieces that tell a story and evoke emotion.
                    </p>
                </div>
                <div class="col-lg-6">
                    <div class="about-image">
                        <img src="https://picsum.photos/500/400?random=999" alt="Jesica Riad Studio" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('styles')
    <style>
        .hero-background {
            background: url('{{ asset('images/hero-background.jpg') }}') no-repeat center center;
            background-size: cover;
            height: 100vh;
            /* color: white; */
            /* text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5); */
        }


    </style>
@endpush
