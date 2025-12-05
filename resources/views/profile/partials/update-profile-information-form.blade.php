<section>
    @if (session('status') === 'profile-updated')
        <div class="alert alert-success mb-3">
            Profile updated successfully!
        </div>
    @endif

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="profile-form">
        @csrf
        @method('patch')

        <div class="form-group mb-3">
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input
                id="name"
                name="name"
                type="text"
                class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name', $user->name) }}"
                required
                autofocus
                autocomplete="name"
            />
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input
                id="email"
                name="email"
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $user->email) }}"
                required
                autocomplete="username"
            />
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2">
                    <p class="text-sm text-danger">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="btn btn-link p-0 text-decoration-underline">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="text-sm text-success mt-2">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="form-group mb-3">
            <label for="phone" class="form-label">{{ __('Phone Number') }}</label>
            <input
                id="phone"
                name="phone"
                type="tel"
                class="form-control @error('phone') is-invalid @enderror"
                value="{{ old('phone', $user->phone) }}"
                required
                autocomplete="tel"
                placeholder="+20 123 456 7890"
            />
            <small class="form-text text-muted">Required for shipping (format: +201234567890)</small>
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-actions d-flex gap-3">
            <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
                <p class="text-sm text-success mb-0 align-self-center">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
