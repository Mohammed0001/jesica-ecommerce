@extends('layouts.app')

@section('title', 'Invoice - Order #' . $order->order_number)

@section('content')
<main class="invoice-page">
    <div class="container">
        <div class="invoice-wrapper">
            <div class="invoice-header">
                <div class="invoice-header-left">
                    <h1 class="invoice-title">INVOICE</h1>
                    <p class="invoice-number">Order #{{ $order->order_number }}</p>
                </div>
                <div class="invoice-header-right">
                    <div class="company-info">
                        <h2 class="company-name">Jesica Riad</h2>
                        <p class="company-address">
                            Fashion & Style<br>
                            E-commerce Store<br>
                            info@jesica-ecommerce.com
                        </p>
                    </div>
                </div>
            </div>

            <div class="invoice-details">
                <div class="invoice-dates">
                    <div class="date-item">
                        <span class="date-label">Invoice Date:</span>
                        <span class="date-value">{{ $order->created_at->format('F d, Y') }}</span>
                    </div>
                    @if($order->shipped_at)
                    <div class="date-item">
                        <span class="date-label">Shipped Date:</span>
                        <span class="date-value">{{ $order->shipped_at->format('F d, Y') }}</span>
                    </div>
                    @endif
                </div>

                @if($order->shipping_address_snapshot)
                <div class="shipping-address">
                    <h3 class="address-title">Shipping Address</h3>
                    @php
                        $address = $order->shipping_address_snapshot;
                    @endphp
                    <div class="address-content">
                        <p>{{ $order->user->name }}</p>
                        <p>{{ $address['street_address'] ?? '' }}</p>
                        @if(isset($address['apartment']))
                            <p>{{ $address['apartment'] }}</p>
                        @endif
                        <p>{{ $address['city'] ?? '' }}, {{ $address['state'] ?? '' }} {{ $address['postal_code'] ?? '' }}</p>
                        @if(isset($address['country']))
                            <p>{{ $address['country'] }}</p>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <div class="invoice-items">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td class="item-description">
                                <strong>{{ $item->product ? $item->product->name : 'Product Unavailable' }}</strong>
                                @if($item->product && $item->product->description)
                                    <br><small class="text-muted">{{ Str::limit($item->product->description, 80) }}</small>
                                @endif
                            </td>
                            <td>{{ $item->quantity }}</td>
                            <td>EGP{{ number_format($item->price, 2) }}</td>
                            <td>EGP{{ number_format($item->quantity * $item->price, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="subtotal-row">
                            <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                            <td><strong>EGP{{ number_format($order->items->sum(function($item) { return $item->quantity * $item->price; }), 2) }}</strong></td>
                        </tr>
                        <tr class="shipping-row">
                            <td colspan="3" class="text-right">Shipping:</td>
                            <td>Free</td>
                        </tr>
                        <tr class="tax-row">
                            <td colspan="3" class="text-right">Tax:</td>
                            <td>EGP0.00</td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td><strong>EGP{{ number_format($order->total_amount, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($order->payments && $order->payments->count() > 0)
            <div class="payment-details">
                <h3 class="payment-title">Payment Information</h3>
                @foreach($order->payments as $payment)
                <div class="payment-item">
                    <span class="payment-method">{{ ucfirst($payment->method) }}</span>
                    <span class="payment-status">{{ ucfirst($payment->status) }}</span>
                    <span class="payment-amount">EGP{{ number_format($payment->amount, 2) }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <div class="invoice-footer">
                <p class="footer-text">Thank you for your business!</p>
                <div class="invoice-actions">
                    <button onclick="window.print()" class="btn btn-primary">Print Invoice</button>
                    <a href="{{ route('orders.index') }}" class="btn btn-secondary">Back to Orders</a>
                </div>
            </div>
        </div>
    </div>
</main>

@push('styles')
<style>
.invoice-page {
    font-family: 'futura-pt', sans-serif;
    padding: 2rem 0;
    background: #f8f9fa;
}

.invoice-wrapper {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    overflow: hidden;
}

.invoice-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 3rem;
    background: var(--primary-color);
    color: white;
}

.invoice-title {
    font-weight: 200;
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    letter-spacing: 0.1em;
}

.invoice-number {
    font-weight: 200;
    font-size: 1.125rem;
    margin-bottom: 0;
    opacity: 0.9;
}

.company-name {
    font-weight: 200;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
    letter-spacing: 0.02em;
}

.company-address {
    font-weight: 200;
    line-height: 1.5;
    margin-bottom: 0;
    opacity: 0.9;
}

.invoice-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    padding: 2rem 3rem;
    border-bottom: 1px solid var(--border-light);
}

.date-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}

.date-label {
    font-weight: 300;
    color: var(--text-muted);
}

.date-value {
    font-weight: 200;
    color: var(--primary-color);
}

.address-title {
    font-weight: 300;
    font-size: 1.25rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
    letter-spacing: 0.02em;
}

.address-content p {
    margin-bottom: 0.25rem;
    color: var(--text-color);
    font-weight: 200;
    line-height: 1.4;
}

.invoice-items {
    padding: 0;
}

.items-table {
    width: 100%;
    border-collapse: collapse;
    font-family: 'futura-pt', sans-serif;
}

.items-table th {
    background: #f8f9fa;
    padding: 1.5rem;
    text-align: left;
    font-weight: 300;
    font-size: 0.875rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border-light);
}

.items-table th:last-child,
.items-table td:last-child {
    text-align: right;
}

.items-table td {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-light);
    font-weight: 200;
    color: var(--text-color);
    vertical-align: top;
}

.item-description strong {
    font-weight: 300;
    color: var(--primary-color);
}

.items-table tfoot td {
    border-bottom: none;
    font-weight: 200;
}

.subtotal-row td,
.shipping-row td,
.tax-row td {
    padding: 0.75rem 1.5rem;
}

.total-row td {
    padding: 1.5rem;
    font-size: 1.125rem;
    font-weight: 300;
    background: #f8f9fa;
    color: var(--primary-color);
}

.payment-details {
    padding: 2rem 3rem;
    border-bottom: 1px solid var(--border-light);
}

.payment-title {
    font-weight: 300;
    font-size: 1.25rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
    letter-spacing: 0.02em;
}

.payment-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    font-weight: 200;
}

.payment-method {
    color: var(--text-color);
}

.payment-status {
    color: var(--text-muted);
    font-size: 0.875rem;
    text-transform: capitalize;
}

.payment-amount {
    color: var(--primary-color);
    font-weight: 300;
}

.invoice-footer {
    padding: 3rem;
    text-align: center;
    background: #f8f9fa;
}

.footer-text {
    font-weight: 200;
    font-size: 1.125rem;
    color: var(--text-muted);
    margin-bottom: 2rem;
}

.invoice-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

.invoice-actions .btn {
    font-family: 'futura-pt', sans-serif;
    font-weight: 300;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 0.75rem 2rem;
    border-radius: 0;
    transition: all 0.3s ease;
}

/* Print Styles */
@media print {
    .invoice-page {
        background: white;
        padding: 0;
    }

    .invoice-wrapper {
        box-shadow: none;
        border-radius: 0;
    }

    .invoice-actions {
        display: none;
    }

    .invoice-footer {
        background: white;
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    .invoice-header {
        flex-direction: column;
        gap: 2rem;
        padding: 2rem;
    }

    .invoice-details {
        grid-template-columns: 1fr;
        gap: 2rem;
        padding: 1.5rem 2rem;
    }

    .items-table,
    .payment-details,
    .invoice-footer {
        padding: 1.5rem 2rem;
    }

    .items-table th,
    .items-table td {
        padding: 1rem;
    }

    .invoice-actions {
        flex-direction: column;
    }

    .invoice-actions .btn {
        width: 100%;
    }
}
</style>
@endpush
@endsection
