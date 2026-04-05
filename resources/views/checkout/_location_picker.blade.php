{{--
    Cascading location picker for checkout.
    Variables expected:
      $cairoDistricts  – Region::cairoDistricts()
      $governorates    – Region::governorates()
      $oldCountry      – previously selected country (default 'Egypt')
      $oldCity         – previously selected city / governorate
      $oldDistrict     – previously selected district (only for Cairo)
--}}

@php
    $isEgypt        = ($oldCountry ?? 'Egypt') !== 'International';
    $isCairo        = $isEgypt && strtolower($oldCity ?? '') === 'cairo';
    $locationPrefix = $prefix ?? 'new';
@endphp

{{-- Hidden fields submitted to the backend --}}
<input type="hidden" name="country"        id="hidden_country"  value="{{ $isEgypt ? 'Egypt' : 'International' }}">
<input type="hidden" name="city"           id="hidden_city"     value="{{ $oldCity ?? '' }}">
<input type="hidden" name="state_province" id="hidden_district" value="{{ $oldDistrict ?? '' }}">

<div class="row">

    {{-- ── Step 1: Country ── --}}
    <div class="col-md-4 mb-3">
        <label for="checkout_country" class="form-label fw-semibold">
            Country <span class="text-danger">*</span>
        </label>
        <select id="checkout_country" class="form-select">
            <option value="Egypt"         {{ $isEgypt ? 'selected' : '' }}>🇪🇬 Egypt</option>
            <option value="International" {{ !$isEgypt ? 'selected' : '' }}>🌍 International (outside Egypt)</option>
        </select>
    </div>

    {{-- ── Step 2: Governorate (Egypt only) ── --}}
    <div class="col-md-4 mb-3" id="govWrapper" style="{{ !$isEgypt ? 'display:none;' : '' }}">
        <label for="checkout_governorate" class="form-label fw-semibold">
            Governorate <span class="text-danger">*</span>
        </label>
        <select id="checkout_governorate" class="form-select">
            <option value="">— Select Governorate —</option>

            {{-- Cairo is always first --}}
            <option value="Cairo" {{ $isCairo ? 'selected' : '' }}>Cairo (القاهرة)</option>

            {{-- All other governorates from regions table --}}
            @foreach($governorates as $gov)
                @php
                    $govName = $gov->city_names[0] ?? $gov->name;
                @endphp
                <option value="{{ $govName }}"
                    {{ (!$isCairo && strtolower($oldCity ?? '') === strtolower($govName)) ? 'selected' : '' }}>
                    {{ $gov->name }}
                    @if($gov->delivery_fee > 0)
                        <span class="text-muted">({{ number_format($gov->delivery_fee, 0) }} EGP)</span>
                    @endif
                </option>
            @endforeach
        </select>
        <small class="text-muted">Select your governorate. Cairo has area-specific pricing.</small>
    </div>

    {{-- ── Step 3: Cairo Area (only when Cairo selected) ── --}}
    <div class="col-md-4 mb-3" id="cairoAreaWrapper" style="{{ ($isEgypt && $isCairo) ? '' : 'display:none;' }}">
        <label for="checkout_cairo_area" class="form-label fw-semibold">
            Cairo Area <span class="text-danger">*</span>
        </label>
        <select id="checkout_cairo_area" class="form-select">
            <option value="">— Select Area —</option>
            @foreach($cairoDistricts as $district)
                @php
                    $districtName = $district->city_names[0] ?? $district->name;
                @endphp
                <option value="{{ $districtName }}"
                    {{ strtolower($oldDistrict ?? '') === strtolower($districtName) ? 'selected' : '' }}>
                    {{ $district->name }}
                    @if($district->delivery_fee > 0)
                        — {{ number_format($district->delivery_fee, 0) }} EGP
                    @endif
                </option>
            @endforeach
        </select>
        <small class="text-muted">Pricing varies by Cairo area.</small>
    </div>

    {{-- Postal code --}}
    <div class="col-md-4 mb-3">
        <label for="postal_code" class="form-label fw-semibold">Postal Code</label>
        <input type="text" name="postal_code" id="postal_code" class="form-control"
               value="{{ old('postal_code') }}" placeholder="Optional">
    </div>

</div>

{{-- International notice --}}
<div id="intlNotice" class="alert alert-info py-2 mb-3" style="{{ !$isEgypt ? '' : 'display:none;' }}">
    <i class="fas fa-plane me-2"></i>
    <strong>International shipping</strong> — a unified flat delivery fee will apply.
    Actual cost is shown in the order summary above.
</div>

@once
@push('scripts')
<script>
(function () {
    const countryEl       = document.getElementById('checkout_country');
    const govWrapper      = document.getElementById('govWrapper');
    const govEl           = document.getElementById('checkout_governorate');
    const cairoAreaWrapper= document.getElementById('cairoAreaWrapper');
    const cairoAreaEl     = document.getElementById('checkout_cairo_area');
    const intlNotice      = document.getElementById('intlNotice');
    const hiddenCity      = document.getElementById('hidden_city');
    const hiddenDistrict  = document.getElementById('hidden_district');
    const hiddenCountry   = document.getElementById('hidden_country');

    function syncHiddens() {
        const country = countryEl.value;
        hiddenCountry.value = country;

        if (country === 'International') {
            hiddenCity.value     = '';
            hiddenDistrict.value = '';
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

    function onCountryChange() {
        const isEgypt = countryEl.value === 'Egypt';
        govWrapper.style.display       = isEgypt ? '' : 'none';
        intlNotice.style.display       = isEgypt ? 'none' : '';
        // If not Egypt, also hide cairo area
        if (!isEgypt) cairoAreaWrapper.style.display = 'none';
        else onGovChange();
        syncHiddens();
        // Trigger fee update if picker JS is loaded
        if (typeof onPickerChange === 'function') onPickerChange();
    }

    function onGovChange() {
        const isCairo = govEl.value === 'Cairo';
        cairoAreaWrapper.style.display = isCairo ? '' : 'none';
        syncHiddens();
        if (typeof onPickerChange === 'function') onPickerChange();
    }

    function onCairoAreaChange() {
        syncHiddens();
        if (typeof onPickerChange === 'function') onPickerChange();
    }

    countryEl.addEventListener('change', onCountryChange);
    govEl.addEventListener('change', onGovChange);
    cairoAreaEl.addEventListener('change', onCairoAreaChange);

    // Init
    syncHiddens();
})();
</script>
@endpush
@endonce
