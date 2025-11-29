@extends('layouts.admin')

@section('title', 'Print Order')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Order Invoice — {{ $order->order_number }}</h2>
        <div>
            <button class="btn btn-outline-secondary" onclick="window.print()"><i class="fas fa-print me-2"></i>Print</button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-6">
                    <h6>Customer</h6>
                    <div>{{ $order->user->name ?? $order->user->email }}</div>
                    <div>{{ $order->user->email }}</div>
                </div>
                <div class="col-md-6 text-end">
                    <h6>Order</h6>
                    <div><strong>Order #:</strong> {{ $order->order_number }}</div>
                    <div><strong>Date:</strong> {{ $order->created_at->format('Y-m-d H:i') }}</div>
                    <div><strong>Status:</strong> {{ $order->status }}</div>
                </div>
            </div>

            <hr />

            <h6>Shipping Address</h6>
            @if($order->shipping_address_snapshot)
                <div>
                    @php $a = $order->shipping_address_snapshot; @endphp
                    <div>{{ $a['company'] ?? '' }}</div>
                    <div>{{ $a['address_line_1'] ?? '' }} {{ $a['address_line_2'] ?? '' }}</div>
                    <div>{{ $a['city'] ?? '' }} {{ $a['state_province'] ?? '' }} {{ $a['postal_code'] ?? '' }}</div>
                    <div>{{ $a['country'] ?? '' }}</div>
                </div>
            @else
                <div>No shipping address recorded.</div>
            @endif

            <hr />

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
                            <td class="text-end">{{ $item->formattedPrice }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end">{{ $item->formattedSubtotal }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="row">
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between"><span>Subtotal</span><span>{{ $order->formattedSubtotal }}</span></div>
                    <div class="d-flex justify-content-between"><span>Discount</span><span>- {{ $order->formattedDiscount }}</span></div>
                    <div class="d-flex justify-content-between"><span>Service Fee</span><span>{{ $order->formattedServiceFee }}</span></div>
                    <div class="d-flex justify-content-between"><span>Shipping</span><span>{{ ($order->shipping_amount ?? 0) <= 0 ? 'Free' : $order->formattedShipping }}</span></div>
                    <div class="d-flex justify-content-between"><span>Tax</span><span>{{ $order->formattedTax }}</span></div>
                    <hr />
                    <div class="d-flex justify-content-between"><strong>Total</strong><strong>{{ $order->formattedTotal }}</strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media print {
    body { -webkit-print-color-adjust: exact; }
}
</style>
@endpush
