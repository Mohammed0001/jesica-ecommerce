@extends('layouts.app')

@section('title', 'Checkout')

@push('styles')
<style>
/* ── Guest Checkout Layout ───────────────────────────────────── */
.guest-checkout {
    min-height: 100vh;
    background: #f5f3f0;
    font-family: 'futura-pt', sans-serif;
}

.guest-checkout__inner {
    display: grid;
    grid-template-columns: 1fr 420px;
    min-height: 100vh;
}

/* Left column – form */
.checkout-form-col {
    background: #fff;
    padding: 3rem 4rem 3rem 3rem;
    border-right: 1px solid #e8e4e0;
}

/* Right column – order summary */
.checkout-summary-col {
    background: #f5f3f0;
    padding: 3rem 3rem 3rem 2rem;
    position: sticky;
    top: 0;
    max-height: 100vh;
    overflow-y: auto;
}

/* Section headings */
.checkout-section-title {
    font-size: 1.375rem;
    font-weight: 300;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: #000;
    margin-bottom: 1.25rem;
    margin-top: 2rem;
}
.checkout-section-title:first-child { margin-top: 0; }

/* Sign-in link next to Contact */
.checkout-section-header {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 1.25rem;
}
.checkout-section-header .checkout-section-title { margin-bottom: 0; margin-top: 0; }
.checkout-signin-link {
    font-size: 0.875rem;
    font-weight: 300;
    color: #555;
    text-decoration: underline;
    letter-spacing: 0.02em;
}
.checkout-signin-link:hover { color: #000; }

/* Input fields */
.checkout-field {
    margin-bottom: 0.875rem;
}
.checkout-field label {
    display: block;
    font-size: 0.8rem;
    font-weight: 300;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: #888;
    margin-bottom: 0.35rem;
}
.checkout-input {
    width: 100%;
    padding: 0.875rem 1rem;
    border: 1px solid #d0ccc8;
    border-radius: 6px;
    font-family: 'futura-pt', sans-serif;
    font-size: 1rem;
    font-weight: 300;
    color: #000;
    background: #fff;
    transition: border-color 0.2s;
}
.checkout-input:focus {
    outline: none;
    border-color: #000;
}
.checkout-input::placeholder { color: #bbb; }
.checkout-input.is-invalid { border-color: #c00; }

.checkout-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
}

/* Checkbox row */
.checkout-check {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
    font-weight: 300;
    color: #333;
    cursor: pointer;
}
.checkout-check input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: #000;
    cursor: pointer;
    flex-shrink: 0;
}

/* Divider */
.checkout-divider {
    border: none;
    border-top: 1px solid #e8e4e0;
    margin: 1.75rem 0;
}

/* Shipping method option */
.shipping-option {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border: 1px solid #d0ccc8;
    border-radius: 6px;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: border-color 0.2s;
}
.shipping-option.selected,
.shipping-option:hover { border-color: #000; }
.shipping-option-label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.95rem;
    font-weight: 300;
}
.shipping-option-label input[type="radio"] {
    accent-color: #000;
    width: 16px;
    height: 16px;
}
.shipping-option-price {
    font-size: 0.95rem;
    font-weight: 300;
    color: #000;
}

/* Payment block */
.payment-block {
    border: 1px solid #d0ccc8;
    border-radius: 6px;
    overflow: hidden;
    margin-bottom: 1rem;
}
.payment-block-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    background: #f5f3f0;
    border-bottom: 1px solid #e8e4e0;
}
.payment-block-header label {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.95rem;
    font-weight: 300;
    cursor: pointer;
}
.payment-block-header input[type="radio"] {
    accent-color: #000;
    width: 16px;
    height: 16px;
}
.payment-card-icons {
    display: flex;
    gap: 0.4rem;
}
.payment-card-icon {
    height: 22px;
    width: auto;
}

/* Place order button */
.checkout-submit-btn {
    width: 100%;
    padding: 1rem;
    background: #000;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-family: 'futura-pt', sans-serif;
    font-size: 1rem;
    font-weight: 300;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background 0.2s, opacity 0.2s;
    margin-top: 1.5rem;
}
.checkout-submit-btn:hover { background: #222; }
.checkout-submit-btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* ── Order Summary ─────────────────────────────────────── */
.summary-items {
    margin-bottom: 1.5rem;
}
.summary-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}
.summary-item-img-wrap {
    position: relative;
    flex-shrink: 0;
}
.summary-item-img {
    width: 64px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
    background: #e8e4e0;
    display: block;
}
.summary-item-qty-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #888;
    color: #fff;
    font-size: 0.7rem;
    font-weight: 400;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.summary-item-info { flex: 1; }
