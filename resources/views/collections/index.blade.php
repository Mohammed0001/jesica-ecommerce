@extends('layouts.app')

@section('title', 'Collections')

@section('content')
<div class="container py-5">
    <!-- Header Section -->
    <div class="section-header">
        <h1 class="section-title">Collections</h1>
        <p class="section-subtitle">Discover our carefully curated fashion collections</p>
    </div>

    <!-- Filter Bar -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div class="d-flex align-items-center gap-3">
            <label for="sort" class="form-label">Sort by:</label>
            <select id="sort" class="form-select" style="width: auto;" onchange="window.location.href = this.value">
                <option value="{{ route('collections.index') }}">Default</option>
                <option value="{{ route('collections.index', ['sort' => 'name']) }}" {{ request('sort') === 'name' ? 'selected' : '' }}>Name</option>
                <option value="{{ route('collections.index', ['sort' => 'newest']) }}" {{ request('sort') === 'newest' ? 'selected' : '' }}>Newest</option>
                <option value="{{ route('collections.index', ['sort' => 'oldest']) }}" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest</option>
            </select>
        </div>
        <span class="text-muted">{{ $collections->count() }} collection{{ $collections->count() !== 1 ? 's' : '' }}</span>
    </div>

    <!-- Collections Grid -->
        <!-- Collections Grid -->
    @if($collections->isNotEmpty())
        <div class="collections-grid">
            @foreach($collections as $collection)
                <div class="collection-card">
                    @if($collection->image_path)
                        <img src="{{ asset('images/' . $collection->image_path) }}"
                             class="collection-image"
                             alt="{{ $collection->title }}">
                    @else
                        <div class="collection-image placeholder">
                            <span>{{ $collection->title }}</span>
                        </div>
                    @endif

                    <div class="collection-content">
                        <h3 class="collection-title">
                            <a href="{{ route('collections.show', $collection->slug) }}" class="text-decoration-none">
                                {{ $collection->title }}
                            </a>
                        </h3>

                        @if($collection->description)
                            <p class="collection-description">
                                {{ Str::limit($collection->description, 120) }}
                            </p>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">
                                {{ $collection->products->count() }} product{{ $collection->products->count() !== 1 ? 's' : '' }}
                            </span>
                            @if($collection->created_at)
                                <span class="text-muted">
                                    {{ $collection->created_at->format('M Y') }}
                                </span>
                            @endif
                        </div>

                        <a href="{{ route('collections.show', $collection->slug) }}" class="btn-outline">
                            View Collection
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-5">
            <h3 class="text-muted mb-3">No Collections Found</h3>
            <p class="text-muted">We're working on bringing you amazing collections. Check back soon!</p>
        </div>
    @endif

    <!-- Pagination -->
    @if($collections->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $collections->links() }}
        </div>
    @endif
</div>
@endsection
