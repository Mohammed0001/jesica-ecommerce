@extends('layouts.app')

@section('title', 'Profile Settings')

@section('content')
<main class="profile-page">
    <!-- Page Header -->
    <section class="profile-header">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h1 class="page-title">Profile Settings</h1>
                    <p class="page-subtitle">Manage your account information and preferences</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Profile Content -->
    <section class="profile-content">
        <div class="container">
            <div class="row">
                <!-- Profile Information -->
                <div class="col-lg-8 mx-auto">
                    <div class="profile-card">
                        <div class="card-header">
                            <h2 class="card-title">Profile Information</h2>
                            <p class="card-subtitle">Update your account's profile information and email address.</p>
                        </div>
                        <div class="card-body">
                            @include('profile.partials.update-profile-information-form')
                        </div>
                    </div>

                    <div class="profile-card">
                        <div class="card-header">
                            <h2 class="card-title">Update Password</h2>
                            <p class="card-subtitle">Ensure your account is using a long, random password to stay secure.</p>
                        </div>
                        <div class="card-body">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>

                    <div class="profile-card">
                        <div class="card-header">
                            <h2 class="card-title">Delete Account</h2>
                            <p class="card-subtitle">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
                        </div>
                        <div class="card-body">
                            @include('profile.partials.delete-user-form')
                        </div>
                    </div>

                    <div class="profile-card">
                        <div class="card-header">
                            <h2 class="card-title">Addresses</h2>
                            <p class="card-subtitle">Manage your shipping and billing addresses.</p>
                        </div>
                        <div class="card-body">
                            @if($user->addresses->isEmpty())
                                <p class="text-muted">No saved addresses yet.</p>
                            @else
                                <ul class="list-group mb-3">
                                    @foreach($user->addresses as $addr)
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>{{ $addr->full_name }}</strong>
                                                <div class="text-muted">{{ $addr->formatted_address }}</div>
                                            </div>
                                            <div>
                                                @if($addr->is_default)
                                                    <span class="badge bg-primary me-2">Default</span>
                                                @endif
                                                <form method="POST" action="{{ route('profile.address.destroy', $addr) }}" style="display:inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                                </form>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <h5>Add New Address</h5>
                            <form method="POST" action="{{ route('profile.address.store') }}">
                                @csrf
                                {{-- First/Last name removed from address form; profile name will be used as fallback --}}
                                <div class="mb-2">
                                    <label class="form-label">Company (optional)</label>
                                    <input name="company" class="form-control" value="{{ old('company') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Address line 1</label>
                                    <input name="address_line_1" class="form-control" required value="{{ old('address_line_1') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Address line 2 (optional)</label>
                                    <input name="address_line_2" class="form-control" value="{{ old('address_line_2') }}">
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">City <span class="text-danger">*</span></label>
                                        <select name="city" class="form-control" required>
                                            <option value="">Select city...</option>
                                            @foreach(\App\Models\BostaCity::orderBy('name')->get() as $bostaCity)
                                                <option value="{{ $bostaCity->name }}" {{ old('city') == $bostaCity->name ? 'selected' : '' }}>
                                                    {{ $bostaCity->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Select from BOSTA supported cities</small>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">District / Zone <span class="text-danger">*</span></label>
                                        <input name="state_province" class="form-control" required value="{{ old('state_province') }}" placeholder="e.g., Nasr City, Maadi, etc.">
                                        <small class="form-text text-muted">Min 3 characters</small>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <label class="form-label">Postal code</label>
                                        <input name="postal_code" class="form-control" value="{{ old('postal_code') }}">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label">Country</label>
                                    <input name="country" class="form-control" required value="{{ old('country', 'Egypt') }}">
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" name="is_default" id="is_default" class="form-check-input" {{ old('is_default') ? 'checked' : '' }}>
                                    <label for="is_default" class="form-check-label">Set as default address</label>
                                </div>
                                <button class="btn btn-primary">Save address</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

@push('styles')
<style>
.profile-page {
    font-family: 'futura-pt', sans-serif;
}

/* Profile Header */
.profile-header {
    padding: 3rem 0 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
}

.page-title {
    font-weight: 200;
    font-size: 3rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    letter-spacing: 0.02em;
}

.page-subtitle {
    font-weight: 200;
    font-size: 1.125rem;
    color: var(--text-muted);
    margin-bottom: 0;
}

/* Profile Content */
.profile-content {
    padding: 3rem 0;
}

.profile-card {
    background: white;
    border: 1px solid var(--border-light);
    border-radius: 8px;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.card-header {
    padding: 2rem 2rem 1rem;
    border-bottom: 1px solid var(--border-light);
}

.card-title {
    font-weight: 300;
    font-size: 1.5rem;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    letter-spacing: 0.02em;
}

.card-subtitle {
    font-weight: 200;
    color: var(--text-muted);
    margin-bottom: 0;
    line-height: 1.5;
}

.card-body {
    padding: 2rem;
}

/* Override default form styles */
.profile-card .form-label {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    font-size: 0.875rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.profile-card .form-control {
    border-radius: 0;
    border: 1px solid var(--border-light);
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    padding: 0.75rem;
    transition: all 0.3s ease;
}

.profile-card .form-control:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.1);
}

.profile-card .btn {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 0.75rem 2rem;
    border-radius: 0;
    transition: all 0.3s ease;
}

.profile-card .btn-primary {
    background: var(--primary-color);
    border: 2px solid var(--primary-color);
    color: white;
}

.profile-card .btn-primary:hover {
    background: transparent;
    color: var(--primary-color);
}

.profile-card .btn-secondary {
    background: transparent;
    color: var(--text-muted);
    border: 2px solid var(--border-light);
}

.profile-card .btn-secondary:hover {
    background: var(--text-muted);
    color: white;
    border-color: var(--text-muted);
}

.profile-card .btn-danger {
    background: #dc3545;
    border: 2px solid #dc3545;
    color: white;
}

.profile-card .btn-danger:hover {
    background: transparent;
    color: #dc3545;
}

.profile-card .text-sm {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
    color: var(--text-muted);
}

.profile-card .text-danger {
    color: #dc3545 !important;
}

.profile-card .invalid-feedback {
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    font-size: 0.875rem;
}

/* Alert styles */
.alert {
    border-radius: 4px;
    font-family: 'futura-pt', sans-serif;
    font-weight: 200;
    margin-bottom: 1rem;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-title {
        font-size: 2.5rem;
    }

    .profile-card {
        margin-bottom: 1.5rem;
    }

    .card-header,
    .card-body {
        padding: 1.5rem;
    }
}
</style>
@endpush
@endsection
