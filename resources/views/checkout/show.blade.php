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
                            <img src="{{ $item['product']['main_image_url'] }}" alt="{{ $item['product']['title'] }}" class="img-fluid" style="max-height:100px;object-fit:contain;background:#f8f9fa;">
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
                    {{-- ═══════════ NO SAVED ADDRESSES ═══════════ --}}
                    <p><strong>No saved addresses.</strong> Please provide a shipping address below.</p>

                    <div class="mb-2">
                        <label for="company">Company (optional)</label>
                        <input type="text" name="company" id="company" class="form-control" value="{{ old('company') }}">
                    </div>
                    <div class="mb-2">
                        <label for="address_line_1">Address line 1 <span class="text-danger">*</span></label>
                        <input type="text" name="address_line_1" id="address_line_1" class="form-control" value="{{ old('address_line_1') }}" required>
                    </div>
                    <div class="mb-2">
                        <label for="address_line_2">Address line 2 (optional)</label>
                        <input type="text" name="address_line_2" id="address_line_2" class="form-control" value="{{ old('address_line_2') }}">
                    </div>

                    {{-- Smart cascading location picker --}}
                    @include('checkout._location_picker', [
                        'prefix'         => 'new',
                        'cairoDistricts' => $cairoDistricts,
                        'governorates'   => $governorates,
                        'oldCountry'     => old('country', 'Egypt'),
                        'oldCity'        => old('city', ''),
                        'oldDistrict'    => old('state_province', ''),
                    ])

                    <div class="form-check mb-2">
                        <input type="checkbox" name="save_address" id="save_address" class="form-check-input" {{ old('save_address') ? 'checked' : '' }}>
                        <label for="save_address" class="form-check-label">Save this address to my profile</label>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" name="is_default" id="is_default" class="form-check-input" {{ old('is_default') ? 'checked' : '' }}>
                        <label for="is_default" class="form-check-label">Set as my default shipping address</label>
                    </div>

                @else
                    {{-- ═══════════ SAVED ADDRESSES ═══════════ --}}
                    @foreach($addresses as $address)
                        <div class="form-check mb-2">
                            <input class="form-check-input saved-addr-radio" type="radio"
                                   name="shipping_address_id"
                                   id="addr-{{ $address->id }}"
                                   value="{{ $address->id }}"
                                   data-city="{{ $address->city }}"
                                   data-district="{{ $address->state_province }}"
                                   data-country="{{ $address->country }}"
                                   {{ $loop->first ? 'checked' : '' }}>
                            <label class="form-check-label" for="addr-{{ $address->id }}">
                                {{ $address->formatted_address ?? $address->address_line_1 }}
                                @if($address->city)
                                    — <em>{{ $address->city }}{{ $address->state_province ? ', ' . $address->state_province : '' }}, {{ $address->country }}</em>
                                @endif
                            </label>
                        </div>
                    @endforeach
                @endif
            </div>

            <div class="mb-3">
                <label for="payment_type">Payment Type</label>
                <select name="payment_type" id="payment_type" class="form-select">
                    <option value="full" {{ old('payment_type') === 'full' ? 'selected' : '' }}>Full</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="payment_method">Payment Method</label>
                <select name="payment_method" id="payment_method" class="form-select" required>
                    <option value="cod" {{ old('payment_method', 'cod') === 'cod' ? 'selected' : '' }}>Cash on Delivery (COD)</option>
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
        const feeConfig = @json($deliveryFeeData);
        const displaySubtotal = {{ json_encode($displaySubtotal) }};
        const discountAmount  = {{ json_encode($discountAmount ?? 0) }};

        let currentShipping = {{ json_encode($shipping) }};
        let currentTotal    = {{ json_encode($total) }};
        let currentDeposit  = {{ json_encode($depositAmount) }};

        const shippingEl      = document.getElementById('shipping-display');
        const taxEl           = document.getElementById('tax-display');
        const totalEl         = document.getElementById('total-display');
        const chargeAmountEl  = document.getElementById('charge-amount');
        const depositAmountEl = document.getElementById('deposit-amount');
        const paymentTypeEl   = document.getElementById('payment_type');

        function recalcTotals(shippingFee) {
            const finalAfterDiscount = Math.max(0, displaySubtotal - discountAmount);
            const freeThreshold = feeConfig.threshold;
            const shipping = finalAfterDiscount >= freeThreshold ? 0 : shippingFee;
            const serviceFee = Math.round(finalAfterDiscount * (feeConfig.servicePct / 100) * 100) / 100;
            const tax = Math.round((finalAfterDiscount + serviceFee + shipping) * (feeConfig.taxPercentage / 100) * 100) / 100;
            const total = Math.max(0, finalAfterDiscount + serviceFee + shipping + tax);
            currentShipping = shipping;
            currentTotal = total;
            if (shippingEl) shippingEl.textContent = shipping <= 0 ? 'Free' : currencySymbol + ' ' + shipping.toFixed(2);
            if (taxEl) taxEl.textContent = currencySymbol + ' ' + tax.toFixed(2);
            if (totalEl) totalEl.textContent = currencySymbol + ' ' + total.toFixed(2);
            updateCharge();
        }

        function updateCharge() {
            if (!chargeAmountEl) return;
            const v = paymentTypeEl ? paymentTypeEl.value : 'full';
            chargeAmountEl.textContent = currencySymbol + ' ' + (v === 'full' ? currentTotal : currentDeposit).toFixed(2);
        }

        if (paymentTypeEl) { paymentTypeEl.addEventListener('change', updateCharge); updateCharge(); }

        // ─── Live delivery fee lookup ───
        let feeTimer = null;
        function fetchDeliveryFee(city, district, country) {
            clearTimeout(feeTimer);
            feeTimer = setTimeout(function() {
                const url = '/checkout/delivery-fee'
                    + '?city='     + encodeURIComponent(city     || '')
                    + '&district=' + encodeURIComponent(district || '')
                    + '&country='  + encodeURIComponent(country  || 'Egypt');
                fetch(url)
                    .then(r => r.json())
                    .then(data => recalcTotals(data.fee))
                    .catch(() => {});
            }, 250);
        }

        // ─── Read location from the cascading picker ───
        function getPickerLocation() {
            const countryEl  = document.getElementById('checkout_country');
            const govEl      = document.getElementById('checkout_governorate');
            const cairoEl    = document.getElementById('checkout_cairo_area');

            if (!countryEl) return { city: '', district: '', country: 'Egypt' };

            const country = countryEl.value;
            if (country === 'International') {
                return { city: '', district: '', country: 'International' };
            }
            // Egypt selected
            const gov = govEl ? govEl.value : '';
            if (gov === 'Cairo') {
                const area = cairoEl ? cairoEl.value : '';
                return { city: 'Cairo', district: area, country: 'Egypt' };
            }
            return { city: gov, district: '', country: 'Egypt' };
        }

        function onPickerChange() {
            const loc = getPickerLocation();
            // Sync hidden form fields
            const hiddenCity     = document.getElementById('hidden_city');
            const hiddenDistrict = document.getElementById('hidden_district');
            const hiddenCountry  = document.getElementById('hidden_country');
            if (hiddenCity)     hiddenCity.value     = loc.city;
            if (hiddenDistrict) hiddenDistrict.value = loc.district;
            if (hiddenCountry)  hiddenCountry.value  = loc.country;
            fetchDeliveryFee(loc.city, loc.district, loc.country);
        }

        // Hook picker selects
        ['checkout_country', 'checkout_governorate', 'checkout_cairo_area'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', onPickerChange);
        });

        // Hook saved address radios
        document.querySelectorAll('.saved-addr-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                const city     = this.dataset.city     || '';
                const district = this.dataset.district || '';
                const country  = this.dataset.country  || 'Egypt';
                fetchDeliveryFee(city, district, country);
            });
        });

        // Trigger on load
        const firstRadio = document.querySelector('.saved-addr-radio:checked');
        if (firstRadio) {
            fetchDeliveryFee(firstRadio.dataset.city || '', firstRadio.dataset.district || '', firstRadio.dataset.country || 'Egypt');
        } else {
            onPickerChange();
        }

        // Prevent double-submit
        const checkoutForm = document.getElementById('checkoutForm');
        const placeBtn = document.getElementById('placeOrderButton');
        const placeLabel = document.getElementById('placeOrderLabel');
        if (checkoutForm && placeBtn) {
            checkoutForm.addEventListener('submit', function(e) {
                const pm = document.getElementById('payment_method');
                if (!pm || !pm.value) {
                    e.preventDefault();
                    alert('Please select a payment method');
                    return false;
                }
                placeBtn.disabled = true;
                placeLabel.textContent = 'Processing...';
            });
        }
    })();
</script>
@endpush
