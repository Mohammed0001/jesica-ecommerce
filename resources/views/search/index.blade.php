@extends('layouts.app')

@section('title', 'Search Results')

@section('content')
<main class="search-page">
    <!-- Search Header -->
    <section class="search-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h1 class="search-title">
                        @if($query)
                            Search Results for "{{ $query }}"
                        @else
                            Search
                        @endif
                    </h1>

                    <!-- Search Form -->
                    <form method="GET" action="{{ route('search') }}" class="search-form">
                        <div class="search-input-group">
                            <input type="text" name="q" value="{{ $query }}"
                                   placeholder="Search products and collections..."
                                   class="search-input" id="searchInput" autocomplete="off">
                            <button type="submit" class="search-button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div class="search-suggestions" id="searchSuggestions" style="display: none;"></div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Results -->
    @if($query)
    <section class="search-results">
        <div class="container">
            @if($products->count() > 0 || $collections->count() > 0)
                <!-- Collections Results -->
                @if($collections->count() > 0)
                <div class="results-section">
                    <h2 class="results-title">Collections</h2>
                    <div class="row">
                        @foreach($collections as $collection)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="collection-card">
                                <a href="{{ route('collections.show', $collection->slug) }}" class="collection-link">
                                    <div class="collection-image">
                                        @if($collection->image_path)
                                            <img src="{{ asset('images/' . $collection->image_path) }}" alt="{{ $collection->title }}">
                                        @else
                                            <div class="collection-placeholder">
                                                <i class="fas fa-images"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="collection-info">
                                        <h3 class="collection-name">{{ $collection->title }}</h3>
                                        <p class="collection-count">{{ $collection->products_count }} {{ Str::plural('product', $collection->products_count) }}</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Products Results -->
                @if($products->count() > 0)
                <div class="results-section">
                    <h2 class="results-title">Products</h2>
                    <div class="row">
                        @foreach($products as $product)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="product-card">
                                <a href="{{ route('products.show', $product->slug) }}" class="product-link">
                                    <div class="product-image">
                                        @if($product->image)
                                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}">
                                        @else
                                            <div class="product-placeholder">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-name">{{ $product->name }}</h3>
                                        <p class="product-price">${{ number_format($product->price, 2) }}</p>
                                        @if($product->collection)
                                            <p class="product-collection">{{ $product->collection->title }}</p>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @else
                <!-- No Results -->
                <div class="no-results">
                    <div class="row">
                        <div class="col-lg-6 mx-auto text-center">
                            <i class="fas fa-search no-results-icon"></i>
                            <h3 class="no-results-title">No results found</h3>
                            <p class="no-results-message">
                                We couldn't find anything matching "{{ $query }}".
                                Try searching with different keywords or browse our collections.
                            </p>
                            <div class="no-results-actions">
                                <a href="{{ route('collections.index') }}" class="btn btn-primary">
                                    Browse Collections
                                </a>
                                <a href="{{ route('home') }}" class="btn btn-outline-primary">
                                    Back to Home
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
    @else
    <!-- Search Suggestions when no query -->
    <section class="search-suggestions-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="suggestions-title">What are you looking for?</h2>
                    <p class="suggestions-subtitle">Search for products, collections, or use one of these popular searches:</p>

                    <div class="popular-searches">
                        <a href="{{ route('search') }}?q=handmade" class="search-tag">Handmade</a>
                        <a href="{{ route('search') }}?q=ceramic" class="search-tag">Ceramic</a>
                        <a href="{{ route('search') }}?q=unique" class="search-tag">Unique</a>
                        <a href="{{ route('search') }}?q=artisan" class="search-tag">Artisan</a>
                        <a href="{{ route('search') }}?q=custom" class="search-tag">Custom</a>
                    </div>

                    <div class="browse-options mt-5">
                        <h3 class="browse-title">Or browse by category:</h3>
                        <div class="browse-links">
                            <a href="{{ route('collections.index') }}" class="browse-link">
                                <i class="fas fa-th-large"></i>
                                All Collections
                            </a>
                            <a href="{{ route('home') }}" class="browse-link">
                                <i class="fas fa-star"></i>
                                Featured Items
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif
</main>

@push('styles')
<style>
.search-page {
    font-family: 'futura-pt', sans-serif;
    min-height: 60vh;
}

/* Search Header */
.search-header {
    padding: 3rem 0 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.search-title {
    font-weight: 200;
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 2rem;
    text-align: center;
    letter-spacing: 0.02em;
}

.search-form {
    position: relative;
    max-width: 500px;
    margin: 0 auto;
}

.search-input-group {
    position: relative;
    display: flex;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-radius: 0;
}

.search-input {
    flex: 1;
    padding: 1rem 1.5rem;
    border: 2px solid var(--primary-color);
    border-right: none;
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 1rem;
    outline: none;
    border-radius: 0;
}

.search-input:focus {
    border-color: var(--primary-color);
    box-shadow: none;
}

.search-button {
    padding: 1rem 1.5rem;
    background: var(--primary-color);
    color: white;
    border: 2px solid var(--primary-color);
    border-left: none;
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 0;
}

.search-button:hover {
    background: #333;
    border-color: #333;
}

.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid var(--border-light);
    border-top: none;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.suggestion-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--border-light);
    cursor: pointer;
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    transition: background-color 0.2s ease;
}

.suggestion-item:hover {
    background: #f8f9fa;
}

.suggestion-item:last-child {
    border-bottom: none;
}

