@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Checkout</h1>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Error:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                <div class="d-flex justify-content-between mb-2"><span>Shipping</span><span id="shipping-display">{{ $shipping <= 0 ? 'Free' : (config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP')) . ' ' . number_format($shipping, 2) }}</span></div>
                <div class="d-flex justify-content-between mb-2"><span>Tax (est.)</span><span id="tax-display">{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($tax ?? 0, 2) }}</span></div>
                <hr />
                <div class="d-flex justify-content-between mb-0"><strong>Total</strong><strong id="total-display">{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($total, 2) }}</strong></div>
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
                            <label for="city">City <span class="text-danger">*</span></label>
                            <select name="city" id="city" class="form-control" required>
                                <option value="">Select City</option>
                                @foreach($bostaCities as $bostaCity)
                                    <option value="{{ $bostaCity->name }}" {{ old('city') == $bostaCity->name ? 'selected' : '' }}>
                                        {{ $bostaCity->name }} ({{ $bostaCity->name_ar }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="state_province">District / Zone <span class="text-danger">*</span></label>
                            <input type="text" name="state_province" id="state_province" class="form-control" value="{{ old('state_province') }}" required placeholder="e.g., Nasr City, Heliopolis, Maadi">
                            <small class="text-muted">Enter the district or zone within the city</small>
                        </div>
                        <div class="col-md-4 mb-2">
                            <label for="postal_code">Postal code <span class="text-danger">*</span></label>
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
                    {{-- <div class="form-check mt-2 mb-3">
                        <input class="form-check-input" type="radio" name="shipping_address_id" id="use_new_address" value="" {{ old('shipping_address_id') === null ? '' : '' }}>
                        <label class="form-check-label" for="use_new_address">Use a different address</label>
                    </div> --}}

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
                                <select name="city" id="city" class="form-control">
                                    <option value="">Select City</option>
                                    @foreach($bostaCities as $bostaCity)
                                        <option value="{{ $bostaCity->name }}" {{ old('city') == $bostaCity->name ? 'selected' : '' }}>
                                            {{ $bostaCity->name }} ({{ $bostaCity->name_ar }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="state_province">District / Zone</label>
                                <input type="text" name="state_province" id="state_province" class="form-control" value="{{ old('state_province') }}" placeholder="e.g., Nasr City, Heliopolis, Maadi">
                                <small class="text-muted">Enter the district or zone within the city</small>
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
                    {{-- <option value="deposit" {{ old('payment_type') === 'deposit' ? 'selected' : '' }}>Deposit</option> --}}
                </select>
            </div>

            <div class="mb-3">
                <label for="payment_method">Payment Method</label>
                <select name="payment_method" id="payment_method" class="form-select" required>
                    <option value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
                    {{-- <option value="mock_gateway" disabled>Mock (test) - Coming Soon</option> --}}
                    @if(config('services.paymob.api_key') || env('PAYMENT_PROVIDER') === 'paymob')
                        <option value="paymob" disabled>Paymob (card) - Coming Soon</option>
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
        const currencySymbol = '{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }}';
        const feeConfig = @json($deliveryFeeData);   // { threshold, taxPercentage, servicePct }
        const displaySubtotal = {{ json_encode($displaySubtotal) }};
        const discountAmount  = {{ json_encode($discountAmount ?? 0) }};
        const initialDeposit  = {{ json_encode($depositAmount) }};

        // Current live values — updated when city changes
        let currentShipping = {{ json_encode($shipping) }};
        let currentTotal    = {{ json_encode($total) }};
        let currentDeposit  = {{ json_encode($depositAmount) }};

        const shippingEl       = document.getElementById('shipping-display');
        const taxEl            = document.getElementById('tax-display');
        const totalEl          = document.getElementById('total-display');
        const chargeAmountEl   = document.getElementById('charge-amount');
        const depositAmountEl  = document.getElementById('deposit-amount');
        const paymentTypeEl    = document.getElementById('payment_type');

        function recalcTotals(shippingFee) {
            const finalAfterDiscount = Math.max(0, displaySubtotal - discountAmount);
            const freeThreshold = feeConfig.threshold;
            const shipping = finalAfterDiscount >= freeThreshold ? 0 : shippingFee;

            const serviceFee = Math.round(finalAfterDiscount * (feeConfig.servicePct / 100) * 100) / 100;
            const tax = Math.round((finalAfterDiscount + serviceFee + shipping) * (feeConfig.taxPercentage / 100) * 100) / 100;
            const total = Math.max(0, finalAfterDiscount + serviceFee + shipping + tax);

            currentShipping = shipping;
            currentTotal    = total;

            if (shippingEl) {
                shippingEl.textContent = shipping <= 0 ? 'Free' : currencySymbol + ' ' + shipping.toFixed(2);
            }
            if (taxEl) {
                taxEl.textContent = currencySymbol + ' ' + tax.toFixed(2);
            }
            if (totalEl) {
                totalEl.textContent = currencySymbol + ' ' + total.toFixed(2);
            }
            updateCharge();
        }

        function updateCharge() {
            if (!chargeAmountEl) return;
            const v = paymentTypeEl ? paymentTypeEl.value : 'full';
            if (v === 'full') {
                chargeAmountEl.textContent = currencySymbol + ' ' + currentTotal.toFixed(2);
            } else {
                chargeAmountEl.textContent = currencySymbol + ' ' + currentDeposit.toFixed(2);
            }
        }

        if (paymentTypeEl) {
            paymentTypeEl.addEventListener('change', updateCharge);
            updateCharge();
        }

        // --- Live city fee lookup ---
        let feeXhr = null;
        function onCityChange(cityValue) {
            if (feeXhr) feeXhr.abort();
            if (!cityValue) return;

            feeXhr = new XMLHttpRequest();
            feeXhr.open('GET', '/checkout/delivery-fee?city=' + encodeURIComponent(cityValue), true);
            feeXhr.onload = function() {
                if (this.status === 200) {
                    const data = JSON.parse(this.responseText);
                    recalcTotals(data.fee);
                }
            };
            feeXhr.send();
        }

        // Hook into all city selects on the page
        document.querySelectorAll('select[name="city"]').forEach(function(select) {
            select.addEventListener('change', function() {
                onCityChange(this.value);
            });
            // Trigger on load if a city is already selected
            if (select.value) onCityChange(select.value);
        });

        // Toggle new-address fields when user selects 'Use a different address'
        const addressRadios = document.querySelectorAll('input[name="shipping_address_id"]');
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
            toggleNewAddressBlock();
        }

        // Prevent multiple submits and provide feedback
        const checkoutForm = document.getElementById('checkoutForm');
        const placeBtn = document.getElementById('placeOrderButton');
        const placeLabel = document.getElementById('placeOrderLabel');
        if (checkoutForm && placeBtn) {
            checkoutForm.addEventListener('submit', function(e){
                const paymentMethod = document.getElementById('payment_method');
                const paymentType = document.getElementById('payment_type');

                if (!paymentMethod || !paymentMethod.value) {
                    e.preventDefault();
                    showNotification('Please select a payment method', 'error');
                    return false;
                }

                if (!paymentType || !paymentType.value) {
                    e.preventDefault();
                    showNotification('Please select a payment type', 'error');
                    return false;
                }

                placeBtn.disabled = true;
                placeLabel.textContent = 'Processing...';
            });
        }

        function showNotification(message, type = 'info') {
            const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
            const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
            const notification = document.createElement('div');
            notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 80px; right: 20px; z-index: 9999; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);';
            notification.innerHTML = `
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }

        window.showNotification = showNotification;
    })();
</script>
@endpush
