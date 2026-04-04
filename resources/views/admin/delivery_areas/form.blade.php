@extends('layouts.admin')

@section('title', $area ? 'Edit Delivery Area' : 'Add Delivery Area')

@section('content')
<div class="container py-4" style="max-width:640px">
    <div class="mb-4">
        <a href="{{ route('admin.delivery-areas.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Delivery Areas
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">{{ $area ? 'Edit: ' . $area->name : 'New Delivery Area' }}</h5>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST"
                  action="{{ $area ? route('admin.delivery-areas.update', $area) : route('admin.delivery-areas.store') }}">
                @csrf
                @if($area)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label fw-semibold">Area Name <span class="text-danger">*</span></label>
                    <input type="text"
                           name="name"
                           id="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $area?->name) }}"
                           placeholder="e.g., Greater Cairo, Alexandria, Giza"
                           required>
                    <div class="form-text">A descriptive label for this delivery zone (not shown to customers).</div>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label for="delivery_fee" class="form-label fw-semibold">Delivery Fee (EGP) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">EGP</span>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="delivery_fee"
                               id="delivery_fee"
                               class="form-control @error('delivery_fee') is-invalid @enderror"
                               value="{{ old('delivery_fee', $area?->delivery_fee) }}"
                               required
                               placeholder="0.00">
                    </div>
                    <div class="form-text">Enter 0 for free delivery to this area.</div>
                    @error('delivery_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label for="city_names" class="form-label fw-semibold">City Names <span class="text-danger">*</span></label>
                    <input type="text"
                           name="city_names"
                           id="city_names"
                           class="form-control @error('city_names') is-invalid @enderror"
                           value="{{ old('city_names', $area ? implode(', ', $area->city_names ?? []) : '') }}"
                           placeholder="Cairo, Al Qahirah, القاهرة"
                           required>
                    <div class="form-text">
                        Comma-separated list of city names that should use this fee.
                        Matching is <strong>case-insensitive</strong>.
                        Use the exact names shown in the Bosta city dropdown on checkout.
                    </div>
                    @error('city_names')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> {{ $area ? 'Save Changes' : 'Create Area' }}
                    </button>
                    <a href="{{ route('admin.delivery-areas.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
