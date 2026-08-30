@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <!-- Hero Section -->
    <section class="hero-section hero-background" style="margin-top:-90px;">
        <div class="container">
            <div class="row justify-content-center" style="background-color: hsla(0, 0%, 100%, 0.384);padding:30px;">
                <div class="col-lg-8 text-center">
                    <h1 class="hero-title" style="background-color: transparent;">
                        JESSICA RIAD
                    </h1>
                    <h2 class="hero-subtitle" style="background-color: transparent; color: #000;">
                        COOL LUXURY
                    </h2>
                    {{-- <p class="hero-description mx-auto" style="background-color: transparent;color: #000;">
                       A visual artist and fashion designer who transforms everything she experiences — from nature
                        textures and paintings and many merged cultures — into wearable art.
                        Her brand thrives on curiosity and multiplicity, embracing a universe where
                        every inusuence becomes a tactile expression
                         </p> --}}
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
                                    class="collection-image" alt="{{ $collection->title }}" loading="lazy"
                                    decoding="async">
                            @elseif($collection->image_path)
                                <img src="{{ Storage::url($collection->image_path) }}" class="collection-image"
                                    alt="{{ $collection->title }}" loading="lazy" decoding="async">
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
                        {{-- The whole card is the link, so a click anywhere on
                             the image, title or price opens the product. --}}
                        <a href="{{ route('products.show', $product) }}" class="product-card"
                            aria-label="View {{ $product->title }}">
                            <div class="product-image-wrap">
                                @if ($product->main_image)
                                    <img src="{{ optional($product->main_image)->url ?? asset('images/picsum/600x800-1-0.jpg') }}"
                                        class="product-image" alt="{{ $product->title }}" loading="lazy"
                                        decoding="async" width="600" height="800">
                                @else
                                    <div class="product-image placeholder">
                                        <span>{{ $product->title }}</span>
                                    </div>
                                @endif
                                @if ($product->isSoldOut())
                                    <span class="sold-out-badge">Sold Out</span>
                                @elseif ($product->isOnSale())
                                    <span class="sale-badge">{{ $product->discount_percentage }}% Off</span>
                                @endif
                            </div>
                            <div class="product-content">
                                <h3 class="product-title">{{ $product->title }}</h3>
                                <p class="product-price">
                                    @if ($product->isOnSale())
                                        <span class="price-sale">{!! $product->formatted_price !!}</span>
                                        <s class="price-original">{!! $product->formatted_original_price !!}</s>
                                    @else
                                        {!! $product->formatted_price !!}
                                    @endif
                                </p>
                                <span class="btn-outline">View Details</span>
                            </div>
                        </a>
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
                    <h2 class="section-title text-start">About JESSICA RIAD</h2>
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
                        <img src="{{ asset('images/about-hero.jpg') }}" alt="Jesica Riad at work" class="img-fluid"
                            loading="lazy" decoding="async">

                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('styles')
    @php
        $heroImage = \App\Models\SiteSetting::get('hero_image');
        $heroUrl = $heroImage ? asset($heroImage) : asset('images/hero-background.jpg');
    @endphp
    {{-- The hero is the largest paint on this page and it lives in CSS, so the
         browser would not discover it until the stylesheet is parsed. --}}
    <link rel="preload" as="image" href="{{ $heroUrl }}" fetchpriority="high">
    <style>
        .hero-background {
            background: url('{{ $heroUrl }}') no-repeat center center;
            background-size: cover;
            height: 100vh;
            /* color: white; */
            /* text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5); */
        }
    </style>
@endpush
