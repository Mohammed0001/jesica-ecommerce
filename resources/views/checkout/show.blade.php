@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Checkout</h1>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <h4>Items</h4>
    @if($cartItems->isEmpty())
        <p>Your cart is empty.</p>
    @else
        <ul class="list-group mb-3">
            @foreach($cartItems as $item)
                <li class="list-group-item d-flex align-items-center">
                    <div class="me-3" style="width:80px;height:100px;flex:0 0 80px;">
                        @if(!empty($item['product']['main_image_url']))
                            <img src="{{ $item['product']['main_image_url'] }}" alt="{{ $item['product']['title'] }}" class="img-fluid" style="max-height:100px;object-fit:cover;">
                        @else
                            <div class="bg-light" style="width:80px;height:100px;"></div>
                        @endif
                    </div>
                    <div class="flex-grow-1">
                        <div><strong>{{ $item['product']['title'] ?? 'Product #' . $item['product_id'] }}</strong></div>
                        <div class="text-muted">SKU: {{ $item['product']['sku'] ?? 'N/A' }} — Quantity: {{ $item['quantity'] }}</div>
                        @if(!empty($item['size_label']))
                            <div>Size: {{ $item['size_label'] }}</div>
                        @endif
                    </div>
                    <div class="text-end" style="min-width:120px;">
                        <div><strong>Subtotal</strong></div>
                        <div>{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($item['product']['display_subtotal'] ?? $item['display_subtotal'] ?? 0, 2) }}</div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($displaySubtotal, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Discount</span><span>- {{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($discountAmount ?? 0, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Service fee</span><span>{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($serviceFee ?? 0, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Shipping</span><span>{{ $shipping <= 0 ? 'Free' : (config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP')) . ' ' . number_format($shipping, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Tax (est.)</span><span>{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($tax ?? 0, 2) }}</span></div>
                <hr />
                <div class="d-flex justify-content-between mb-0"><strong>Total</strong><strong>{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($total, 2) }}</strong></div>
            </div>
        </div>

        <p><strong>Deposit amount:</strong> <span id="deposit-amount">{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($depositAmount, 2) }}</span></p>

        <h5>Shipping Address</h5>
        <form id="checkoutForm" method="POST" action="{{ route('checkout.process') }}">
            @csrf

            <div class="mb-3">
                @if($addresses->isEmpty())
                    <p><strong>No saved addresses.</strong> Please provide a shipping address below.</p>

                    {{-- Name fields removed: use profile name for recipient --}}

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
                        <input type="text" name="country" id="country" class="form-control" value="{{ old('country', 'Egypt') }}" required>
                    </div>

                    <div class="form-check mb-2">
                        <input type="checkbox" name="save_address" id="save_address" class="form-check-input" {{ old('save_address') ? 'checked' : '' }}>
                        <label for="save_address" class="form-check-label">Save this address to my profile</label>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_default" id="is_default" class="form-check-input" {{ old('is_default') ? 'checked' : '' }}>
                        <label for="is_default" class="form-check-label">Set as my default shipping address</label>
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
                        <!-- New address inputs (name fields removed) -->
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
                            <input type="text" name="country" id="country" class="form-control" value="{{ old('country', 'Egypt') }}">
                        </div>
                        <div class="form-check mb-2">
                            <input type="checkbox" name="save_address" id="save_address" class="form-check-input" {{ old('save_address') ? 'checked' : '' }}>
                            <label for="save_address" class="form-check-label">Save this address to my profile</label>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" name="is_default" id="is_default" class="form-check-input" {{ old('is_default') ? 'checked' : '' }}>
                            <label for="is_default" class="form-check-label">Set as my default shipping address</label>
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
                <p><strong>Amount to charge:</strong> <span id="charge-amount">{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($depositAmount, 2) }}</span></p>
            </div>

            <button type="submit" id="placeOrderButton" class="btn btn-primary">
                <span id="placeOrderLabel">Pay / Place Order</span>
            </button>
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

        // Toggle new-address fields when user selects 'Use a different address'
        const addressRadios = document.querySelectorAll('input[name="shipping_address_id"]');
        const useNewRadio = document.getElementById('use_new_address');
        const newAddressBlock = document.getElementById('new-address-fields');

        function toggleNewAddressBlock() {
            if (!newAddressBlock) return;
            const selected = document.querySelector('input[name="shipping_address_id"]:checked');
            if (selected && selected.id === 'use_new_address') {
                newAddressBlock.style.display = '';
            } else {
                newAddressBlock.style.display = 'none';
            }
        }

        if (addressRadios.length) {
            addressRadios.forEach(r => r.addEventListener('change', toggleNewAddressBlock));
            // initialize
            toggleNewAddressBlock();
        }

        // Prevent multiple submits and provide feedback
        const checkoutForm = document.getElementById('checkoutForm');
        const placeBtn = document.getElementById('placeOrderButton');
        const placeLabel = document.getElementById('placeOrderLabel');
        if (checkoutForm && placeBtn) {
            checkoutForm.addEventListener('submit', function(e){
                // Let the form submit normally, but disable the button to avoid double submits
                placeBtn.disabled = true;
                placeLabel.textContent = 'Processing...';
            });
        }
    })();
</script>
@endpush
