@extends('layouts.admin')

@section('title', 'Collections Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="page-title">Collections</h1>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.collections.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Collection
                </a>
            </div>
        </div>
    </div>

    <!-- Collections List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6>All Collections ({{ $collections->total() }})</h6>
                    <div class="d-flex gap-2">
                        <select class="form-select form-select-sm" style="width: auto;">
                            <option>All Status</option>
                            <option>Published</option>
                            <option>Draft</option>
                        </select>
                        <div class="input-group" style="width: 250px;">
                            <input type="text" class="form-control form-control-sm" placeholder="Search collections...">
                            <button class="btn btn-outline-secondary btn-sm" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($collections->count() > 0)
                        <div class="table-responsive">
                            <table class="table activity-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">Image</th>
                                        <th>Collection</th>
                                        <th>Products Count</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th style="width: 150px;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($collections as $collection)
                                        <tr>
                                            <td>
                                                @if($collection->image_path)
                                                    <img src="{{ asset('storage/' . $collection->image_path) }}"
                                                         alt="{{ $collection->title }}"
                                                         class="collection-thumbnail">
                                                @else
                                                    <div class="collection-thumbnail-placeholder">
                                                        <i class="fas fa-layer-group"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="collection-name">{{ $collection->title }}</div>
                                                    <div class="collection-description">{{ Str::limit($collection->description, 60) }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="products-count">{{ $collection->products_count }} products</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-{{ $collection->visible ? 'success' : 'secondary' }}">
                                                        {{ $collection->visible ? 'Published' : 'Draft' }}
                                                    </span>
                                                    <form method="POST" action="{{ route('admin.collections.toggle-visibility', $collection) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-link p-0" title="Toggle visibility">
                                                            <i class="fas fa-{{ $collection->visible ? 'eye-slash' : 'eye' }}"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                            <td>{{ $collection->created_at->format('M j, Y') }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.collections.show', $collection) }}"
                                                       class="btn btn-sm btn-outline-primary" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.collections.edit', $collection) }}"
                                                       class="btn btn-sm btn-outline-secondary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('admin.collections.destroy', $collection) }}"
                                                          class="d-inline" onsubmit="return confirm('Are you sure you want to delete this collection?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="card-footer">
                            {{ $collections->links() }}
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-layer-group"></i>
                            <p>No collections found</p>
                            <a href="{{ route('admin.collections.create') }}" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-2"></i>Create Your First Collection
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.collection-thumbnail {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid var(--border-light);
}

.collection-thumbnail-placeholder {
    width: 50px;
    height: 50px;
    background-color: var(--border-light);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-muted);
}

.collection-name {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 1rem;
    color: var(--primary-color);
    margin-bottom: 0.25rem;
}

.collection-description {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--text-muted);
    line-height: 1.4;
}

.products-count {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--primary-color);
    padding: 0.25rem 0.75rem;
    background-color: rgba(0, 0, 0, 0.05);
    border-radius: 0;
}
</style>
@endpush
@endsection
