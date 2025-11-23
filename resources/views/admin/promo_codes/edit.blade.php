@extends('layouts.admin')

@section('title', 'Edit Promo Code')

@section('content')
<div class="container-fluid">
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title">Edit Promo Code - {{ $promoCode->code }}</h1>
        <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.promo-codes.update', $promoCode) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" value="{{ $promoCode->code }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="percentage" {{ $promoCode->type === 'percentage' ? 'selected' : '' }}>Percentage</option>
                        <option value="fixed" {{ $promoCode->type === 'fixed' ? 'selected' : '' }}>Fixed</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Value</label>
                    <input type="number" step="0.01" name="value" value="{{ $promoCode->value }}" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Max Uses</label>
                    <input type="number" name="max_uses" value="{{ $promoCode->max_uses }}" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Expires At</label>
                    <input type="date" name="expires_at" value="{{ optional($promoCode->expires_at)->format('Y-m-d') }}" class="form-control">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="active" class="form-check-input" {{ $promoCode->active ? 'checked' : '' }}>
                    <label class="form-check-label">Active</label>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary">Update Promo</button>
                    <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
