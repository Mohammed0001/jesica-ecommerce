@extends('layouts.admin')

@section('title', 'Create Promo Code')

@section('content')
<div class="container-fluid">
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title">Create Promo Code</h1>
        <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.promo-codes.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Value</label>
                    <input type="number" step="0.01" name="value" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Max Uses</label>
                    <input type="number" name="max_uses" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Expires At</label>
                    <input type="date" name="expires_at" class="form-control">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="active" class="form-check-input" checked>
                    <label class="form-check-label">Active</label>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary">Create Promo</button>
                    <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
