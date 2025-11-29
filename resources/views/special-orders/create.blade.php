@extends('layouts.app')

@section('title', 'Request Special Order')

@section('content')
<div class="container py-5">
    <h2 class="section-title mb-4">Request a Special Order</h2>

    <form method="POST" action="{{ route('special-orders.store') }}">
        @csrf

        <div class="mb-3">
            <label class="form-label">Title</label>
            <input name="title" class="form-control" required value="{{ old('title') }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="6" required>{{ old('description') }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Measurements (optional)</label>
            <textarea name="measurements" class="form-control" rows="3">{{ old('measurements') }}</textarea>
        </div>

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Estimated Budget (EGP)</label>
                <input name="estimated_price" type="number" step="0.01" class="form-control" value="{{ old('estimated_price') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Deposit Amount (EGP)</label>
                <input name="deposit_amount" type="number" step="0.01" class="form-control" value="{{ old('deposit_amount') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Desired Delivery Date</label>
                <input name="desired_delivery_date" type="date" class="form-control" value="{{ old('desired_delivery_date') }}">
            </div>
        </div>

        <div class="mb-3 mt-3">
            <label class="form-label">Attach to Product (optional)</label>
            <select name="product_id" class="form-select">
                <option value="">-- No product --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Additional Message (optional)</label>
            <textarea name="message" class="form-control" rows="3">{{ old('message') }}</textarea>
        </div>

        <div class="d-grid">
            <button class="btn btn-primary">Submit Special Order</button>
        </div>
    </form>
</div>
@endsection
