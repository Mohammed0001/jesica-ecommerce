@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
    <main class="search-page min-h-screen bg-white">
        <!-- Header + Search Bar -->
        <section class="pt-32 pb-20 px-6 border-b border-gray-200">
            <div class="max-w-3xl mx-auto text-center">
                <h1 class="text-5xl md:text-6xl font-light tracking-tight text-black mb-12 leading-none">
                    @if ($query)
                        “{{ $query }}”
                    @else
                        Search
                    @endif
                </h1>

                <form method="GET" action="{{ route('search') }}" class="relative max-w-2xl mx-auto" style="height:50px;">
                    <input type="text" name="q" value="{{ $query }}"
                        placeholder="Search products, collections..."
                        class="w-full px-10 py-6 text-lg font-light bg-white border border-black focus:outline-none transition-all duration-200 placeholder:text-gray-400"
                        id="searchInput" autocomplete="off"
                        style="height:50px;">
                    <button type="submit"
                        class="absolute right-6 top-1/2 -translate-y-1/2 text-gray-600 hover:text-black transition"
                        style="height: 100%;margin: auto;padding: 5px;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </form>

                <div id="searchSuggestions"
                    class="absolute w-full max-w-2xl left-1/2 -translate-x-1/2 mt-2 bg-white border border-gray-300 shadow-sm overflow-hidden z-50 hidden">
                </div>
            </div>
        </section>

        <!-- Search Results - One Card Per Line -->
        @if ($query)
            <section class="px-6 py-16">
                <div class="max-w-5xl mx-auto space-y-8">

                    @forelse($collections->merge($products) as $item)
                        @php
                            $isCollection = $item instanceof \App\Models\Collection;
                            $title = $isCollection ? $item->title : $item->name;
                            $subtitle = $isCollection
                                ? $item->products_count . ' items'
                                : '$' . number_format($item->price, 2);
                            $image = $isCollection
                                ? $item->images->first()?->url ?? null
                                : $item->main_image?->url ?? null;
                            $url = $isCollection
                                ? route('collections.show', $item->slug)
                                : route('products.show', $item->slug);
                            $typeLabel = $isCollection ? 'COLLECTION' : 'PRODUCT';
                        @endphp

                        <a href="{{ $url }}" class="block group" style="margin: 20px !important;">
                            <div class="border border-gray-300 overflow-hidden hover:bg-gray-50 transition">
                                <div class="flex items-center">
                                    <!-- Image -->
                                    <div
                                        class="w-48 h-48 bg-gray-50 flex-shrink-0 border-r border-gray-300;"style="margin:10px;height:150px;">
                                        @if ($image)
                                            <img src="{{ $image }}" alt="{{ $title }}"
                                                class="w-full h-full object-cover group-hover:opacity-90 transition"
                                                style="height:150px;object-fit:cover;">
                                        @endif
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 px-10 py-12">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-xs font-light text-gray-500 tracking-widest uppercase mb-2">
                                                    {{ $typeLabel }}</p>
                                                <h3 class **text-2xl font-light text-black leading-tight">
                                                    {{ $title }}</h3>
                                                @if (!$isCollection && $item->collection)
                                                    <p class="text-sm text-gray-500 mt-2 font-light">
                                                        {{ $item->collection->title }}</p>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <p class="text-2xl font-light text-black">{{ $subtitle }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Arrow -->
                                    <div class="px-10 text-gray-400 group-hover:text-black transition">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </a>

                    @empty
                        <!-- No Results -->
                        <div class="text-center py-32 border-t border-gray-200">
                            <p class="text-4xl font-light text-gray-400 mb-8">—</p>
                            <h3 class="text-3xl font-light text-gray-800 mb-4">No results found</h3>
                            <p class="text-gray-500 font-light text-lg max-w-md mx-auto leading-relaxed">
                                Try searching for something else.
                            </p>
                        </div>
                    @endforelse
                </div>
            </section>
        @else
            <!-- Empty Search State -->
            <section class="px-6 py-32 text-center border-t border-gray-200">
                <div class="max-w-2xl mx-auto">
                    <p class="text-lg font-light text-gray-600 tracking-wide">
                        Begin typing to explore the collection
                    </p>

                    <div class="mt-16 flex flex-wrap justify-center gap-8">
                        @foreach (['Minimal', 'Ceramic', 'Handmade', 'Sculpture', 'Artisan'] as $tag)
                            <a href="{{ route('search') }}?q={{ strtolower($tag) }}"
                                class="text-sm font-light text-gray-700 hover:text-black transition border-b border-transparent hover:border-black pb-1">
                                {{ $tag }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </main>

    @push('styles')
        <style>
            .search-page {
                font-family: 'Helvetica Neue', 'Futura', system-ui, sans-serif;
                line-height: 1.6;
                width: 90%;
                margin: auto;
            }

            input::placeholder {
                color: #aaa;
                font-weight: 300;
            }


            .suggestion-item {
                padding: 1rem 1.5rem;
                border-bottom: 1px solid #eee;
                cursor: pointer;
                font-weight: 300;
                transition: background 0.2s;
                margin: 20px !important;
            }

            .suggestion-item:hover {
                background: #f8f8f8;
            }

            /* Force zero border radius everywhere */
            * {
                border-radius: 0 !important;
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const input = document.getElementById('searchInput');
                const suggestions = document.getElementById('searchSuggestions');

                const fetchSuggestions = (q) => {
                    fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(q)}`)
                        .then(r => r.json())
                        .then(data => {
                            suggestions.innerHTML = '';
                            if (!data.length) return suggestions.classList.add('hidden');

                            data.forEach(term => {
                                const div = document.createElement('div');
                                div.className = 'suggestion-item';
                                div.textContent = term;
                                div.onclick = () => {
                                    input.value = term;
                                    input.form.submit();
                                };
                                suggestions.appendChild(div);
                            });
                            suggestions.classList.remove('hidden');
                        });
                };

                let timer;
                input?.addEventListener('input', function() {
                    clearTimeout(timer);
                    const q = this.value.trim();
                    if (q.length < 2) return suggestions.classList.add('hidden');
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
@endsection