/* Search Results */
.search-results {
    padding: 3rem 0;
}

.results-section {
    margin-bottom: 3rem;
}

.results-title {
    font-weight: 300;
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 2rem;
    letter-spacing: 0.02em;
}

/* Collection Cards */
.collection-card {
    border: 1px solid var(--border-light);
    border-radius: 4px;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
}

.collection-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.collection-link {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
}

.collection-image {
    aspect-ratio: 16/9;
    overflow: hidden;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.collection-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.collection-placeholder {
    color: var(--text-muted);
    font-size: 2rem;
}

.collection-info {
    padding: 1.5rem;
}

.collection-name {
    font-weight: 300;
    font-size: 1.25rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.collection-count {
    font-weight: 200;
    color: var(--text-muted);
    margin-bottom: 0;
}

/* Product Cards */
.product-card {
    border: 1px solid var(--border-light);
    border-radius: 4px;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
}

.product-card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.product-link {
    text-decoration: none;
    color: inherit;
    display: block;
    height: 100%;
}

.product-image {
    aspect-ratio: 1;
    overflow: hidden;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-placeholder {
    color: var(--text-muted);
    font-size: 2rem;
}

.product-info {
    padding: 1.5rem;
}

.product-name {
    font-weight: 300;
    font-size: 1.25rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.product-price {
    font-weight: 400;
    font-size: 1.125rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.product-collection {
    font-weight: 200;
    color: var(--text-muted);
    margin-bottom: 0;
    font-size: 0.875rem;
}

/* No Results */
.no-results {
    padding: 4rem 0;
    text-align: center;
}

.no-results-icon {
    font-size: 4rem;
    color: var(--border-light);
    margin-bottom: 1.5rem;
}

.no-results-title {
    font-weight: 300;
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.no-results-message {
    font-weight: 200;
    color: var(--text-muted);
    margin-bottom: 2rem;
    line-height: 1.6;
}

.no-results-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

/* Search Suggestions Section */
.search-suggestions-section {
    padding: 3rem 0;
}

.suggestions-title {
    font-weight: 300;
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.suggestions-subtitle {
    font-weight: 200;
    color: var(--text-muted);
    margin-bottom: 2rem;
}

.popular-searches {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 3rem;
}

.search-tag {
    padding: 0.5rem 1rem;
    background: var(--primary-color);
    color: white;
    text-decoration: none;
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    transition: all 0.3s ease;
}

.search-tag:hover {
    background: #333;
    color: white;
    transform: translateY(-1px);
}

.browse-options {
    text-align: center;
}

.browse-title {
    font-weight: 300;
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-bottom: 1.5rem;
}

.browse-links {
    display: flex;
    gap: 2rem;
    justify-content: center;
    flex-wrap: wrap;
}

.browse-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1.5rem;
    border: 1px solid var(--border-light);
    text-decoration: none;
    color: var(--primary-color);
    transition: all 0.3s ease;
    min-width: 150px;
}

.browse-link:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.browse-link i {
    font-size: 1.5rem;
}

/* Buttons */
.btn {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 0.75rem 2rem;
    border-radius: 0;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: var(--primary-color);
    border: 2px solid var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background: transparent;
    color: var(--primary-color);
}

.btn-outline-primary {
    background: transparent;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.btn-outline-primary:hover {
    background: var(--primary-color);
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .search-title {
        font-size: 2rem;
    }

    .search-input-group {
        flex-direction: column;
    }

    .search-input {
        border-right: 2px solid var(--primary-color);
        border-bottom: none;
    }

    .search-button {
        border-left: 2px solid var(--primary-color);
        border-top: none;
    }

    .popular-searches {
        gap: 0.5rem;
    }

    .browse-links {
        flex-direction: column;
        align-items: center;
    }

    .no-results-actions {
        flex-direction: column;
        align-items: center;
    }

    .btn {
        width: 100%;
        max-width: 300px;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const suggestionsDiv = document.getElementById('searchSuggestions');
    let debounceTimer;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            const query = this.value.trim();

            if (query.length >= 2) {
                debounceTimer = setTimeout(() => {
                    fetchSuggestions(query);
                }, 300);
            } else {
                hideSuggestions();
            }
        });

        searchInput.addEventListener('blur', function() {
            // Hide suggestions after a small delay to allow clicking
            setTimeout(() => {
                hideSuggestions();
            }, 200);
        });

        searchInput.addEventListener('focus', function() {
            const query = this.value.trim();
            if (query.length >= 2) {
                fetchSuggestions(query);
            }
        });
    }

    function fetchSuggestions(query) {
        fetch(`{{ route('search.suggestions') }}?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(suggestions => {
                showSuggestions(suggestions);
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
                hideSuggestions();
            });
    }

    function showSuggestions(suggestions) {
        if (suggestions.length === 0) {
            hideSuggestions();
            return;
        }

        suggestionsDiv.innerHTML = '';
        suggestions.forEach(suggestion => {
            const div = document.createElement('div');
            div.className = 'suggestion-item';
            div.textContent = suggestion;
            div.addEventListener('click', function() {
                searchInput.value = suggestion;
                hideSuggestions();
                searchInput.form.submit();
            });
            suggestionsDiv.appendChild(div);
        });
        suggestionsDiv.style.display = 'block';
    }

    function hideSuggestions() {
        suggestionsDiv.style.display = 'none';
    }
});
</script>
@endpush
@endsection
