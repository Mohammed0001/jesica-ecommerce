@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Checkout</h1>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <h4>Items</h4>
    @if($cartItems->isEmpty())
        <p>Your cart is empty.</p>
    @else
        <ul class="list-group mb-3">
            @foreach($cartItems as $item)
                <li class="list-group-item">
                    Product ID: {{ $item['product_id'] }} — Quantity: {{ $item['quantity'] }}
                    @if(!empty($item['size_label']))
                        — Size: {{ $item['size_label'] }}
                    @endif
                </li>
            @endforeach
        </ul>

        <p><strong>Total:</strong> <span id="total-amount">{{ number_format($total, 2) }}</span></p>
        <p><strong>Deposit amount:</strong> <span id="deposit-amount">{{ number_format($depositAmount, 2) }}</span></p>

        <h5>Shipping Address</h5>
        <form method="POST" action="{{ route('checkout.process') }}">
            @csrf

            <div class="mb-3">
                @if($addresses->isEmpty())
                    <p><strong>No saved addresses.</strong> Please provide a shipping address below.</p>

                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label for="first_name">First name</label>
                            <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name') }}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label for="last_name">Last name</label>
                            <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name') }}" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="company">Company (optional)</label>
                        <input type="text" name="company" id="company" class="form-control" value="{{ old('company') }}">
                    </div>

                    <div class="mb-2">
                        <label for="address_line_1">Address line 1</label>
                        <input type="text" name="address_line_1" id="address_line_1" class="form-control" value="{{ old('address_line_1') }}" required>
                    </div>

                    <div class="mb-2">
                        <label for="address_line_2">Address line 2 (optional)</label>
                        <input type="text" name="address_line_2" id="address_line_2" class="form-control" value="{{ old('address_line_2') }}">
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label for="city">City</label>
                            <input type="text" name="city" id="city" class="form-control" value="{{ old('city') }}" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="state_province">State / Province</label>
                            <input type="text" name="state_province" id="state_province" class="form-control" value="{{ old('state_province') }}" required>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="postal_code">Postal code</label>
                            <input type="text" name="postal_code" id="postal_code" class="form-control" value="{{ old('postal_code') }}" required>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="country">Country</label>
                        <input type="text" name="country" id="country" class="form-control" value="{{ old('country', 'Morocco') }}" required>
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" name="save_address" id="save_address" class="form-check-input" {{ old('save_address') ? 'checked' : '' }}>
                        <label for="save_address" class="form-check-label">Save this address to my profile</label>
                    </div>

                @else
                    @foreach($addresses as $address)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="shipping_address_id" id="addr-{{ $address->id }}" value="{{ $address->id }}" {{ $loop->first ? 'checked' : '' }}>
                            <label class="form-check-label" for="addr-{{ $address->id }}">
                                {{ $address->formatted_address ?? $address->address_line_1 }}
                            </label>
                        </div>
                    @endforeach
                    <div class="form-check mt-2 mb-3">
                        <input class="form-check-input" type="radio" name="shipping_address_id" id="use_new_address" value="" {{ old('shipping_address_id') === null ? '' : '' }}>
                        <label class="form-check-label" for="use_new_address">Use a different address</label>
                    </div>

                    {{-- if user chooses different address, they can fill below - we still include the fields so backend can validate them when shipping_address_id is empty --}}
                    <div class="collapse" id="new-address-fields" style="display:none;">
                        <!-- New address inputs (same as above) -->
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="first_name">First name</label>
                                <input type="text" name="first_name" id="first_name" class="form-control" value="{{ old('first_name') }}">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="last_name">Last name</label>
                                <input type="text" name="last_name" id="last_name" class="form-control" value="{{ old('last_name') }}">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="company">Company (optional)</label>
                            <input type="text" name="company" id="company" class="form-control" value="{{ old('company') }}">
                        </div>
                        <div class="mb-2">
                            <label for="address_line_1">Address line 1</label>
                            <input type="text" name="address_line_1" id="address_line_1" class="form-control" value="{{ old('address_line_1') }}">
                        </div>
                        <div class="mb-2">
                            <label for="address_line_2">Address line 2 (optional)</label>
                            <input type="text" name="address_line_2" id="address_line_2" class="form-control" value="{{ old('address_line_2') }}">
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <label for="city">City</label>
                                <input type="text" name="city" id="city" class="form-control" value="{{ old('city') }}">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="state_province">State / Province</label>
                                <input type="text" name="state_province" id="state_province" class="form-control" value="{{ old('state_province') }}">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="postal_code">Postal code</label>
                                <input type="text" name="postal_code" id="postal_code" class="form-control" value="{{ old('postal_code') }}">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label for="country">Country</label>
                            <input type="text" name="country" id="country" class="form-control" value="{{ old('country', 'Morocco') }}">
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="save_address" id="save_address" class="form-check-input" {{ old('save_address') ? 'checked' : '' }}>
                            <label for="save_address" class="form-check-label">Save this address to my profile</label>
                        </div>
                    </div>
                @endif
            </div>

            <div class="mb-3">
                <label for="payment_type">Payment Type</label>
                <select name="payment_type" id="payment_type" class="form-select">
                    <option value="full" {{ old('payment_type') === 'full' ? 'selected' : '' }}>Full</option>
                    <option value="deposit" {{ old('payment_type') === 'deposit' ? 'selected' : '' }}>Deposit</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="payment_method">Payment Method</label>
                <select name="payment_method" id="payment_method" class="form-select" required>
                    <option value="mock_gateway" {{ old('payment_method', env('PAYMENT_PROVIDER', 'mock')) === 'mock' || old('payment_method') === 'mock_gateway' ? 'selected' : '' }}>Mock (test)</option>
                    @if(config('services.paymob.api_key') || env('PAYMENT_PROVIDER') === 'paymob')
                        <option value="paymob" {{ old('payment_method') === 'paymob' || env('PAYMENT_PROVIDER') === 'paymob' ? 'selected' : '' }}>Paymob (card)</option>
                    @endif
                </select>
            </div>

            <div class="mb-3">
                <p><strong>Amount to charge:</strong> <span id="charge-amount">{{ number_format($depositAmount, 2) }}</span></p>
            </div>

            <button class="btn btn-primary">Pay / Place Order</button>
        </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
    (function(){
        const total = {{ json_encode($total) }};
        const deposit = {{ json_encode($depositAmount) }};

        const paymentTypeEl = document.getElementById('payment_type');
        const chargeAmountEl = document.getElementById('charge-amount');

        function updateCharge() {
            if (!paymentTypeEl || !chargeAmountEl) return;
            const v = paymentTypeEl.value;
            if (v === 'full') {
                chargeAmountEl.textContent = Number(total).toFixed(2);
            } else {
                chargeAmountEl.textContent = Number(deposit).toFixed(2);
            }
        }

        if (paymentTypeEl) {
            paymentTypeEl.addEventListener('change', updateCharge);
            // initialize
            updateCharge();
        }
    })();
</script>
@endpush
