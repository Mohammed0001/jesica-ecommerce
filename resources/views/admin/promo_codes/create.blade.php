@extends('layouts.admin')

@section('title', 'Create Promo Code')

@section('content')
<div class="container-fluid">
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title">Create Promo Code</h1>
        <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.promo-codes.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed</option>
                        <option value="bogo">Buy X Get Y (value = % off the cheapest items, 100 = free)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Value</label>
                    <input type="number" step="0.01" name="value" class="form-control" required>
                </div>
                <div class="mb-3" id="bogo-fields" style="display:none">
                    <label class="form-label">Buy / Get quantities</label>
                    <div class="d-flex align-items-center gap-2">
                        <span>Buy</span>
                        <input type="number" min="1" max="20" name="buy_quantity" class="form-control" style="width:90px" value="1">
                        <span>Get</span>
                        <input type="number" min="1" max="20" name="get_quantity" class="form-control" style="width:90px" value="1">
                    </div>
                    <div class="form-text">e.g. Buy 2 Get 1: in every 3 items, the cheapest is discounted by the Value % (100 = free).</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Max Uses</label>
                    <input type="number" name="max_uses" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Affiliate (owner)</label>
                    <select name="owner_user_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($affiliates as $affiliate)
                            <option value="{{ $affiliate->id }}" {{ old('owner_user_id') == $affiliate->id ? 'selected' : '' }}>
                                {{ $affiliate->name }} ({{ $affiliate->email }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">If set, this affiliate can see orders placed with this code in their dashboard.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Expires At</label>
                    <input type="date" name="expires_at" class="form-control">
                </div>
                <div class="mb-3 form-check">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" class="form-check-input" id="active"
                        {{ old('active', true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="active">Active</label>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary">Create Promo</button>
                    <a href="{{ route('admin.promo-codes.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
(function () {
    var type = document.querySelector('select[name="type"]');
    var box = document.getElementById('bogo-fields');
    function sync() { box.style.display = type.value === 'bogo' ? '' : 'none'; }
    type.addEventListener('change', sync);
    sync();
})();
</script>
@endpush
@endsection
