{{--
    Sale pricing + colour repeater, shared by the create and edit forms.

    Expects:
      $product  (optional) the product being edited
--}}
@php
    $editing = isset($product);

    // Repopulate from old input after a validation failure, otherwise from the
    // saved colours, otherwise a single blank row to type into.
    $colorRows = old('colors');

    if ($colorRows === null) {
        $colorRows = $editing
            ? $product->colors->map(fn ($color) => [
                'name' => $color->name,
                'hex_code' => $color->hex_code,
                'is_available' => $color->is_available ? 1 : 0,
            ])->values()->all()
            : [];
    }

    if (empty($colorRows)) {
        $colorRows = [['name' => '', 'hex_code' => '#000000', 'is_available' => 1]];
    }
@endphp

<div class="col-12 mb-4">
    <hr class="my-2">
    <h6 class="form-section-title">Sale</h6>
    <p class="form-text mb-3">
        Set a sale price to discount this product. Customers see the regular price struck through,
        a "% Off" badge on the cards, and pay the sale price at checkout. Leave the sale price
        empty to take the product off sale.
    </p>
</div>

<div class="col-md-4 mb-4">
    <label for="sale_price" class="form-label">Sale Price</label>
    <input type="number" step="0.01" min="0"
           class="form-control @error('sale_price') is-invalid @enderror"
           id="sale_price" name="sale_price"
           value="{{ old('sale_price', $editing ? $product->sale_price : null) }}"
           placeholder="Not on sale">
    @error('sale_price')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">Must be lower than the regular price.</div>
</div>

<div class="col-md-4 mb-4">
    <label for="sale_starts_at" class="form-label">Sale Starts</label>
    <input type="datetime-local"
           class="form-control @error('sale_starts_at') is-invalid @enderror"
           id="sale_starts_at" name="sale_starts_at"
           value="{{ old('sale_starts_at', $editing && $product->sale_starts_at ? $product->sale_starts_at->format('Y-m-d\TH:i') : null) }}">
    @error('sale_starts_at')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">Optional. Empty means the sale is live immediately.</div>
</div>

<div class="col-md-4 mb-4">
    <label for="sale_ends_at" class="form-label">Sale Ends</label>
    <input type="datetime-local"
           class="form-control @error('sale_ends_at') is-invalid @enderror"
           id="sale_ends_at" name="sale_ends_at"
           value="{{ old('sale_ends_at', $editing && $product->sale_ends_at ? $product->sale_ends_at->format('Y-m-d\TH:i') : null) }}">
    @error('sale_ends_at')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <div class="form-text">Optional. Empty means the sale runs until you remove it.</div>
</div>

<div class="col-12 mb-4">
    <hr class="my-2">
    <h6 class="form-section-title">Colours</h6>
    <p class="form-text mb-3">
        Add one row per colourway. Customers pick a colour on the product page and it is recorded
        on the order. Leave this empty for products that come in a single colour.
    </p>

    @error('colors')
        <div class="alert alert-danger py-2">{{ $message }}</div>
    @enderror

    <div id="colorRepeater">
        @foreach ($colorRows as $index => $row)
            <div class="color-row row g-2 align-items-center mb-2">
                <div class="col-md-5">
                    <input type="text" class="form-control color-name"
                           name="colors[{{ $index }}][name]"
                           value="{{ $row['name'] ?? '' }}"
                           placeholder="Colour name, e.g. Ivory" maxlength="60">
                </div>
                <div class="col-md-3 d-flex align-items-center gap-2">
                    <input type="color" class="form-control form-control-color color-hex-picker"
                           value="{{ $row['hex_code'] ?: '#000000' }}"
                           aria-label="Pick colour" title="Pick colour">
                    <input type="text" class="form-control color-hex"
                           name="colors[{{ $index }}][hex_code]"
                           value="{{ $row['hex_code'] ?? '' }}"
                           placeholder="#000000" maxlength="7">
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="colors[{{ $index }}][is_available]" value="0">
                        <input class="form-check-input" type="checkbox"
                               name="colors[{{ $index }}][is_available]" value="1"
                               id="color_available_{{ $index }}"
                               {{ ($row['is_available'] ?? 1) ? 'checked' : '' }}>
                        <label class="form-check-label" for="color_available_{{ $index }}">In stock</label>
                    </div>
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-color" aria-label="Remove colour">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" id="addColorRow">
        <i class="fas fa-plus me-1"></i>Add colour
    </button>
    <div class="form-text">Rows left blank are ignored. Removing a row removes that colour from the product.</div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const repeater = document.getElementById('colorRepeater');
        const addBtn = document.getElementById('addColorRow');
        if (!repeater || !addBtn) return;

        // Indices only need to be unique within the POST, not contiguous, so
        // the counter can just keep climbing as rows are added and removed.
        let nextIndex = repeater.querySelectorAll('.color-row').length;

        addBtn.addEventListener('click', function () {
            const i = nextIndex++;
            const row = document.createElement('div');
            row.className = 'color-row row g-2 align-items-center mb-2';
            row.innerHTML = `
                <div class="col-md-5">
                    <input type="text" class="form-control color-name" name="colors[${i}][name]"
                           placeholder="Colour name, e.g. Ivory" maxlength="60">
                </div>
                <div class="col-md-3 d-flex align-items-center gap-2">
                    <input type="color" class="form-control form-control-color color-hex-picker"
                           value="#000000" aria-label="Pick colour" title="Pick colour">
                    <input type="text" class="form-control color-hex" name="colors[${i}][hex_code]"
                           placeholder="#000000" maxlength="7">
                </div>
                <div class="col-md-3">
                    <div class="form-check">
                        <input type="hidden" name="colors[${i}][is_available]" value="0">
                        <input class="form-check-input" type="checkbox" name="colors[${i}][is_available]"
                               value="1" id="color_available_${i}" checked>
                        <label class="form-check-label" for="color_available_${i}">In stock</label>
                    </div>
                </div>
                <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-color" aria-label="Remove colour">
                        <i class="fas fa-times"></i>
                    </button>
                </div>`;
            repeater.appendChild(row);
            row.querySelector('.color-name').focus();
        });

        // Delegated so rows added after load behave the same as rendered ones.
        repeater.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('.remove-color');
            if (!removeBtn) return;

            const rows = repeater.querySelectorAll('.color-row');
            if (rows.length === 1) {
                // Keep one row on screen; clearing it is how you remove the last colour.
                const row = rows[0];
                row.querySelector('.color-name').value = '';
                row.querySelector('.color-hex').value = '';
                return;
            }
            removeBtn.closest('.color-row').remove();
        });

        // Keep the swatch picker and the text field in step.
        repeater.addEventListener('input', function (e) {
            const row = e.target.closest('.color-row');
            if (!row) return;

            if (e.target.classList.contains('color-hex-picker')) {
                row.querySelector('.color-hex').value = e.target.value;
            }

            if (e.target.classList.contains('color-hex') && /^#[0-9a-fA-F]{6}$/.test(e.target.value)) {
                row.querySelector('.color-hex-picker').value = e.target.value;
            }
        });
    });
</script>
@endpush