.summary-item-title {
    font-size: 0.9rem;
    font-weight: 300;
    color: #000;
    margin-bottom: 0.2rem;
}
.summary-item-size {
    font-size: 0.8rem;
    color: #888;
}
.summary-item-price {
    font-size: 0.9rem;
    font-weight: 300;
    color: #000;
    white-space: nowrap;
}

.summary-line {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    font-weight: 300;
    color: #444;
    margin-bottom: 0.6rem;
}
.summary-line.total-line {
    font-size: 1rem;
    font-weight: 400;
    color: #000;
    border-top: 1px solid #d0ccc8;
    padding-top: 0.75rem;
    margin-top: 0.5rem;
}
.summary-line .label { color: #888; }
.summary-line.total-line .label { color: #000; }

/* Error alert */
.checkout-alert {
    background: #fdf2f2;
    border: 1px solid #f0c0c0;
    border-radius: 6px;
    padding: 0.875rem 1rem;
    font-size: 0.875rem;
    font-weight: 300;
    color: #c00;
    margin-bottom: 1.25rem;
}
.checkout-alert ul { margin: 0.5rem 0 0 1rem; }

/* Responsive */
@media (max-width: 900px) {
    .guest-checkout__inner {
        grid-template-columns: 1fr;
    }
    .checkout-summary-col {
        position: static;
        max-height: none;
        order: -1;
        border-bottom: 1px solid #e8e4e0;
        padding: 2rem 1.5rem;
    }
    .checkout-form-col {
        padding: 2rem 1.5rem;
        border-right: none;
    }
}
@media (max-width: 520px) {
    .checkout-row { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
@php
    $currSym = config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP');
@endphp

<div class="guest-checkout">
    <div class="guest-checkout__inner">

        {{-- ══════════════ LEFT: FORM ══════════════ --}}
        <div class="checkout-form-col">

            {{-- Error messages --}}
            @if(session('error'))
                <div class="checkout-alert">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="checkout-alert">
                    <strong>Please fix the following:</strong>
                    <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form id="guestCheckoutForm" method="POST" action="{{ route('guest.checkout.process') }}">
                @csrf

                {{-- ── Contact ── --}}
                <div class="checkout-section-header">
                    <h2 class="checkout-section-title">Contact</h2>
                    @auth
                        <span class="checkout-signin-link">Signed in as {{ Auth::user()->email }}</span>
                    @else
                        <a href="{{ route('login') }}" class="checkout-signin-link">Sign in</a>
                    @endauth
                </div>

                <div class="checkout-field">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="checkout-input @error('email') is-invalid @enderror"
                           value="{{ old('email', auth()->user()?->email) }}"
                           placeholder="Email" required>
                </div>

                <label class="checkout-check">
                    <input type="checkbox" name="news_offers" value="1" {{ old('news_offers', '1') ? 'checked' : '' }}>
                    Email me with news and offers
                </label>

                <hr class="checkout-divider">

                {{-- ── Delivery ── --}}
                <h2 class="checkout-section-title">Delivery</h2>

                {{-- Country --}}
                <div class="checkout-field">
                    <label for="checkout_country">Country/Region</label>
                    <select id="checkout_country" class="checkout-input">
                        <option value="Egypt" {{ (old('country', 'Egypt') !== 'International') ? 'selected' : '' }}>Egypt</option>
                        <option value="International" {{ old('country') === 'International' ? 'selected' : '' }}>International (outside Egypt)</option>
                    </select>
                </div>

                {{-- Hidden submitted fields --}}
                <input type="hidden" name="country"        id="hidden_country"   value="{{ old('country', 'Egypt') }}">
                <input type="hidden" name="city"           id="hidden_city"      value="{{ old('city', '') }}">
                <input type="hidden" name="state_province" id="hidden_district"  value="{{ old('state_province', '') }}">

                {{-- Name row --}}
                <div class="checkout-row">
                    <div class="checkout-field">
                        <label for="first_name">First name</label>
                        <input type="text" name="first_name" id="first_name"
                               class="checkout-input @error('first_name') is-invalid @enderror"
                               value="{{ old('first_name') }}" placeholder="First name" required>
                    </div>
                    <div class="checkout-field">
                        <label for="last_name">Last name</label>
                        <input type="text" name="last_name" id="last_name"
                               class="checkout-input @error('last_name') is-invalid @enderror"
                               value="{{ old('last_name') }}" placeholder="Last name" required>
                    </div>
                </div>

                {{-- Address --}}
                <div class="checkout-field">
                    <label for="address_line_1">Address</label>
                    <input type="text" name="address_line_1" id="address_line_1"
                           class="checkout-input @error('address_line_1') is-invalid @enderror"
                           value="{{ old('address_line_1') }}" placeholder="Address" required>
                </div>

                <div class="checkout-field">
                    <label for="address_line_2">Apartment, suite, etc. (optional)</label>
                    <input type="text" name="address_line_2" id="address_line_2"
                           class="checkout-input"
                           value="{{ old('address_line_2') }}" placeholder="Apartment, suite, etc. (optional)">
                </div>

                {{-- City + Governorate row --}}
                <div class="checkout-row">
                    <div class="checkout-field">
                        <label for="city_input">City</label>
                        <input type="text" id="city_input"
                               class="checkout-input @error('city') is-invalid @enderror"
                               value="{{ old('city') }}" placeholder="City">
                    </div>

                    {{-- Governorate (Egypt) / State (Intl) --}}
                    <div class="checkout-field" id="govWrapper">
                        <label for="checkout_governorate">Governorate</label>
                        <select id="checkout_governorate" class="checkout-input">
                            <option value="">— Select —</option>
                            <option value="Cairo" {{ strtolower(old('state_province','')) === 'cairo' ? 'selected' : '' }}>Cairo</option>
                            @foreach($governorates as $gov)
                                @php $govName = $gov->city_names[0] ?? $gov->name; @endphp
                                <option value="{{ $govName }}"
                                    {{ strtolower(old('state_province','')) === strtolower($govName) ? 'selected' : '' }}>
                                    {{ $gov->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="checkout-field" id="intlStateWrapper" style="display:none;">
                        <label for="intl_state">State / Province</label>
                        <input type="text" id="intl_state" class="checkout-input"
                               placeholder="State / Province" value="{{ old('state_province') }}">
                    </div>
                </div>

                {{-- Cairo district combobox (shown only when Cairo selected) --}}
                <div class="checkout-field" id="cairoAreaWrapper" style="display:none;">
                    <label for="checkout_cairo_area">Cairo Area</label>

                    <datalist id="cairo_area_options">
                        @foreach($cairoDistricts as $district)
                            @php $dName = $district->city_names[0] ?? $district->name; @endphp
                            <option value="{{ $dName }}" data-fee="{{ $district->delivery_fee }}">
                                {{ $district->name }}
                                @if($district->delivery_fee > 0)({{ number_format($district->delivery_fee, 0) }} EGP)@endif
                            </option>
                        @endforeach
                    </datalist>

                    <input type="text"
                           id="checkout_cairo_area"
                           list="cairo_area_options"
                           class="checkout-input"
                           placeholder="Type to search area…"
                           value="{{ old('state_province', '') }}"
                           autocomplete="off">

                    <small style="font-size:0.8rem;color:#888;">Type or pick your area. Fee updates automatically.</small>
                </div>

                {{-- Postal + Phone row --}}
                <div class="checkout-row">
                    <div class="checkout-field">
                        <label for="postal_code">Postal code (optional)</label>
                        <input type="text" name="postal_code" id="postal_code"
                               class="checkout-input"
                               value="{{ old('postal_code') }}" placeholder="Postal code (optional)">
                    </div>
                    <div class="checkout-field">
                        <label for="phone">Phone</label>
                        <input type="tel" name="phone" id="phone"
                               class="checkout-input @error('phone') is-invalid @enderror"
                               value="{{ old('phone') }}" placeholder="Phone" required>
                    </div>
                </div>

                <label class="checkout-check">
                    <input type="checkbox" name="save_info" value="1">
                    Save this information for next time
                </label>

                <hr class="checkout-divider">

                {{-- ── Shipping Method ── --}}
                <h2 class="checkout-section-title">Shipping method</h2>

                <div class="shipping-option selected">
                    <label class="shipping-option-label">
                        <input type="radio" name="shipping_method_display" checked readonly> DOMESTIC
                    </label>
                    <span class="shipping-option-price" id="shipping-option-display">
                        @if($shipping <= 0)
                            Free
                        @else
                            {{ $currSym }} {{ number_format($shipping, 2) }}
                        @endif
                    </span>
                </div>

                <hr class="checkout-divider">

                {{-- ── Payment ── --}}
                <h2 class="checkout-section-title">Payment</h2>
                <p style="font-size:0.85rem;font-weight:300;color:#888;margin-bottom:1rem;letter-spacing:0.02em;">
                    All transactions are secure and encrypted.
                </p>

                <div class="payment-block">
                    <div class="payment-block-header">
                        <label>
                            <input type="radio" name="payment_method" value="cod" checked>
                            Cash on Delivery (COD)
                        </label>
                        <div class="payment-card-icons">
                            {{-- COD icon placeholder --}}
                            <span style="font-size:0.8rem;color:#888;font-weight:300;">COD</span>
                        </div>
                    </div>
                    @if(config('services.paymob.api_key') || env('PAYMENT_PROVIDER') === 'paymob')
                    <div class="payment-block-header" style="border-top:1px solid #e8e4e0;">
                        <label>
                            <input type="radio" name="payment_method" value="paymob">
                            Credit card
                        </label>
                        <div class="payment-card-icons">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/800px-Mastercard-logo.svg.png"
                                 alt="Mastercard" class="payment-card-icon">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/800px-Visa_Inc._logo.svg.png"
                                 alt="Visa" class="payment-card-icon">
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Payment type (hidden — default full) --}}
                <input type="hidden" name="payment_type" value="full">

                {{-- Submit --}}
                <button type="submit" id="placeOrderBtn" class="checkout-submit-btn">
                    <span id="placeOrderLabel">Pay now</span>
                </button>

            </form>
        </div>

        {{-- ══════════════ RIGHT: ORDER SUMMARY ══════════════ --}}
        <div class="checkout-summary-col">

            {{-- Items --}}
            <div class="summary-items">
                @foreach($cartItems as $item)
                <div class="summary-item">
                    <div class="summary-item-img-wrap">
                        @if(!empty($item['product']['main_image_url']))
                            <img src="{{ $item['product']['main_image_url'] }}"
                                 alt="{{ $item['product']['title'] }}"
                                 class="summary-item-img">
                        @else
                            <div class="summary-item-img" style="background:#e8e4e0;"></div>
                        @endif
                        <span class="summary-item-qty-badge">{{ $item['quantity'] }}</span>
                    </div>
                    <div class="summary-item-info">
                        <div class="summary-item-title">{{ $item['product']['title'] ?? 'Product' }}</div>
                        @if(!empty($item['size_label']))
                            <div class="summary-item-size">Size: {{ $item['size_label'] }}</div>
                        @endif
                    </div>
                    <div class="summary-item-price">
                        {{ $currSym }} {{ number_format($item['product']['display_subtotal'] ?? 0, 2) }}
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Totals --}}
            <div class="summary-line">
                <span class="label">Subtotal</span>
                <span>{{ $currSym }} {{ number_format($displaySubtotal, 2) }}</span>
            </div>
            @if(($discountAmount ?? 0) > 0)
            <div class="summary-line">
                <span class="label">Discount</span>
                <span>− {{ $currSym }} {{ number_format($discountAmount, 2) }}</span>
            </div>
            @endif
            @if(($serviceFee ?? 0) > 0)
            <div class="summary-line">
                <span class="label">Service fee</span>
                <span>{{ $currSym }} {{ number_format($serviceFee, 2) }}</span>
            </div>
            @endif
            <div class="summary-line">
                <span class="label">Shipping</span>
                <span id="summary-shipping">
                    {{ $shipping <= 0 ? 'Free' : $currSym . ' ' . number_format($shipping, 2) }}
                </span>
            </div>
            @if(($tax ?? 0) > 0)
            <div class="summary-line">
                <span class="label">Tax (est.)</span>
                <span id="summary-tax">{{ $currSym }} {{ number_format($tax, 2) }}</span>
            </div>
            @endif
            <div class="summary-line total-line">
                <span class="label">Total</span>
                <span id="summary-total"><small style="font-size:0.75rem;opacity:.6;">{{ session('currency', 'EGP') }}</small> {{ $currSym }} {{ number_format($total, 2) }}</span>
            </div>
        </div>

    </div>{{-- /.guest-checkout__inner --}}
</div>{{-- /.guest-checkout --}}
@endsection

@push('scripts')
<script>
(function () {
    const currSym     = @json($currSym);
    const feeConfig   = @json($deliveryFeeData);
    const subTotal    = {{ json_encode($displaySubtotal) }};
    const discount    = {{ json_encode($discountAmount ?? 0) }};
    const servicePct  = feeConfig.servicePct;

    // ── DOM refs ──
    const countryEl       = document.getElementById('checkout_country');
    const govWrapper      = document.getElementById('govWrapper');
    const govEl           = document.getElementById('checkout_governorate');
    const intlStateWrap   = document.getElementById('intlStateWrapper');
    const intlStateEl     = document.getElementById('intl_state');
    const cairoAreaWrap   = document.getElementById('cairoAreaWrapper');
    const cairoAreaEl     = document.getElementById('checkout_cairo_area');
    const cityInputEl     = document.getElementById('city_input');

    const hiddenCountry   = document.getElementById('hidden_country');
    const hiddenCity      = document.getElementById('hidden_city');
    const hiddenDistrict  = document.getElementById('hidden_district');

    const summaryShipping = document.getElementById('summary-shipping');
    const summaryTax      = document.getElementById('summary-tax');
    const summaryTotal    = document.getElementById('summary-total');
    const shippingOption  = document.getElementById('shipping-option-display');

    // ── Sync hidden fields ──
    function syncHiddens() {
        const country = countryEl.value;
        hiddenCountry.value = country;

        if (country === 'International') {
            hiddenCity.value     = cityInputEl ? cityInputEl.value : '';
            hiddenDistrict.value = intlStateEl ? intlStateEl.value : '';
        } else {
            const gov = govEl.value;
            if (gov === 'Cairo') {
                hiddenCity.value     = 'Cairo';
                hiddenDistrict.value = cairoAreaEl.value;
            } else {
                hiddenCity.value     = gov;
                hiddenDistrict.value = '';
            }
        }
    }

    // ── Recalculate totals ──
    function recalcTotals(shippingFee) {
        const after   = Math.max(0, subTotal - discount);
        const free    = after >= feeConfig.threshold;
        const ship    = free ? 0 : shippingFee;
        const svc     = Math.round(after * (servicePct / 100) * 100) / 100;
        const tax     = Math.round((after + svc + ship) * (feeConfig.taxPercentage / 100) * 100) / 100;
        const total   = Math.max(0, after + svc + ship + tax);

        const shipText = ship <= 0 ? 'Free' : currSym + ' ' + ship.toFixed(2);
        if (summaryShipping)   summaryShipping.textContent = shipText;
        if (shippingOption)    shippingOption.textContent  = shipText;
        if (summaryTax)        summaryTax.textContent      = currSym + ' ' + tax.toFixed(2);
        if (summaryTotal) {
            summaryTotal.innerHTML = '<small style="font-size:.75rem;opacity:.6;">{{ session('currency', 'EGP') }}</small> ' + currSym + ' ' + total.toFixed(2);
        }
    }

    // ── Fetch delivery fee from server ──
    let feeTimer = null;
    function fetchFee() {
        syncHiddens();
        clearTimeout(feeTimer);
        feeTimer = setTimeout(function () {
            const city     = hiddenCity.value;
            const district = hiddenDistrict.value;
            const country  = hiddenCountry.value;
            const url = '/checkout/guest/delivery-fee'
                + '?city='     + encodeURIComponent(city)
                + '&district=' + encodeURIComponent(district)
                + '&country='  + encodeURIComponent(country);
            fetch(url)
                .then(r => r.json())
                .then(d => recalcTotals(d.fee))
                .catch(() => {});
        }, 300);
    }

    // ── Country change ──
    function onCountryChange() {
        const isEgypt = countryEl.value === 'Egypt';
        govWrapper.style.display     = isEgypt ? '' : 'none';
        intlStateWrap.style.display  = isEgypt ? 'none' : '';
        if (!isEgypt) cairoAreaWrap.style.display = 'none';
        else onGovChange();
        fetchFee();
    }

    // ── Governorate change ──
    function onGovChange() {
        const isCairo = govEl.value === 'Cairo';
        cairoAreaWrap.style.display = isCairo ? '' : 'none';
        fetchFee();
    }

    countryEl.addEventListener('change', onCountryChange);
    govEl.addEventListener('change', onGovChange);
    cairoAreaEl.addEventListener('change', fetchFee);
    cairoAreaEl.addEventListener('input', fetchFee);
    if (intlStateEl) intlStateEl.addEventListener('input', fetchFee);
    if (cityInputEl) cityInputEl.addEventListener('input', function () {
        // For Egypt, city is derived from governorate, not free text; ignore
        if (countryEl.value === 'International') fetchFee();
    });

    // ── Init ──
    onCountryChange();

    // ── Prevent double submit ──
    const form    = document.getElementById('guestCheckoutForm');
    const btn     = document.getElementById('placeOrderBtn');
    const btnLbl  = document.getElementById('placeOrderLabel');
    if (form && btn) {
        form.addEventListener('submit', function () {
            btn.disabled    = true;
            btnLbl.textContent = 'Processing…';
        });
    }
})();
</script>
@endpush
