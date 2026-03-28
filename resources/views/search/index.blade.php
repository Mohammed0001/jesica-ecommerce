@extends('layouts.app')

@section('title', 'Search')@section('content')<main class="mq-search-page">

        {{-- ── SEARCH HEADER ── --}}
        <div class="mq-search__header">
            <div class="mq-search__header-inner">

                <p class="mq-search__label">Search</p>

                <form method="GET" action="{{ route('search') }}" class="mq-search__form" autocomplete="off">
                    <div class="mq-search__field">
                        <input
                            type="text"
                            id="searchInput"
                            name="q"
                            value="{{ $query ?? '' }}"
                            placeholder="Search products, collections..."
                            class="mq-search__input"
                            autocomplete="off"
                        >
                        <button type="submit" class="mq-search__submit" aria-label="Search">
                            <svg viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg" width="15" height="15">
                                <circle cx="7.5" cy="7.5" r="5.5" stroke="currentColor" stroke-width="1.2"/>
                                <path d="M11.5 11.5L16 16" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </form>

                {{-- Suggestions dropdown --}}
                <div id="searchSuggestions" class="mq-suggestions hidden"></div>

            </div>
        </div>

        {{-- ── RESULTS ── --}}
        @if(!empty($query))
            <div class="mq-search__results">
                <div class="mq-search__results-inner">

                    @forelse($collections->merge($products) as $item)
                        @php
                            $isCollection = $item instanceof \App\Models\Collection;
                            $title = $isCollection ? $item->title : $item->name;
                            $currencySymbol = config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP');
                            $subtitle = $isCollection
                                ? $item->products_count . ' items'
                                : $currencySymbol . ' ' . number_format($item->price, 2);
                            $image = $isCollection
                                ? $item->images->first()?->url ?? null
                                : $item->main_image?->url ?? null;
                            $url = $isCollection
                                ? route('collections.show', $item->slug)
                                : route('products.show', $item->slug);
                            $typeLabel = $isCollection ? 'Collection' : 'Product';
                        @endphp

                        <a href="{{ $url }}" class="mq-result">
                            {{-- Image --}}
                            <div class="mq-result__img-wrap">
                                @if($image)
                                    <img src="{{ $image }}" alt="{{ $title }}" class="mq-result__img" loading="lazy">
                                @else
                                    <div class="mq-result__img-placeholder"></div>
                                @endif
                            </div>

                            {{-- Info --}}
                            <div class="mq-result__body">
                                <span class="mq-result__type">{{ $typeLabel }}</span>
                                <p class="mq-result__name">{{ $title }}</p>
                                @if(!$isCollection && $item->collection)
                                    <p class="mq-result__sub">{{ $item->collection->title }}</p>
                                @endif
                            </div>

                            {{-- Price / count --}}
                            <div class="mq-result__meta">
                                <span class="mq-result__price">{{ $subtitle }}</span>
                            </div>

                            {{-- Arrow --}}
                            <div class="mq-result__arrow">
                                <svg viewBox="0 0 8 14" fill="none" width="8" height="14">
                                    <path d="M1 1l6 6-6 6" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                                </svg>
                            </div>
                        </a>

                    @empty
                        <div class="mq-no-results">
                            <p class="mq-no-results__dash">—</p>
                            <p class="mq-no-results__text">No results for "{{ $query }}"</p>
                            <p class="mq-no-results__hint">Try a different search term.</p>
                        </div>
                    @endforelse

                </div>
            </div>

        @else
            {{-- ── EMPTY STATE ── --}}
            <div class="mq-search__empty">
                <p class="mq-search__empty-prompt">Begin typing to explore the collection</p>
                <div class="mq-search__tags">
                    @foreach(['Minimal', 'Ceramic', 'Handmade', 'Sculpture', 'Artisan'] as $tag)
                        <a href="{{ route('search') }}?q={{ strtolower($tag) }}" class="mq-search__tag">{{ $tag }}</a>
                    @endforeach
                </div>
            </div>
        @endif

    </main>
@endsection


