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
                                ? $product->images->prepend($product->main_image)
                                : collect([$product->main_image ?? (object) ['url' => $mainImageUrl]]);
                        @endphp

                        <div class="mq-gallery__main">
                            <img id="mainImage" src="{{ $mainImageUrl }}" alt="{{ $product->name }}" loading="lazy">
                        </div>

                        @if($images->count() > 1)
                            <div class="mq-gallery__thumbs">
                                @foreach($images as $index => $image)
                                    <button class="mq-thumb {{ $index === 0 ? 'is-active' : '' }}"
                                        onclick="changeMainImage('{{ $image->url }}', this)" type="button">
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
                            {!! $product->formatted_price !!}
                            @if($product->compare_price && $product->compare_price > $product->price)
                                <s class="mq-price--compare">{{ number_format($product->compare_price, 0) }}</s>
                            @endif
                        </div>

                        {{-- Color swatch (static — adapt if you have colour variants) --}}
                        <!-- <div class="mq-option-row">
                                            <span class="mq-option-label">Color</span>
                                            <div class="mq-swatches">
                                                <button class="mq-swatch mq-swatch--black is-active" aria-label="Black" type="button"></button>
                                            </div>
                                        </div> -->

                        {{-- Size --}}
                        <div class="mq-option-row">
                            <div class="mq-size-header">
                                <span class="mq-option-label">Size (IT)</span>
                                @if($product->sizeChart)
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#sizeGuideModal"
                                        class="mq-size-guide">Size guide</a>
                                @endif
                            </div>
                            @if($product->sizes && $product->sizes->where('quantity', '>', 0)->count() > 0)
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
                        @if($product->quantity > 0)
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

                        {{-- Contact accordion --}}
                        <div class="mq-accordion">
                            <button class="mq-accordion__trigger" type="button" data-target="contact-body">
                                <span>Contact us</span>
                                <svg class="mq-accordion__icon" viewBox="0 0 12 8" fill="none">
                                    <path d="M1 1l5 5 5-5" stroke="currentColor" stroke-width="1.2" />
                                </svg>
                            </button>
                            <div class="mq-accordion__body" id="contact-body" style="display:none;">
                                <p>You can call or WhatsApp us at <strong>+20 XX XX XX XX XX</strong></p>
                                <p>Email us: <a href="mailto:hello@store.com">hello@store.com</a></p>
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
                @if($product->quantity > 0)
                    <button class="mq-btn mq-btn--primary mq-btn--sm"
                        onclick="document.getElementById('addToCartForm').dispatchEvent(new Event('submit'))">
                        {{ $needsSizeSelection ? 'SELECT A SIZE' : 'ADD TO CART' }}
                    </button>
                    <!-- <button class="mq-btn mq-btn--apple-pay mq-btn--sm" type="button">
                                                    <svg viewBox="0 0 64 28" fill="none" xmlns="http://www.w3.org/2000/svg" height="14">
                                                        <path d="M12.36 4.56c-.73.87-1.9 1.55-3.07 1.45-.15-1.17.43-2.42 1.1-3.19C11.12 1.93 12.4 1.3 13.44 1.25c.13 1.2-.35 2.4-1.08 3.31zm1.07 1.7c-1.7-.1-3.15.97-3.96.97-.82 0-2.06-.92-3.41-.89C4.38 6.37 2.7 7.45 1.77 9.1c-1.9 3.28-.49 8.14 1.35 10.81.9 1.32 1.98 2.78 3.4 2.73 1.35-.05 1.88-.87 3.51-.87 1.64 0 2.12.87 3.56.84 1.47-.03 2.4-1.32 3.3-2.64.99-1.44 1.4-2.85 1.42-2.92-.03-.02-2.73-1.05-2.76-4.17-.03-2.6 2.13-3.86 2.23-3.92-1.22-1.8-3.12-2-3.35-2.04zm9.9-3.58v18.86h2.93v-6.44h4.06c3.7 0 6.3-2.54 6.3-6.22 0-3.68-2.55-6.2-6.2-6.2h-7.09zm2.93 2.46h3.38c2.54 0 3.99 1.35 3.99 3.76 0 2.4-1.45 3.77-4 3.77h-3.37V5.14zm16.3 16.48c1.83 0 3.53-.93 4.3-2.4h.06v2.26h2.71V13.1c0-2.72-2.18-4.48-5.53-4.48-3.11 0-5.41 1.79-5.5 4.24h2.64c.22-1.17 1.3-1.94 2.78-1.94 1.8 0 2.8.84 2.8 2.38v1.05l-3.66.22c-3.4.2-5.24 1.6-5.24 4.02 0 2.44 1.9 4.03 4.64 4.03zm.79-2.24c-1.57 0-2.57-.75-2.57-1.9 0-1.19.97-1.88 2.81-1.99l3.26-.2v1.07c0 1.74-1.48 3.02-3.5 3.02zm11.4 7.26c2.86 0 4.21-1.09 5.38-4.39l5.15-14.4h-2.98l-3.46 11.18h-.06l-3.46-11.18h-3.07l5 13.8-.27.84c-.45 1.42-1.18 1.97-2.49 1.97-.23 0-.68-.03-.87-.05v2.27c.18.05.84.1 1.13.1v-.14z" fill="white"/>
                                                    </svg>
                                                </button> -->
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
@endsection


