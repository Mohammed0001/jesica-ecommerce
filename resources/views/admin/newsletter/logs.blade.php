@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4">Newsletter Log Viewer</h1>
        <div>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-secondary">Back</a>
        </div>
    </div>

    <form method="get" class="mb-3 row g-2 align-items-center">
        <div class="col-auto">
            <label for="q" class="col-form-label">Search</label>
        </div>
        <div class="col-auto flex-grow-1">
            <input id="q" name="q" type="text" class="form-control form-control-sm" placeholder="Search text in logs" value="{{ old('q', $query) }}">
        </div>

        <div class="col-auto">
            <label for="perPage" class="col-form-label">Per page</label>
        </div>
        <div class="col-auto">
            <input id="perPage" name="perPage" type="number" min="1" class="form-control form-control-sm" value="{{ $perPage }}">
        </div>

        <div class="col-auto">
            <button class="btn btn-primary btn-sm" type="submit">Filter</button>
        </div>

        <div class="col-12 mt-2">
            <small class="text-muted">Path: <code>{{ $path }}</code>
                @if($lastModified) • Last modified: {{ $lastModified }} • Total lines: {{ $totalLines }} @endif</small>
        </div>
    </form>

    <div class="card">
        <div class="card-body p-2">
            <div class="mb-2">
                <small class="text-muted">Showing <strong>{{ count($logs) }}</strong> of <strong>{{ $paginator->total() }}</strong> matching lines.</small>
            </div>
            <pre style="white-space:pre-wrap;word-break:break-word;margin:0;font-size:13px;">
@foreach($logs as $line)
{{ $line }}
@endforeach
            </pre>
        </div>
        <div class="card-footer">
            <div class="d-flex justify-content-center">
                {{ $paginator->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
