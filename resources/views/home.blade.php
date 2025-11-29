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
                    <a href="{{ route('collections.index') }}" class="btn-primary" style="display: inline-block;">
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
                    <p>
                        Jessica Riad is a visual artist and fashion designer who transforms emotion, memory, and
                        culture into wearable art. Her creations merge craftsmanship with storytelling, blending
                        recycled materials, bold textures, and heritage influences into collectible fashion
                        pieces. Each design carries a fragment of identity — a story shaped by feeling and
                        detail. Rooted in Cairo and inspired by global artistry, Jessica’s work redefines cool
                        luxury as something soulful, personal, and timeless.
                    </p>
                    <p>
                        For those who seek meaning beyond fashion, Jessica Riad invites you to carry art, not
                        trend.
                    </p>
                    <span
                        style="font-family: 'Dancing Script', 'Brush Script MT', cursive; font-size: 1.4em; font-weight: 400; color: #2c3e50; letter-spacing: 1px; display: inline-block; margin: 10px 0; position: relative; z-index: 1;"
                        title="Hand-signed quote">art you carry – emotion you own</span>
                </div>
                <div class="col-lg-6">
                    <div class="about-image">
                        <img src="{{ asset('images/about-hero.jpg') }}" alt="Jesica Riad at work" class="img-fluid">

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
