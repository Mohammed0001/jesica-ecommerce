@extends('layouts.admin')

@section('title', 'Settings')

@section('content')
<div class="container py-4">
    <h1>Site Settings</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Delivery Fee (display currency)</label>
            <input name="delivery_fee" class="form-control" required value="{{ old('delivery_fee', $delivery_fee) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Free Delivery Threshold (display currency)</label>
            <input name="delivery_threshold" class="form-control" required value="{{ old('delivery_threshold', $delivery_threshold) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Tax Percentage (%)</label>
            <input name="tax_percentage" class="form-control" required value="{{ old('tax_percentage', $tax_percentage) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Service Fee Percentage (%)</label>
            <input name="service_fee_percentage" class="form-control" required value="{{ old('service_fee_percentage', $service_fee_percentage) }}">
        </div>

        <button class="btn btn-primary">Save</button>
    </form>
</div>

@endsection