@push('styles')
    <style>
        /* ═══════════════════════════════════════════════
                   style Luxury Product Page
                   ═══════════════════════════════════════════════ */

        :root {
            --mq-black: #000000;
            --mq-white: #ffffff;
            --mq-grey: #f5f5f5;
            --mq-mid: #999999;
            --mq-border: #e0e0e0;
            --mq-text: #1a1a1a;
            --mq-font: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            --mq-gap: 24px;
        }

        /* Reset */
        .product-page *,
        .mq-sticky-bar * {
            box-sizing: border-box;
        }

        /* Layout wrapper */
        .product-page {
            font-family: var(--mq-font);
            background: var(--mq-white);
            color: var(--mq-text);
            font-size: 13px;
            line-height: 1.6;
        }

        .mq-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 32px;
        }

        /* ── Breadcrumb ── */
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

        .mq-breadcrumb a {
            color: var(--mq-text);
            text-decoration: none;
            font-size: 11px;
            letter-spacing: 0.02em;
        }

        .mq-breadcrumb a:hover {
            text-decoration: underline;
        }

        .mq-breadcrumb span {
            font-size: 11px;
            color: var(--mq-mid);
        }

        .mq-breadcrumb__current {
            color: var(--mq-mid);
        }

        /* ── Product Grid ── */
        .mq-product {
            padding: 32px 0 60px;
        }

        .mq-product__grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 64px;
            align-items: start;
        }

        /* ── Gallery ── */
        .mq-product__gallery {
            position: sticky;
            top: 0;
        }

        .mq-gallery__main {
            width: 100%;
            background: var(--mq-white);
            overflow: hidden;
        }

        .mq-gallery__main img {
            width: 100%;
            height: auto;
            display: block;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .mq-gallery__main img:hover {
            transform: scale(1.02);
        }

        .mq-gallery__thumbs {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            overflow-x: auto;
            scrollbar-width: none;
        }

        .mq-gallery__thumbs::-webkit-scrollbar {
            display: none;
        }

        .mq-thumb {
            flex-shrink: 0;
            width: 80px;
            height: 80px;
            border: 1px solid transparent;
            padding: 0;
            background: none;
            cursor: pointer;
            overflow: hidden;
            transition: border-color 0.2s;
        }

        .mq-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .mq-thumb.is-active,
        .mq-thumb:hover {
            border-color: var(--mq-black);
        }

        /* ── Product Info ── */
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
            font-weight: 400;
            margin-bottom: 24px;
            color: var(--mq-text);
        }

        .mq-price--compare {
            color: var(--mq-mid);
            margin-left: 8px;
            font-size: 13px;
        }

        /* ── Option rows ── */
        .mq-option-row {
            margin-bottom: 20px;
        }

        .mq-option-label {
            display: block;
            font-size: 11px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--mq-text);
            margin-bottom: 10px;
            font-weight: 500;
        }

        /* Color swatches */
        .mq-swatches {
            display: flex;
            gap: 8px;
        }

        .mq-swatch {
            width: 28px;
            height: 28px;
            border: 1px solid transparent;
            border-radius: 50%;
            cursor: pointer;
            padding: 0;
            transition: border-color 0.2s;
        }

        .mq-swatch--black {
            background: #000;
        }

        .mq-swatch.is-active {
            border-color: #000;
            outline: 1px solid #fff;
            outline-offset: -3px;
        }

        /* Size header */
        .mq-size-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .mq-size-guide {
            font-size: 11px;
            color: var(--mq-text);
            text-decoration: underline;
            letter-spacing: 0.02em;
        }

        /* Size select */
        .mq-select-wrap {
            position: relative;
            display: inline-flex;
            align-items: center;
            width: 100%;
        }

        .mq-select {
            width: 100%;
            appearance: none;
            -webkit-appearance: none;
            border: 1px solid var(--mq-border);
            padding: 10px 36px 10px 14px;
            font-size: 12px;
            font-family: var(--mq-font);
            letter-spacing: 0.04em;
            background: var(--mq-white);
            color: var(--mq-text);
            cursor: pointer;
            outline: none;
            transition: border-color 0.2s;
            border-radius: 0;
        }

        .mq-select:focus {
            border-color: var(--mq-black);
        }

        .mq-select-caret {
            position: absolute;
            right: 12px;
            width: 12px;
            pointer-events: none;
            color: var(--mq-text);
        }

        /* ── CTA Buttons ── */
        .mq-cta-group {
            display: flex;
            gap: 8px;
            margin: 24px 0 28px;
        }

        .mq-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: none;
            cursor: pointer;
            font-family: var(--mq-font);
            font-size: 11px;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-weight: 500;
            transition: background 0.2s, opacity 0.2s;
            white-space: nowrap;
            text-decoration: none;
            padding: 0 20px;
            height: 44px;
            border-radius: 0;
        }

        .mq-btn--primary {
            background: var(--mq-black);
            color: var(--mq-white);
            flex: 1;
        }

        .mq-btn--primary:hover:not([disabled]) {
            background: #222;
        }

        .mq-btn--primary[disabled] {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .mq-btn--apple-pay {
            background: var(--mq-black);
            color: var(--mq-white);
            min-width: 100px;
        }

        .mq-btn--apple-pay:hover {
            background: #222;
        }

        .mq-btn--sm {
            height: 36px;
            font-size: 10px;
            padding: 0 16px;
        }

        .mq-btn--outline {
            background: transparent;
            color: var(--mq-black);
            border: 1px solid var(--mq-black);
        }

        /* ── Accordions ── */
        .mq-accordion {
            border-top: 1px solid var(--mq-border);
        }

        .mq-accordion:last-child {
            border-bottom: 1px solid var(--mq-border);
        }

        .mq-accordion__trigger {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: none;
            border: none;
            padding: 14px 0;
            font-family: var(--mq-font);
            font-size: 12px;
            letter-spacing: 0.04em;
            color: var(--mq-text);
            cursor: pointer;
            text-align: left;
        }

        .mq-accordion__icon {
            width: 12px;
            transition: transform 0.25s ease;
            flex-shrink: 0;
        }

        .mq-accordion__trigger.is-open .mq-accordion__icon {
            transform: rotate(180deg);
        }

        .mq-accordion__body {
            padding-bottom: 16px;
            font-size: 12px;
            color: #555;
            line-height: 1.7;
        }

        .mq-accordion__body p {
            margin: 0 0 8px;
        }

        .mq-accordion__body a {
            color: var(--mq-text);
        }

        .mq-view-details {
            font-size: 12px;
            color: var(--mq-text);
            text-decoration: underline;
            display: inline-block;
            margin-top: 4px;
        }

        .mq-shipping-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .mq-shipping-list li {
            padding: 3px 0;
            font-size: 12px;
            color: #555;
        }

        .mq-out-of-stock-note {
            font-size: 12px;
            color: var(--mq-mid);
            margin: 0;
        }

        /* ── Similar Items ── */
        .mq-similar {
            padding: 32px 0 48px;
            border-top: 1px solid var(--mq-border);
        }

        .mq-similar__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .mq-similar__title {
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 0.04em;
            margin: 0;
        }

        .mq-similar__arrows {
            display: flex;
            gap: 4px;
        }

        .mq-arrow {
            background: none;
            border: none;
            font-size: 22px;
            color: var(--mq-text);
            cursor: pointer;
            padding: 4px 8px;
            line-height: 1;
            transition: opacity 0.2s;
        }

        .mq-arrow:hover {
            opacity: 0.5;
        }

        .mq-similar__track-wrap {
            overflow: hidden;
        }

        .mq-similar__track {
            display: flex;
            gap: 16px;
            transition: transform 0.4s ease;
        }

        .mq-product-card {
            flex: 0 0 calc(25% - 12px);
            min-width: 0;
            text-decoration: none;
            color: var(--mq-text);
            display: block;
        }

        .mq-product-card__img-wrap {
            width: 100%;
            aspect-ratio: 3/4;
            overflow: hidden;
            background: var(--mq-white);
        }

        .mq-product-card__img-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }

        .mq-product-card:hover .mq-product-card__img-wrap img {
            transform: scale(1.04);
        }

        .mq-product-card__body {
            padding: 10px 0 0;
        }

        .mq-product-card__name {
            font-size: 12px;
            margin: 0 0 4px;
            line-height: 1.4;
            color: var(--mq-text);
        }

        .mq-product-card__price {
            font-size: 12px;
            color: var(--mq-text);
            margin: 0;
        }

        /* ── Sticky Bar ── */
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

        .mq-sticky-bar__name {
            font-size: 12px;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .mq-sticky-bar__price {
            font-size: 12px;
            white-space: nowrap;
        }

        .mq-sticky-bar__actions {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        /* ── Size table ── */
        .mq-size-table {
            font-size: 12px;
        }

        .mq-size-table th {
            font-weight: 500;
            font-size: 11px;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #ddd;
        }

        .mq-modal-title {
            font-size: 14px;
            font-weight: 400;
            letter-spacing: 0.04em;
        }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .mq-product__grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }

            .mq-product__gallery {
                position: static;
            }

            .mq-product-card {
                flex: 0 0 calc(50% - 8px);
            }

            .mq-sticky-bar__name {
                display: none;
            }
        }

        @media (max-width: 520px) {
            .mq-container {
                padding: 0 16px;
            }

            .mq-product-card {
                flex: 0 0 calc(50% - 8px);
            }

            .mq-cta-group {
                flex-direction: column;
            }

            .mq-btn--apple-pay {
                min-width: 100%;
            }
        }
    </style>
