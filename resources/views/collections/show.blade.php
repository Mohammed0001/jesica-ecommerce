@extends('layouts.app')

@section('title', $collection->title)

@section('content')
    <main class="collection-pure" style="background:#fff; color:#000; line-height:1.6;">
        <div class="container">

            <!-- HERO – Maximum breathing room -->
            <section class="pt-12 pb-12">
                <div class="row g-8">

                    <!-- Images -->
                    <div class="col-lg-7">
                        @php
                            $images = $collection->images->count()
                                ? $collection->images
                                : collect([
                                    $collection->image_path
                                        ? (object) ['url' => Storage::url($collection->image_path)]
                                        : null,
                                ])->filter();
                            $first = $images->first()?->url ?? asset('images/placeholder.jpg');
                        @endphp

                        <!-- Main Image -->
                        <div class="mb-8 position-relative">
                            <img id="mainImg" src="{{ $first }}" alt="{{ $collection->title }}" class="w-100"
                                style="height:720px; object-fit:cover;">
                            @if ($images->count() > 1)
                                <div id="imageCounter"
                                    class="position-absolute bottom-0 end-0 mb-5 me-5 bg-white px-4 py-2 small fw-medium tracking-widest">
                                    1 / {{ $images->count() }}
                                </div>
                            @endif
                        </div>

                        <!-- Thumbnails – Clean & Spacious -->
                        @if ($images->count() > 1)
                            <div class="d-flex flex-wrap gap-5 justify-content-start">
                                @foreach ($images as $i => $img)
                                    <div data-index="{{ $i }}" data-src="{{ $img->url ?? Storage::url($img) }}"
                                        class="thumbnail cursor-pointer transition-all {{ $i === 0 ? 'active' : '' }}"
                                        style="width:120px; height:120px; overflow:hidden;">
                                        <img src="{{ $img->url ?? Storage::url($img) }}"
                                            class="w-100 h-100 object-fit-cover">
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Text Content -->
                    <div class="col-lg-5 pt-10">
                        <nav class="small text-uppercase tracking-widest text-muted mb-8">
                            <a href="{{ route('home') }}" class="text-muted text-decoration-none">Home</a>
                            <span class="mx-4">·</span>
                            <a href="{{ route('collections.index') }}"
                                class="text-muted text-decoration-none">Collections</a>
                            <span class="mx-4">·</span>
                            <span class="text-black">{{ $collection->title }}</span>
                        </nav>

                        <h1 class="display-2 fw-light mb-10 lh-1">{{ $collection->title }}</h1>

                        @if ($collection->description)
                            <p class="lead fw-light text-muted mb-12" style="max-width:88%; font-size:1.2rem;">
                                {!! nl2br(e($collection->description)) !!}
                            </p>
                        @endif

                        <div class="row g-8 mb-12 text-center text-lg-start">
                            <div class="col-6 col-lg-12">
                                <div class="display-4 fw-light mb-3">{{ $products->total() }}</div>
                                <div class="small text-uppercase tracking-widest text-muted">Pieces</div>
                            </div>
                            @if ($collection->release_date)
                                <div class="col-6 col-lg-12">
                                    <div class="display-4 fw-light mb-3">{{ $collection->release_date->format('Y') }}</div>
                                    <div class="small text-uppercase tracking-widest text-muted">Released</div>
                                </div>
                            @endif
                        </div>

                        @if ($collection->pdf_path)
                            <button id="openLookbook"
                                class="btn btn-outline-dark px-6 py-4 text-uppercase tracking-widest fw-medium"
                                style="letter-spacing:0.2em; border:1px solid #000; border-radius: 0; font-size:0.95rem;">
                                View Lookbook
                            </button>
                        @endif
                    </div>
                </div>
            </section>

            <!-- PRODUCTS -->
            <section class="pb-16">
                <div class="d-flex justify-content-between align-items-center mb-10">
                    <h2 class="h4 fw-light text-uppercase tracking-widest mb-0">Collection Pieces</h2>
                    <select class="form-select border-0 bg-transparent text-uppercase tracking-wider small"
                        onchange="location=this.value">
                        <option value="{{ route('collections.show', $collection->slug) }}"
                            {{ !request('sort') ? 'selected' : '' }}>Default</option>
                        <option value="{{ route('collections.show', [$collection->slug, 'sort' => 'name']) }}"
                            {{ request('sort') == 'name' ? 'selected' : '' }}>Name</option>
                        <option value="{{ route('collections.show', [$collection->slug, 'sort' => 'price_low']) }}"
                            {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low–High</option>
                        <option value="{{ route('collections.show', [$collection->slug, 'sort' => 'price_high']) }}"
                            {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High–Low</option>
                        <option value="{{ route('collections.show', [$collection->slug, 'sort' => 'newest']) }}"
                            {{ request('sort') == 'newest' ? 'selected' : '' }}>Newest</option>
                    </select>
                </div>

                @if ($products->count())
                    <div class="row g-8">
                        @foreach ($products as $product)
                            <div class="col-md-6 col-lg-4">
                                <a href="{{ route('products.show', $product->slug) }}"
                                    class="text-decoration-none text-black d-block">
                                    <div class="mb-6 overflow-hidden">
                                        <img src="{{ $product->main_image?->url ?? asset('images/placeholder.jpg') }}"
                                            class="w-100" style="height:520px; object-fit:cover;"
                                            alt="{{ $product->name }}">
                                    </div>
                                    <h3 class="h5 fw-medium text-uppercase tracking-wider mb-4">{{ $product->name }}</h3>
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div class="fw-bold fs-4">{!! $product->formatted_price !!}</div>
                                        <span class="small text-uppercase tracking-widest text-muted">View →</span>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    @if ($products->hasPages())
                        <div class="text-center mt-12">
                            {{ $products->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-16">
                        <h3 class="display-5 fw-light mb-6">Coming Soon</h3>
                        <p class="lead text-muted">This collection is being curated.</p>
                    </div>
                @endif
            </section>
        </div>
    </main>

    <!-- Lookbook Modal -->
    @if ($collection->pdf_path)
        <div class="modal fade" id="lookbookModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content border-0 overflow-hidden" style="position:relative;background:transparent;">

                    <!-- Close button -->
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-4 z-3" data-bs-dismiss="modal"
                        style="font-size: 2rem; filter: drop-shadow(0 0 8px rgba(0,0,0,0.5));">
                    </button>

                    <!-- PDF Container with Loader -->
                    <div class="pdf-container">
                        <!-- Loading Overlay with your logo -->
                        <div class="pdf-loading-overlay" id="pdfLoading">
                            <img src="{{ asset('images/signature-logo.png') }}" alt="Jesica Riad Signature"
                                class="loading-logo" style="filter: invert(1); height: 90px;">
                            <p class="mt-4 text-muted fs-5">Loading lookbook...</p>
                        </div>

                        <!-- PDF iFrame -->
                        <iframe id="pdfIframe" data-src="{{ Storage::url($collection->pdf_path) }}" class="pdf-iframe"
                            frameborder="0">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('styles')
        <style>
            *,
            *::before,
            *::after {
                border-radius: 0 !important;
            }

            img {
                image-rendering: -webkit-optimize-contrast;
            }

            .thumbnail {
                outline: 4px solid transparent;
                outline-offset: -4px;
                transition: outline 0.4s ease;
            }

            .thumbnail.active,
            .thumbnail:hover {
                outline: 4px solid #000;
            }

            .btn:hover {
                background: #000 !important;
                color: #fff !important;
            }

            /* Extra breathing room */
            .pt-12 {
                padding-top: 8rem !important;
            }

            .pb-12 {
                padding-bottom: 8rem !important;
            }

            .mb-12 {
                margin-bottom: 8rem !important;
            }

            .mb-10 {
                margin-bottom: 6rem !important;
            }

            .mb-8 {
                margin-bottom: 5rem !important;
            }

            .gap-8 {
                gap: 5rem !important;
            }

            .pdf-container {
                position: relative;
                width: 90%;
                height: 90vh;
                background: #000;
                margin: auto;

                /* fallback while PDF loads */
            }

            .pdf-iframe {
                width: 100%;
                height: 100%;
                border: none;
                display: block;
                background: transparent;
            }

            .pdf-loading-overlay {
                position: absolute;
                inset: 0;
                background: rgba(255, 255, 255, 0.98);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                z-index: 2;
                transition: opacity 0.6s ease;
            }

            .loading-logo {
                animation: gentlePulse 2.2s infinite ease-in-out;
            }

            @keyframes gentlePulse {

                0%,
                100% {
                    transform: scale(1);
                }

                50% {
                    transform: scale(1.1);
                }
            }

            @keyframes pulse {
                0% {
                    transform: scale(1);
                }

                50% {
                    transform: scale(1.08);
                }

                100% {
                    transform: scale(1);
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.addEventListener('click', function() {
                    const src = this.dataset.src;
                    const index = parseInt(this.dataset.index) + 1;

                    // Change main image
                    document.getElementById('mainImg').src = src;

                    // Update counter
                    const counter = document.getElementById('imageCounter');
                    if (counter) counter.textContent = index + ' / {{ $images->count() }}';

                    // Update active state
                    document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            // Open Lookbook
            document.getElementById('openLookbook')?.addEventListener('click', () => {
                new bootstrap.Modal('#lookbookModal').show();
            });
            document.addEventListener('DOMContentLoaded', function() {
                const modalEl = document.getElementById('lookbookModal');
                const iframe = document.getElementById('pdfIframe');
                const loader = document.getElementById('pdfLoading');

                modalEl.addEventListener('shown.bs.modal', function() {
                    // Only load the PDF when the modal is actually opened (saves bandwidth)
                    if (iframe.src === '' || iframe.src === location.href) {
                        iframe.src = iframe.dataset.src;
                    }

                    // When iframe finishes loading → hide loader smoothly
                    iframe.onload = function() {
                        setTimeout(() => {
                            loader.style.opacity = '0';
                            setTimeout(() => loader.style.display = 'none', 600);
                        }, 200);
                    };

                    // Fallback: hide loader after 20 seconds max (in case of error)
                    setTimeout(() => {
                        loader.style.opacity = '0';
                        setTimeout(() => loader.style.display = 'none', 600);
                    }, 20000);
                });

                // Optional: reset when modal closes (so it shows again next time)
                modalEl.addEventListener('hidden.bs.modal', function() {
                    loader.style.opacity = '1';
                    loader.style.display = 'flex';
                    iframe.src = ''; // optional: unload PDF to free memory
                });
            });
        </script>
    @endpush
@endsection
