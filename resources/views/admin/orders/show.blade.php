@extends('layouts.admin')

@section('title', 'Order Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h3>Order #{{ $order->order_number }}</h3>
            <div class="text-muted">Placed: {{ $order->created_at->format('Y-m-d H:i') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="mailto:{{ $order->user->email ?? '' }}" class="btn btn-outline-secondary">Email Customer</a>
            <a href="{{ route('admin.orders.print', $order) }}" target="_blank" class="btn btn-outline-info">Print</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-body">
                    <h6>Customer</h6>
                    <p class="mb-1"><strong>{{ $order->user->name ?? 'Guest' }}</strong></p>
                    <p class="mb-0">{{ $order->user->email ?? '' }}</p>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h6>Shipping Address</h6>
                    @if($order->shipping_address_snapshot)
                        @php $a = $order->shipping_address_snapshot; @endphp
                        <div>{{ $a['company'] ?? '' }}</div>
                        <div>{{ $a['address_line_1'] ?? '' }} {{ $a['address_line_2'] ?? '' }}</div>
                        <div>{{ $a['city'] ?? '' }} {{ $a['state_province'] ?? '' }} {{ $a['postal_code'] ?? '' }}</div>
                        <div>{{ $a['country'] ?? '' }}</div>
                    @else
                        <div class="text-muted">No shipping address recorded.</div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6>Items</h6>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Product</th>
                                <th class="text-end">Unit</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderItems as $item)
                                <tr>
                                    <td>{{ data_get($item->product_snapshot, 'sku') }}</td>
                                    <td>{{ data_get($item->product_snapshot, 'title') }}</td>
                                    <td class="text-end">{{ config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP' }} {{ number_format($item->price, 2) }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP' }} {{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-body">
                    <h6>Summary</h6>
                    <div class="d-flex justify-content-between"><span>Subtotal</span><span>{{ config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP' }} {{ number_format($order->subtotal ?? $order->total_amount, 2) }}</span></div>
                    <div class="d-flex justify-content-between"><span>Discount</span><span>- {{ config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP' }} {{ number_format($order->discount_amount ?? 0, 2) }}</span></div>
                    <div class="d-flex justify-content-between"><span>Service</span><span>{{ config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP' }} {{ number_format($order->service_fee ?? 0, 2) }}</span></div>
                    <div class="d-flex justify-content-between"><span>Shipping</span><span>{{ ($order->shipping_amount ?? 0) <= 0 ? 'Free' : (config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP') . ' ' . number_format($order->shipping_amount ?? 0, 2) }}</span></div>
                    <div class="d-flex justify-content-between"><span>Tax</span><span>{{ config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP' }} {{ number_format($order->tax_amount ?? 0, 2) }}</span></div>
                    <hr />
                    <div class="d-flex justify-content-between"><strong>Total</strong><strong>{{ config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP' }} {{ number_format($order->total_amount, 2) }}</strong></div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6>Admin Actions</h6>
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-2">
                            <label for="status" class="form-label">Status</label>
                            @php
                                $statuses = \App\Models\Order::STATUSES;
                            @endphp
                            <select id="status" name="status" class="form-select">
                                @foreach($statuses as $s)
                                    @if($s === 'draft')
                                        @continue
                                    @endif
                                    <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-2">
                            <label for="notes" class="form-label">Admin Notes</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes', $order->notes ?? '') }}</textarea>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