@endpush


@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {

            /* ── Image switcher ── */
            window.changeMainImage = (src, el) => {
                const img = document.getElementById('mainImage');
                img.style.opacity = '0.5';
                setTimeout(() => { img.src = src; img.style.opacity = '1'; }, 150);
                document.querySelectorAll('.mq-thumb').forEach(t => t.classList.remove('is-active'));
                el.classList.add('is-active');
            };

            /* ── Accordions ── */
            document.querySelectorAll('.mq-accordion__trigger').forEach(btn => {
                btn.addEventListener('click', () => {
                    const body = document.getElementById(btn.dataset.target);
                    const open = btn.classList.contains('is-open');
                    btn.classList.toggle('is-open', !open);
                    body.style.display = open ? 'none' : 'block';
                });
            });
            // Open first accordion (description) by default
            const firstBody = document.getElementById('desc-body');
            if (firstBody) firstBody.style.display = 'block';

            /* ── Size select sync ── */
            const sizeSelect = document.getElementById('size-select');
            if (sizeSelect) {
                sizeSelect.addEventListener('change', () => {
                    document.getElementById('size_label').value = sizeSelect.value;
                });
            }

            /* ── Add to cart ── */
            const form = document.getElementById('addToCartForm');
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    if (sizeSelect && !document.getElementById('size_label').value) {
                        showNotification('Please select a size', 'error');
                        sizeSelect.focus();
                        return;
                    }
                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(r => r.json())
                        .then(data => {
                            showNotification(data.message || (data.success ? 'Added to cart' : 'Error'), data.success ? 'success' : 'error');
                            if (data.success && data.cartCount !== undefined) updateCartBadge(data.cartCount);
                        })
                        .catch(() => showNotification('Error adding to cart', 'error'));
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
                const cardW = () => track.children[0]?.offsetWidth + 16 || 0; // card + gap
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