@push('styles')
    <style>
    /* ═══════════════════════════════════════════════
       MQ — McQueen-style Search Page
       ═══════════════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; }

    .mq-search-page {
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        background: #fff;
        color: #1a1a1a;
        min-height: calc(100vh - 60px);
    }

    /* ── Header ── */
    .mq-search__header {
        border-bottom: 1px solid #e0e0e0;
        padding: 48px 0 0;
    }
    .mq-search__header-inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 32px 32px;
        position: relative;
    }

    .mq-search__label {
        font-size: 11px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #000;
        margin: 0 0 14px;
        font-weight: 500;
    }

    /* Search field — underline only */
    .mq-search__form { position: relative; }
    .mq-search__field {
        display: flex;
        align-items: center;
        border-bottom: 1px solid #000;
        padding-bottom: 8px;
        gap: 10px;
        max-width: 480px;
    }
    .mq-search__input {
        flex: 1;
        border: none;
        outline: none;
        font-family: inherit;
        font-size: 14px;
        color: #1a1a1a;
        background: transparent;
        padding: 0;
        letter-spacing: 0.02em;
    }
    .mq-search__input::placeholder { color: #bbb; }

    .mq-search__submit {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: #000;
        display: flex;
        align-items: center;
        flex-shrink: 0;
        transition: opacity 0.2s;
    }
    .mq-search__submit:hover { opacity: 0.4; }

    /* Suggestions */
    .mq-suggestions {
        position: absolute;
        top: calc(100% + 4px);
        left: 32px;
        width: 480px;
        background: #fff;
        border: 1px solid #e0e0e0;
        z-index: 100;
    }
    .mq-suggestions.hidden { display: none; }
    .mq-suggestion-item {
        padding: 12px 16px;
        font-size: 13px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.15s;
        letter-spacing: 0.01em;
        color: #1a1a1a;
    }
    .mq-suggestion-item:last-child { border-bottom: none; }
    .mq-suggestion-item:hover { background: #f8f8f8; }

    /* ── Results list ── */
    .mq-search__results { padding: 0; }
    .mq-search__results-inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 32px;
    }

    .mq-result {
        display: flex;
        align-items: center;
        gap: 0;
        text-decoration: none;
        color: #1a1a1a;
        border-bottom: 1px solid #e0e0e0;
        transition: background 0.15s;
    }
    .mq-result:first-child { border-top: none; }
    .mq-result:hover { background: #fafafa; }

    .mq-result__img-wrap {
        width: 120px;
        height: 120px;
        flex-shrink: 0;
        overflow: hidden;
        background: #f5f5f5;
        margin: 16px 24px 16px 0;
    }
    .mq-result__img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.4s ease;
    }
    .mq-result:hover .mq-result__img { transform: scale(1.04); }
    .mq-result__img-placeholder { width: 100%; height: 100%; background: #eee; }

    .mq-result__body { flex: 1; padding: 16px 0; }
    .mq-result__type {
        display: block;
        font-size: 10px;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #999;
        margin-bottom: 6px;
    }
    .mq-result__name {
        font-size: 14px;
        font-weight: 400;
        margin: 0 0 4px;
        letter-spacing: 0.01em;
        line-height: 1.4;
    }
    .mq-result__sub {
        font-size: 11px;
        color: #999;
        margin: 0;
        letter-spacing: 0.02em;
    }

    .mq-result__meta {
        padding: 0 32px;
        flex-shrink: 0;
        text-align: right;
    }
    .mq-result__price {
        font-size: 13px;
        color: #1a1a1a;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    .mq-result__arrow {
        flex-shrink: 0;
        color: #ccc;
        transition: color 0.2s;
    }
    .mq-result:hover .mq-result__arrow { color: #000; }

    /* ── No results ── */
    .mq-no-results {
        padding: 80px 0;
        border-top: 1px solid #e0e0e0;
    }
    .mq-no-results__dash {
        font-size: 32px;
        font-weight: 300;
        color: #ccc;
        margin: 0 0 16px;
    }
    .mq-no-results__text {
        font-size: 16px;
        font-weight: 400;
        color: #1a1a1a;
        margin: 0 0 8px;
        letter-spacing: 0.01em;
    }
    .mq-no-results__hint {
        font-size: 12px;
        color: #999;
        margin: 0;
    }

    /* ── Empty state ── */
    .mq-search__empty {
        max-width: 1200px;
        margin: 0 auto;
        padding: 64px 32px;
    }
    .mq-search__empty-prompt {
        font-size: 13px;
        color: #999;
        letter-spacing: 0.04em;
        margin: 0 0 32px;
    }
    .mq-search__tags {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
    }
    .mq-search__tag {
        font-size: 12px;
        color: #1a1a1a;
        text-decoration: none;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        border-bottom: 1px solid transparent;
        padding-bottom: 2px;
        transition: border-color 0.2s;
    }
    .mq-search__tag:hover { border-color: #000; }

    /* ── Responsive ── */
    @media (max-width: 640px) {
        .mq-search__header-inner,
        .mq-search__results-inner,
        .mq-search__empty { padding: 0 16px; }
        .mq-search__header { padding-top: 32px; }
        .mq-result__meta { padding: 0 16px; }
        .mq-result__img-wrap { width: 80px; height: 80px; }
        .mq-suggestions { left: 16px; width: calc(100vw - 32px); }
    }
    </style>
@endpush


@push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const input       = document.getElementById('searchInput');
        const suggestions = document.getElementById('searchSuggestions');
        let timer;

        const fetchSuggestions = (q) => {
            fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(q)}`)
                .then(r => r.json())
                .then(data => {
                    suggestions.innerHTML = '';
                    if (!data.length) { suggestions.classList.add('hidden'); return; }

                    data.forEach(term => {
                        const div = document.createElement('div');
                        div.className = 'mq-suggestion-item';
                        div.textContent = term;
                        div.onclick = () => { input.value = term; input.form.submit(); };
                        suggestions.appendChild(div);
                    });
                    suggestions.classList.remove('hidden');
                })
                .catch(() => suggestions.classList.add('hidden'));
        };

        input?.addEventListener('input', function () {
            clearTimeout(timer);
            const q = this.value.trim();
            if (q.length < 2) { suggestions.classList.add('hidden'); return; }
            timer = setTimeout(() => fetchSuggestions(q), 250);
        });

        document.addEventListener('click', (e) => {
            if (!input?.contains(e.target) && !suggestions?.contains(e.target)) {
                suggestions.classList.add('hidden');
            }
        });
    });
    </script>
@endpush