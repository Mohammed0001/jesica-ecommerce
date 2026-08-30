@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <main class="product-page">
        {{-- ── BREADCRUMB ── --}}
        <nav class="mq-breadcrumb">
            <div class="mq-container">
                <a href="{{ route('home') }}">Home</a>
                <span>/</span>
                <a href="{{ route('collections.index') }}">Women</a>
                @if ($product->collection)
                    <span>/</span>
                    <a href="{{ route('collections.show', $product->collection->slug) }}">{{ $product->collection->title }}</a>
                @endif
                <span>/</span>
                <span class="mq-breadcrumb__current">{{ $product->name }}</span>
            </div>
        </nav>

        {{-- ── PRODUCT LAYOUT ── --}}
        <section class="mq-product">
            <div class="mq-container">
                <div class="mq-product__grid">

                    {{-- LEFT: Image --}}
                    <div class="mq-product__gallery">
                        @php
                            $mainImageUrl = $product->main_image
                                ? $product->main_image->url
                                : asset('images/picsum/600x800-1-0.jpg');
                            $images = $product->images && $product->images->count() > 0
                                ? $product->images
                                : collect([$product->main_image ?? (object) ['url' => $mainImageUrl]]);
                        @endphp

                        <div class="mq-gallery__main" id="mainImageTrack">
                            @foreach($images as $index => $image)
                                <div class="mq-gallery__slide">
                                    <img src="{{ $image->url }}" alt="{{ $product->name }} {{ $index + 1 }}"
                                        loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                        class="mq-zoom-trigger" data-index="{{ $index }}">
                                    <button type="button" class="mq-zoom-btn" data-index="{{ $index }}" aria-label="Zoom image">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="11" cy="11" r="7" />
                                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                            <line x1="11" y1="8" x2="11" y2="14" />
                                            <line x1="8" y1="11" x2="14" y2="11" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        @if($images->count() > 1)
                            <div class="mq-gallery__thumbs">
                                @foreach($images as $index => $image)
                                    <button class="mq-thumb {{ $index === 0 ? 'is-active' : '' }}"
                                        onclick="scrollToImage({{ $index }})" type="button">
                                        <img src="{{ $image->url }}" alt="{{ $product->name }} {{ $index + 1 }}" loading="lazy">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- RIGHT: Info --}}
                    <div class="mq-product__info">

                        {{-- Name --}}
                        <h1 class="mq-product__name">{{ $product->name }}</h1>

                        {{-- Price --}}
                        <div class="mq-product__price">
                            @if($product->isOnSale())
                                <span class="mq-price--sale">{!! $product->formatted_price !!}</span>
                                <s class="mq-price--compare">{!! $product->formatted_original_price !!}</s>
                                <span class="mq-price__badge">{{ $product->discount_percentage }}% off</span>
                            @else
                                {!! $product->formatted_price !!}
                            @endif
                        </div>

                        @if($product->isOnSale() && $product->sale_ends_at)
                            <p class="mq-sale-note">Sale ends {{ $product->sale_ends_at->format('j M Y') }}</p>
                        @endif

                        {{-- Colour --}}
                        @php $productColors = $product->available_colors; @endphp
                        @if($productColors->isNotEmpty())
                            <div class="mq-option-row">
                                <span class="mq-option-label">
                                    Colour<span class="mq-chosen-color" id="chosenColor"></span>
                                </span>
                                <div class="mq-swatches" role="radiogroup" aria-label="Colour">
                                    @foreach($productColors as $index => $color)
                                        <button type="button"
                                            class="mq-swatch {{ $productColors->count() === 1 ? 'is-selected' : '' }} {{ $color->is_light ? 'is-light' : '' }}"
                                            style="--swatch: {{ $color->swatch_color }}"
                                            data-color="{{ $color->name }}"
                                            role="radio"
                                            aria-checked="{{ $productColors->count() === 1 ? 'true' : 'false' }}"
                                            aria-label="{{ $color->name }}"
                                            title="{{ $color->name }}">
                                            <span class="mq-swatch__chip"></span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Size --}}
                        <div class="mq-option-row">
                            <div class="mq-size-header">
                                <span class="mq-option-label">Size (IT)</span>
                                @if($product->sizeChart)
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#sizeGuideModal"
                                        class="mq-size-guide">Size guide</a>
                                @endif
                            </div>
                            @if(!$product->isSoldOut() && $product->sizes && $product->sizes->where('quantity', '>', 0)->count() > 0)
                                <div class="mq-select-wrap">
                                    <select id="size-select" class="mq-select" required>
                                        <option value="">Select a size</option>
                                        @foreach($product->sizes->where('quantity', '>', 0) as $size)
                                            <option value="{{ $size->size_label }}">
                                                {{ $size->size_label }}{{ $size->quantity <= 5 ? ' — only ' . $size->quantity . ' left' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <svg class="mq-select-caret" viewBox="0 0 12 8" fill="none">
                                        <path d="M1 1l5 5 5-5" stroke="currentColor" stroke-width="1.2" />
                                    </svg>
                                </div>
                            @else
                                <p class="mq-out-of-stock-note">Currently out of stock in all sizes</p>
                            @endif
                        </div>

                        {{-- CTA Buttons --}}
                        @if($product->isAvailable())
                            @php
                                $availableSizes = $product->sizes ? $product->sizes->where('quantity', '>', 0) : collect();
                                $needsSizeSelection = $availableSizes->count() > 1;
                            @endphp
                            <form id="addToCartForm" action="{{ route('cart.add') }}" method="POST" class="mq-cta-group">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <input type="hidden" name="size_label" id="size_label"
                                    value="{{ $availableSizes->count() === 1 ? $availableSizes->first()->size_label : '' }}">
                                {{-- Pre-filled when there is only one colour, so a
                                     single-colour product still adds in one click. --}}
                                <input type="hidden" name="color_name" id="color_name"
                                    value="{{ $productColors->count() === 1 ? $productColors->first()->name : '' }}">

                                <button type="submit" class="mq-btn mq-btn--primary">
                                    {{ $needsSizeSelection ? 'SELECT A SIZE' : 'ADD TO CART' }}
                                </button>
                            </form>
                        @else
                            <div class="mq-cta-group">
                                <button class="mq-btn mq-btn--primary" disabled>SOLD OUT</button>
                            </div>
                        @endif

                        {{-- Description accordion --}}
                        @if($product->description || $product->story)
                            <div class="mq-accordion">
                                <button class="mq-accordion__trigger is-open" type="button" data-target="desc-body">
                                    <span>Description</span>
                                    <svg class="mq-accordion__icon" viewBox="0 0 12 8" fill="none">
                                        <path d="M1 1l5 5 5-5" stroke="currentColor" stroke-width="1.2" />
                                    </svg>
                                </button>
                                <div class="mq-accordion__body" id="desc-body">
                                    @if($product->description)
                                        <p>{!! nl2br(e($product->description)) !!}</p>
                                    @endif
                                    @if($product->story)
                                        <p>{{ $product->story }}</p>
                                    @endif
                                    <a href="#" class="mq-view-details">View details <span>→</span></a>
                                </div>
                            </div>
                        @endif

                        {{-- Shipping accordion --}}
                        <div class="mq-accordion">
                            <button class="mq-accordion__trigger" type="button" data-target="shipping-body">
                                <span>Shipping information</span>
                                <svg class="mq-accordion__icon" viewBox="0 0 12 8" fill="none">
                                    <path d="M1 1l5 5 5-5" stroke="currentColor" stroke-width="1.2" />
                                </svg>
                            </button>
                            <div class="mq-accordion__body" id="shipping-body" style="display:none;">
                                <ul class="mq-shipping-list">
                                    <li>Standard delivery: 3–5 business days</li>
                                    <li>Express delivery available at checkout</li>
                                    <li>14-day return policy</li>
                                    <li>Professional dry clean recommended</li>
                                </ul>
                            </div>
                        </div>

                    </div>{{-- /.mq-product__info --}}
                </div>{{-- /.mq-product__grid --}}
            </div>
        </section>

        {{-- ── SIMILAR ITEMS ── --}}
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <section class="mq-similar">
                <div class="mq-container">
                    <div class="mq-similar__header">
                        <h2 class="mq-similar__title">Similar items</h2>
                        <div class="mq-similar__arrows">
                            <button class="mq-arrow" id="prevBtn" type="button" aria-label="Previous">&#8249;</button>
                            <button class="mq-arrow" id="nextBtn" type="button" aria-label="Next">&#8250;</button>
                        </div>
                    </div>
                    <div class="mq-similar__track-wrap">
                        <div class="mq-similar__track" id="similarTrack">
                            @foreach($relatedProducts->take(8) as $rp)
                                <a href="{{ route('products.show', $rp->slug) }}" class="mq-product-card">
                                    <div class="mq-product-card__img-wrap">
                                        <img src="{{ $rp->main_image?->url ?? asset('images/placeholder.jpg') }}"
                                            alt="{{ $rp->name }}" loading="lazy">
                                    </div>
                                    <div class="mq-product-card__body">
                                        <p class="mq-product-card__name">{{ $rp->name }}</p>
                                        <p class="mq-product-card__price">{!! $rp->formatted_price !!}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

    </main>

    {{-- ── STICKY BOTTOM BAR ── --}}
    <div class="mq-sticky-bar" id="stickyBar">
        <div class="mq-container mq-sticky-bar__inner">
            <span class="mq-sticky-bar__name">{{ $product->name }}</span>
            <span class="mq-sticky-bar__price">{!! $product->formatted_price !!}</span>
            <div class="mq-sticky-bar__actions">
                @if($product->isAvailable())
                    <button class="mq-btn mq-btn--primary mq-btn--sm"
                        onclick="document.getElementById('addToCartForm').dispatchEvent(new Event('submit'))">
                        {{ $needsSizeSelection ? 'SELECT A SIZE' : 'ADD TO CART' }}
                    </button>
                @else
                    <button class="mq-btn mq-btn--primary mq-btn--sm" disabled>SOLD OUT</button>
                @endif
            </div>
        </div>
    </div>

    {{-- ── SIZE GUIDE MODAL ── --}}
    @if($product->sizeChart)
        <div class="modal fade" id="sizeGuideModal" tabindex="-1" aria-labelledby="sizeGuideModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content rounded-0">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title mq-modal-title" id="sizeGuideModalLabel">Size Guide</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-2">
                        @if($product->sizeChart->image_url)
                            <img src="{{ $product->sizeChart->image_url }}" alt="{{ $product->sizeChart->name }}"
                                class="img-fluid mb-4">
                        @endif
                        @php $measurements = $product->sizeChart->measurements ?? []; @endphp
                        @if(is_array($measurements) && count($measurements) > 0)
                            @php $isAssoc = array_keys($measurements) !== range(0, count($measurements) - 1); @endphp
                            @if($isAssoc)
                                @php
                                    $allKeys = [];
                                    foreach ($measurements as $sl => $vals) {
                                        if (is_array($vals))
                                            $allKeys = array_unique(array_merge($allKeys, array_keys($vals)));
                                    }
                                @endphp
                                <div class="table-responsive">
                                    <table class="table table-sm mq-size-table">
                                        <thead>
                                            <tr>
                                                <th>Size</th>@foreach($allKeys as $k)<th>{{ strtoupper($k) }}</th>@endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($measurements as $sl => $vals)
                                                <tr>
                                                    <td>{{ $sl }}</td>@foreach($allKeys as $k)<td>{{ $vals[$k] ?? '—' }}</td>@endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                @php $keys = is_array($measurements[0]) ? array_keys($measurements[0]) : []; @endphp
                                <div class="table-responsive">
                                    <table class="table table-sm mq-size-table">
                                        <thead>
                                            <tr>@foreach($keys as $k)<th>{{ strtoupper($k) }}</th>@endforeach</tr>
                                        </thead>
                                        <tbody>
                                            @foreach($measurements as $row)
                                                <tr>@foreach($keys as $k)<td>{{ $row[$k] ?? '—' }}</td>@endforeach</tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        @endif
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="mq-btn mq-btn--outline" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── IMAGE ZOOM MODAL ── --}}
    <div class="modal fade mq-zoom-modal" id="imageZoomModal" tabindex="-1" aria-labelledby="imageZoomModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen m-0">
            <div class="modal-content mq-zoom-content">
                <span id="imageZoomModalLabel" class="visually-hidden">{{ $product->name }} — zoomed image</span>
                <button type="button" class="mq-zoom-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                @if($images->count() > 1)
                    <button type="button" class="mq-zoom-arrow mq-zoom-arrow--prev" id="zoomPrevBtn" aria-label="Previous image">&#8249;</button>
                    <button type="button" class="mq-zoom-arrow mq-zoom-arrow--next" id="zoomNextBtn" aria-label="Next image">&#8250;</button>
                @endif
                <div class="mq-zoom-viewport" id="zoomViewport">
                    <img id="zoomImage" src="" alt="{{ $product->name }}">
                </div>
                @if($images->count() > 1)
                    <div class="mq-zoom-counter" id="zoomCounter"></div>
                @endif
                <p class="mq-zoom-hint">Click image to zoom</p>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* ═══════════════════════════════════════════════
                   Luxury Product Page - Mobile Fixed
                   ═══════════════════════════════════════════════ */

        :root {
            --mq-black: #000000;
            --mq-white: #ffffff;
            --mq-grey: #f5f5f5;
            --mq-mid: #999999;
            --mq-border: #e0e0e0;
            --mq-text: #1a1a1a;
            --mq-font: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        .product-page *,
        .mq-sticky-bar * {
            box-sizing: border-box;
        }

        .product-page {
            font-family: var(--mq-font);
            background: var(--mq-white);
            color: var(--mq-text);
            font-size: 13px;
            line-height: 1.6;
            padding-top: 85px;
        }

        .mq-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 32px;
        }

        /* Breadcrumb */
        .mq-breadcrumb {
            padding: 12px 0;
            border-bottom: 1px solid var(--mq-border);
        }

        .mq-breadcrumb .mq-container {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .mq-breadcrumb a,
        .mq-breadcrumb span {
            font-size: 11px;
            letter-spacing: 0.02em;
        }

        .mq-breadcrumb a { color: var(--mq-text); text-decoration: none; }
        .mq-breadcrumb a:hover { text-decoration: underline; }
        .mq-breadcrumb span { color: var(--mq-mid); }
        .mq-breadcrumb__current { color: var(--mq-mid); }

        /* Product Grid */
        .mq-product {
            padding: 32px 0 60px;
        }

        .mq-product__grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 64px;
            align-items: start;
        }

        /* Gallery - Desktop */
        .mq-product__gallery {
            position: sticky;
            top: 100px;
        }

        .mq-gallery__main {
            width: 100%;
            background: #f8f9fa;
            position: relative;
            aspect-ratio: 3/4;
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
        }

        .mq-gallery__main::-webkit-scrollbar { display: none; }

        .mq-gallery__slide {
            flex: 0 0 100%;
            width: 100%;
            height: 100%;
            position: relative;
            scroll-snap-align: start;
            scroll-snap-stop: always;
        }

        .mq-gallery__main img {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center center;
            background-color: #f8f9fa;
        }

        .mq-gallery__thumbs {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .mq-gallery__thumbs::-webkit-scrollbar { display: none; }

        .mq-thumb {
            flex-shrink: 0;
            width: 80px;
            height: 80px;
            border: 1px solid transparent;
            padding: 0;
            background: none;
            cursor: pointer;
            overflow: hidden;
        }

        .mq-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background-color: #f8f9fa;
        }

        .mq-thumb.is-active,
        .mq-thumb:hover {
            border-color: var(--mq-black);
        }

        /* Zoom trigger + button */
        .mq-zoom-trigger {
            cursor: zoom-in;
        }

        .mq-zoom-btn {
            position: absolute;
            bottom: 12px;
            right: 12px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--mq-border);
            border-radius: 50%;
            cursor: pointer;
            color: var(--mq-text);
            z-index: 2;
            transition: background 0.2s ease;
        }

        .mq-zoom-btn:hover {
            background: var(--mq-white);
        }

        /* Zoom lightbox modal */
        .mq-zoom-modal .modal-content.mq-zoom-content {
            background: rgba(255, 255, 255, 0.98);
            border: none;
            border-radius: 0;
            height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .mq-zoom-close {
            position: absolute;
            top: 20px;
            right: 24px;
            width: 40px;
            height: 40px;
            background: none;
            border: none;
            font-size: 32px;
            line-height: 1;
            cursor: pointer;
            color: var(--mq-text);
            z-index: 5;
        }

        .mq-zoom-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 1px solid var(--mq-border);
            background: var(--mq-white);
            font-size: 24px;
            line-height: 1;
            cursor: pointer;
            z-index: 5;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mq-zoom-arrow--prev { left: 24px; }
        .mq-zoom-arrow--next { right: 24px; }

        .mq-zoom-viewport {
            width: 100%;
            height: 100%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: zoom-in;
            touch-action: none;
        }

        .mq-zoom-viewport img {
            max-width: 90%;
            max-height: 85vh;
            object-fit: contain;
            transform-origin: center center;
            transition: transform 0.15s ease-out;
            user-select: none;
            pointer-events: none;
        }

        .mq-zoom-viewport.is-zoomed {
            cursor: zoom-out;
        }

        .mq-zoom-viewport.is-zoomed img {
            transition: none;
        }

        .mq-zoom-counter {
            position: absolute;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            letter-spacing: 0.05em;
            color: var(--mq-mid);
        }

        .mq-zoom-hint {
            position: absolute;
            bottom: 24px;
            right: 24px;
            font-size: 11px;
            color: var(--mq-mid);
            margin: 0;
        }

        /* Product Info */
        .mq-product__info {
            padding-top: 8px;
        }

        .mq-product__name {
            font-size: 20px;
            font-weight: 400;
            letter-spacing: 0.01em;
            margin: 0 0 10px;
            line-height: 1.3;
        }

        .mq-product__price {
            font-size: 14px;
            margin-bottom: 24px;
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 8px;
        }

        .mq-price--sale { color: #b02a37; }

        .mq-price--compare {
            color: #8a8a8a;
            font-size: 12px;
        }

        .mq-price__badge {
            background: #b02a37;
            color: #fff;
            font-size: 10px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 3px 8px;
        }

        .mq-sale-note {
            font-size: 11px;
            letter-spacing: 0.04em;
            color: #8a8a8a;
            margin: -16px 0 20px;
        }

        /* Colour swatches */
        .mq-swatches {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .mq-swatch {
            background: none;
            border: 0;
            padding: 2px;
            cursor: pointer;
            border-radius: 50%;
            line-height: 0;
            box-shadow: inset 0 0 0 1px transparent;
            transition: box-shadow 0.2s ease;
        }

        .mq-swatch__chip {
            display: block;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--swatch);
        }

        /* Pale colours need an outline or they vanish against the page */
        .mq-swatch.is-light .mq-swatch__chip {
            box-shadow: inset 0 0 0 1px var(--mq-border);
        }

        .mq-swatch:hover { box-shadow: inset 0 0 0 1px #bbb; }

        .mq-swatch.is-selected,
        .mq-swatch:focus-visible {
            box-shadow: inset 0 0 0 1px #000;
        }

        .mq-chosen-color {
            font-weight: 300;
            text-transform: none;
            letter-spacing: 0.02em;
        }

        /* Option rows, buttons, accordions... (keeping your existing styles for these) */
        .mq-option-row { margin-bottom: 20px; }
        .mq-option-label {
            display: block;
            font-size: 11px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 10px;
            font-weight: 500;
        }

        .mq-select-wrap { position: relative; }
        .mq-select {
            width: 100%;
            appearance: none;
            border: 1px solid var(--mq-border);
            padding: 10px 36px 10px 14px;
            font-size: 12px;
            background: var(--mq-white);
            border-radius: 0;
        }

        .mq-cta-group {
            display: flex;
            gap: 8px;
            margin: 24px 0 28px;
        }

        .mq-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-weight: 500;
            height: 44px;
            padding: 0 20px;
            border: none;
            border-radius: 0;
            cursor: pointer;
        }

        .mq-btn--primary {
            background: var(--mq-black);
            color: var(--mq-white);
            flex: 1;
        }

        /* Accordions */
        .mq-accordion { border-top: 1px solid var(--mq-border); }
        .mq-accordion:last-child { border-bottom: 1px solid var(--mq-border); }

        .mq-accordion__trigger {
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 14px 0;
            background: none;
            border: none;
            font-size: 12px;
            text-align: left;
        }

        .mq-accordion__icon {
            width: 12px;
            transition: transform 0.25s ease;
        }

        .mq-accordion__trigger.is-open .mq-accordion__icon {
            transform: rotate(180deg);
        }

        .mq-accordion__body {
            padding-bottom: 16px;
            font-size: 12px;
            color: #555;
        }

        /* Similar Items */
        .mq-similar {
            padding: 32px 0 48px;
            border-top: 1px solid var(--mq-border);
        }

        .mq-product-card {
            flex: 0 0 calc(25% - 12px);
            text-decoration: none;
            color: inherit;
        }

        .mq-product-card__img-wrap {
            aspect-ratio: 3/4;
            overflow: hidden;
        }

        .mq-product-card__img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* Sticky Bar */
        .mq-sticky-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--mq-white);
            border-top: 1px solid var(--mq-border);
            z-index: 1000;
            padding: 10px 0;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .mq-sticky-bar.is-visible {
            transform: translateY(0);
        }

        .mq-sticky-bar__inner {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* ────────────────────────────────
           RESPONSIVE - TABLET
           ──────────────────────────────── */
        @media (max-width: 900px) {
            .mq-product__grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .mq-product__gallery {
                position: static;
            }

            .mq-gallery__main {
                aspect-ratio: 1 / 1 !important;
                height: auto !important;
            }

            .mq-gallery__main img {
                object-fit: cover !important;
                object-position: center center !important;
            }

            .mq-sticky-bar__name { display: none; }
            .mq-similar__arrows { display: none; }
        }

        /* ────────────────────────────────
           RESPONSIVE - MOBILE (FIXED)
           ──────────────────────────────── */
        @media (max-width: 520px) {
            .product-page {
                padding-top: 70px;
            }

            .mq-container {
                padding: 0 16px;
            }

            .mq-product {
                padding: 16px 0 32px;
            }

            .mq-product__name {
                font-size: 20px;
                font-weight: 500;
            }

            .mq-product__price {
                font-size: 16px;
            }

            /* CRITICAL FIX for feathery/irregular images like Blush Riott */
            .mq-gallery__main {
                aspect-ratio: 4 / 5 !important;   /* Slightly taller than square */
                height: auto !important;
                max-height: 520px;
                background: #f8f9fa;
            }

            .mq-gallery__main img {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: 100% !important;
                object-fit: cover !important;           /* Changed from contain */
                object-position: center top !important; /* Important: top bias for dresses */
            }

            .mq-gallery__thumbs {
                margin-left: -16px;
                margin-right: -16px;
                padding: 0 16px;
            }

            .mq-similar__track-wrap {
                margin-left: -16px;
                margin-right: -16px;
                padding: 0 16px;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                scrollbar-width: none;
            }

            .mq-similar__track-wrap::-webkit-scrollbar {
                display: none;
            }

            .mq-product-card {
                flex: 0 0 calc(65% - 16px);
                scroll-snap-align: start;
            }

            .mq-cta-group {
                flex-direction: column;
            }

            .mq-btn--primary {
                width: 100%;
            }

            .mq-sticky-bar__actions .mq-btn {
                width: 100%;
            }

            .mq-zoom-hint {
                display: none;
            }

            .mq-zoom-arrow {
                width: 36px;
                height: 36px;
                font-size: 20px;
            }

            .mq-zoom-arrow--prev { left: 8px; }
            .mq-zoom-arrow--next { right: 8px; }

            .mq-zoom-viewport img {
                max-width: 100%;
            }
        }
    </style>
@endpush


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            /* ── Image gallery: scroll-snap track + thumbnail sync ── */
            const mainTrack = document.getElementById('mainImageTrack');
            const thumbs = document.querySelectorAll('.mq-thumb');

            window.scrollToImage = (index) => {
                const slide = mainTrack?.children[index];
                if (slide) slide.scrollIntoView({ behavior: 'smooth', inline: 'start', block: 'nearest' });
            };

            if (mainTrack && thumbs.length) {
                let scrollTimeout;
                mainTrack.addEventListener('scroll', () => {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(() => {
                        const index = Math.round(mainTrack.scrollLeft / mainTrack.clientWidth);
                        thumbs.forEach((t, i) => t.classList.toggle('is-active', i === index));
                    }, 100);
                });
            }

            /* ── Image zoom lightbox ── */
            const zoomModalEl = document.getElementById('imageZoomModal');
            if (zoomModalEl) {
                const zoomModal = new bootstrap.Modal(zoomModalEl);
                const zoomViewport = document.getElementById('zoomViewport');
                const zoomImage = document.getElementById('zoomImage');
                const zoomCounter = document.getElementById('zoomCounter');
                const prevBtn = document.getElementById('zoomPrevBtn');
                const nextBtn = document.getElementById('zoomNextBtn');
                const galleryImages = Array.from(document.querySelectorAll('.mq-zoom-trigger')).map(img => img.src);
                const ZOOM_SCALE = 2.5;
                let currentIndex = 0;

                const resetZoom = () => {
                    zoomViewport.classList.remove('is-zoomed');
                    zoomImage.style.transform = 'scale(1)';
                };

                const showImage = (index) => {
                    currentIndex = (index + galleryImages.length) % galleryImages.length;
                    zoomImage.src = galleryImages[currentIndex];
                    resetZoom();
                    if (zoomCounter) zoomCounter.textContent = `${currentIndex + 1} / ${galleryImages.length}`;
                };

                const openZoom = (index) => {
                    showImage(index);
                    zoomModal.show();
                };

                document.querySelectorAll('.mq-zoom-trigger, .mq-zoom-btn').forEach(el => {
                    el.addEventListener('click', (e) => {
                        e.preventDefault();
                        openZoom(parseInt(el.dataset.index, 10) || 0);
                    });
                });

                prevBtn?.addEventListener('click', () => showImage(currentIndex - 1));
                nextBtn?.addEventListener('click', () => showImage(currentIndex + 1));

                const panTo = (clientX, clientY) => {
                    const rect = zoomImage.getBoundingClientRect();
                    const originX = Math.min(100, Math.max(0, ((clientX - rect.left) / rect.width) * 100));
                    const originY = Math.min(100, Math.max(0, ((clientY - rect.top) / rect.height) * 100));
                    zoomImage.style.transformOrigin = `${originX}% ${originY}%`;
                };

                zoomViewport.addEventListener('click', (e) => {
                    if (zoomViewport.classList.contains('is-zoomed')) {
                        resetZoom();
                        return;
                    }
                    panTo(e.clientX, e.clientY);
                    zoomImage.style.transform = `scale(${ZOOM_SCALE})`;
                    zoomViewport.classList.add('is-zoomed');
                });

                zoomViewport.addEventListener('mousemove', (e) => {
                    if (zoomViewport.classList.contains('is-zoomed')) panTo(e.clientX, e.clientY);
                });

                zoomViewport.addEventListener('touchmove', (e) => {
                    const touch = e.touches[0];
                    if (touch && zoomViewport.classList.contains('is-zoomed')) panTo(touch.clientX, touch.clientY);
                }, { passive: true });

                zoomModalEl.addEventListener('hidden.bs.modal', resetZoom);

                document.addEventListener('keydown', (e) => {
                    if (!zoomModalEl.classList.contains('show')) return;
                    if (e.key === 'ArrowRight') showImage(currentIndex + 1);
                    if (e.key === 'ArrowLeft') showImage(currentIndex - 1);
                });
            }

            /* ── Accordions ── */
            document.querySelectorAll('.mq-accordion__trigger').forEach(btn => {
                btn.addEventListener('click', () => {
                    const body = document.getElementById(btn.dataset.target);
                    const open = btn.classList.contains('is-open');
                    btn.classList.toggle('is-open', !open);
                    body.style.display = open ? 'none' : 'block';
                });
            });
            const firstBody = document.getElementById('desc-body');
            if (firstBody) firstBody.style.display = 'block';

            /* ── Size select sync ── */
            const sizeSelect = document.getElementById('size-select');
            if (sizeSelect) {
                sizeSelect.addEventListener('change', () => {
                    document.getElementById('size_label').value = sizeSelect.value;
                });
            }

            /* ── Colour swatches ── */
            const colorInput = document.getElementById('color_name');
            const swatches = Array.from(document.querySelectorAll('.mq-swatch'));
            const chosenColorLabel = document.getElementById('chosenColor');

            function setChosenColour(name) {
                if (colorInput) colorInput.value = name;
                if (chosenColorLabel) chosenColorLabel.textContent = name ? ': ' + name : '';
            }

            swatches.forEach((swatch) => {
                swatch.addEventListener('click', () => {
                    swatches.forEach((other) => {
                        other.classList.toggle('is-selected', other === swatch);
                        other.setAttribute('aria-checked', other === swatch ? 'true' : 'false');
                    });
                    setChosenColour(swatch.dataset.color);
                });
            });

            // A product with a single colour is pre-selected server side; mirror
            // that in the label so the customer sees what they are buying.
            if (colorInput && colorInput.value) {
                setChosenColour(colorInput.value);
            }

            /* ── Add to cart ── */
            const form = document.getElementById('addToCartForm');
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();

                    if (sizeSelect && !document.getElementById('size_label').value) {
                        showNotification('Please choose a size before adding this piece to your bag.', 'error');
                        sizeSelect.focus();
                        return;
                    }

                    if (swatches.length && colorInput && !colorInput.value) {
                        const names = swatches.map((s) => s.dataset.color).join(', ');
                        showNotification('Please choose a colour before adding this piece to your bag (' + names + ').', 'error');
                        swatches[0].focus();
                        return;
                    }

                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                        .then(async (r) => {
                            // Error bodies carry the reason, so parse either way.
                            const data = await r.json().catch(() => ({}));
                            return { ok: r.ok, status: r.status, data };
                        })
                        .then(({ ok, status, data }) => {
                            if (ok && data.success) {
                                showNotification(data.message || 'Added to your bag.', 'success');
                                if (data.cartCount !== undefined) updateCartBadge(data.cartCount);
                                return;
                            }

                            if (status === 419) {
                                showNotification('Your session expired. Reload the page and try again — nothing was added.', 'error');
                            } else if (data.errors) {
                                showNotification(Object.values(data.errors).flat().join(' '), 'error');
                            } else {
                                showNotification(data.message || 'This piece could not be added to your bag right now.', 'error');
                            }
                        })
                        .catch(() => showNotification('Could not reach the store. Check your connection and try again.', 'error'));
                });
            }

            /* ── Sticky bar on scroll ── */
            const stickyBar = document.getElementById('stickyBar');
            const ctaSection = document.querySelector('.mq-cta-group');
            if (stickyBar && ctaSection) {
                const observer = new IntersectionObserver(([entry]) => {
                    stickyBar.classList.toggle('is-visible', !entry.isIntersecting);
                }, { threshold: 0 });
                observer.observe(ctaSection);
            }

            /* ── Similar items slider ── */
            const track = document.getElementById('similarTrack');
            if (track) {
                let pos = 0;
                const cardW = () => track.children[0]?.offsetWidth + 16 || 0;
                const maxPos = () => -(track.children.length - 4) * cardW();

                document.getElementById('nextBtn')?.addEventListener('click', () => {
                    pos = Math.max(pos - cardW(), maxPos());
                    track.style.transform = `translateX(${pos}px)`;
                });
                document.getElementById('prevBtn')?.addEventListener('click', () => {
                    pos = Math.min(pos + cardW(), 0);
                    track.style.transform = `translateX(${pos}px)`;
                });
            }
        });

        function updateCartBadge(count) {
            const badge = document.querySelector('.cart-badge');
            if (badge) { badge.textContent = count; badge.style.display = count > 0 ? 'inline-block' : 'none'; }
        }

        function showNotification(msg, type = 'info') {
            const cls = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
            const el = document.createElement('div');
            el.className = `alert ${cls} alert-dismissible fade show position-fixed`;
            el.style.cssText = 'top:80px;right:20px;z-index:9999;max-width:280px;border-radius:0;font-size:12px;';
            el.innerHTML = `${msg}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
            document.body.appendChild(el);
            setTimeout(() => el.parentNode && el.remove(), 3000);
        }
    </script>
@endpush