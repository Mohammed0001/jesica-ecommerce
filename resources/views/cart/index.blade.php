@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="display-6 fw-light mb-4">Shopping Cart</h1>
        </div>
    </div>

    @if(count($cartItems) > 0)
        <div class="row">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-0">
                        @foreach($cartItems as $index => $item)
                            @php
                                // Use data_get to safely handle arrays and objects for product id and cart key
                                $cartProductId = data_get($item, 'product_id') ?? data_get($item, 'product.id');
                                $cartKey = data_get($item, 'cart_key');
                            @endphp
                            <div class="cart-item p-4 {{ $index > 0 ? 'border-top' : '' }}" data-item-id="{{ $cartKey ?? $cartProductId }}">
                                <div class="row align-items-center">
                                    <!-- Product Image -->
                                    <div class="col-md-2 col-sm-3">
                                        @if(data_get($item, 'product.main_image.url'))
                                            <img src="{{ data_get($item, 'product.main_image.url') ?? asset('images/picsum/600x800-1-0.jpg') }}"
                                                 class="img-fluid rounded"
                                                 alt="{{ data_get($item, 'product.name') }}"
                                                 style="height: 80px; width: 80px; object-fit: cover;">
                                        @else
                                            <div class="bg-light d-flex align-items-center justify-content-center rounded"
                                                 style="height: 80px; width: 80px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Product Details -->
                                    <div class="col-md-4 col-sm-5">
                                        <h6 class="mb-1">
                                            <a href="{{ route('products.show', data_get($item, 'product.slug')) }}"
                                               class="text-decoration-none text-dark">
                                                {{ data_get($item, 'product.name') }}
                                            </a>
                                        </h6>
                                        <small class="text-muted">
                                            SKU: {{ data_get($item, 'product.sku') }}
                                        </small>
                                        @if(data_get($item, 'size'))
                                            <br><small class="text-muted">Size: {{ data_get($item, 'size') }}</small>
                                        @endif
                                        @if(data_get($item, 'color'))
                                            <br><small class="text-muted">Color: {{ ucfirst(data_get($item, 'color')) }}</small>
                                        @endif
                                        @if(data_get($item, 'is_deposit'))
                                            <br><span class="badge bg-info">Deposit Payment</span>
                                        @endif
                                    </div>

                                    <!-- Quantity Controls -->
                                    {{-- <div class="col-md-2 col-sm-2">
                                        <div class="input-group" style="max-width: 120px;">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    onclick="updateQuantity('{{ $cartKey ?? $cartProductId }}', {{ (int) data_get($item, 'quantity', 0) - 1 }})">
                                                -
                                            </button>
                          <input type="number" class="form-control form-control-sm text-center"
                              value="{{ data_get($item, 'quantity') }}"
                                                   min="1"
                              max="{{ data_get($item, 'product.stock_quantity') }}"
                                                   onchange="updateQuantity('{{ $cartKey ?? $cartProductId }}', this.value)">
                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    onclick="updateQuantity('{{ $cartKey ?? $cartProductId }}', {{ (int) data_get($item, 'quantity', 0) + 1 }})">
                                                +
                                            </button>
                                        </div>
                                    </div> --}}

                                    <!-- Price -->
                                    <div class="col-md-2 col-sm-2 text-center">
                                            <div class="price">
                                            @if(data_get($item, 'is_deposit'))
                                                <span class="fw-bold">{!! data_get($item, 'formatted_price') !!}</span>
                                                <br><small class="text-muted">(30% deposit)</small>
                                            @else
                                                <span class="fw-bold">{!! data_get($item, 'formatted_price') !!}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Total -->
                                    <div class="col-md-1 col-sm-2 text-center">
                                        <div class="total fw-bold">
                                            {!! config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') !!} {{ number_format(data_get($item, 'display_subtotal', 0), 2) }}
                                        </div>
                                    </div>

                                    <!-- Remove Button -->
                                    <div class="col-md-1 text-end">
                    <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="removeFromCart('{{ $cartKey ?? $cartProductId }}')"
                                                title="Remove from cart">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Cart Actions -->
                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('collections.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                    <button type="button" class="btn btn-outline-danger" onclick="clearCart()">
                        <i class="fas fa-trash me-2"></i>Clear Cart
                    </button>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal ({{ collect($cartItems)->sum('quantity') }} items)</span>
                            <span class="fw-bold">{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($displaySubtotal, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount</span>
                            <span class="text-muted">- {{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($discountAmount, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Service Fee ({{ number_format((float) \App\Models\SiteSetting::get('service_fee_percentage', 0), 2) }}%)</span>
                            <span class="text-muted">{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($serviceFee ?? 0, 2) }}</span>
                        </div>

                        <div class="d-flex justify-content-between mb-3">
                            <span>Shipping</span>
                            <span class="text-muted">
                                @if($shipping <= 0)
                                    <span class="text-success">Free</span>
                                @else
                                    {{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($shipping, 2) }}
                                @endif
                            </span>
                        </div>

                        @php $deliveryThreshold = (float) \App\Models\SiteSetting::get('delivery_threshold', 200); @endphp
                        @if($displaySubtotal < $deliveryThreshold)
                            <div class="alert alert-info py-2 px-3 small">
                                <i class="fas fa-truck me-1"></i>
                                Add {{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($deliveryThreshold - $displaySubtotal, 2) }} more for free shipping!
                            </div>
                        @endif

                        <div class="d-flex justify-content-between mb-3">
                            <span>Tax (estimated)</span>
                            <span>{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }}{{ number_format($tax, 2) }}</span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-4">
                            <span class="h5">Total</span>
                            <span class="h5 fw-bold">{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($total, 2) }}</span>
                        </div>

                        <!-- Special Notes for Deposit Payments -->
                        @if($hasDepositItems)
                            <div class="alert alert-warning py-2 px-3 small mb-3">
                                <i class="fas fa-info-circle me-1"></i>
                                Some items use deposit payment. You'll pay the remaining balance before shipping.
                            </div>
                        @endif

                        <!-- Promo Code -->
                        <div class="mb-3">
                            <div class="input-group">
                                <input type="text" class="form-control" placeholder="Promo code" id="promoCode">
                                <button class="btn btn-outline-secondary" type="button" onclick="applyPromoCode()">
                                    Apply
                                </button>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <div class="d-grid">
                            <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">
                                Proceed to Checkout
                            </a>
                        </div>

                        <!-- Payment Options -->
                        <div class="text-center mt-3">
                            <small class="text-muted">We accept</small>
                            <div class="payment-icons mt-2">
                                <i class="fab fa-cc-visa fa-2x text-muted me-2"></i>
                                <i class="fab fa-cc-mastercard fa-2x text-muted me-2"></i>
                                <i class="fab fa-cc-amex fa-2x text-muted me-2"></i>
                                <i class="fab fa-cc-paypal fa-2x text-muted"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recently Viewed -->
                @if(session('recently_viewed') && count(session('recently_viewed')) > 0)
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Recently Viewed</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                @foreach(session('recently_viewed') as $recentProduct)
                                    @if($recentProduct['id'] != request()->route('product'))
                                        <div class="col-6">
                                            <div class="card border-0">
                                                <a href="{{ route('products.show', $recentProduct['slug']) }}" class="text-decoration-none">
                                                    @if($recentProduct['image'])
                                                        <img src="{{ asset('storage/' . $recentProduct['image']) }}"
                                                             class="card-img-top"
                                                             alt="{{ $recentProduct['name'] }}"
                                                             style="height: 80px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light d-flex align-items-center justify-content-center"
                                                             style="height: 80px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div class="card-body p-2">
                                                        <small class="text-dark">{{ Str::limit($recentProduct['name'], 20) }}</small>
                                                           <br><small class="text-muted">{{ config('currencies.symbols')[session('currency', 'EGP')] ?? session('currency', 'EGP') }} {{ number_format($recentProduct['price'], 2) }}</small>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Empty Cart -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                    <h3 class="text-muted mb-3">Your cart is empty</h3>
                    <p class="text-muted mb-4">Looks like you haven't added any items to your cart yet.</p>
                    <a href="{{ route('collections.index') }}" class="btn btn-primary btn-lg">
                        Start Shopping
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function updateQuantity(cartKey, newQuantity) {
    if (newQuantity < 1) {
        removeFromCart(cartKey);
        return;
    }

    fetch('/cart/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            cart_key: cartKey,
            quantity: newQuantity
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to update totals
        } else {
            showNotification(data.message || 'Error updating quantity', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error updating quantity', 'error');
    });
}

function removeFromCart(cartKey) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }

    fetch('/cart/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            cart_key: cartKey
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the cart item from DOM
            const cartItem = document.querySelector(`[data-item-id="${cartKey}"]`);
            if (cartItem) {
                cartItem.remove();
            }

            // Reload page if cart is empty
            if (data.cart_count === 0) {
                location.reload();
            } else {
                // Update cart count in navbar
                updateCartCount();
                showNotification('Item removed from cart', 'success');
            }
        } else {
            showNotification(data.message || 'Error removing item', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error removing item', 'error');
    });
}

function clearCart() {
    if (!confirm('Are you sure you want to clear your entire cart?')) {
        return;
    }

    fetch('/cart/clear', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            showNotification(data.message || 'Error clearing cart', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error clearing cart', 'error');
    });
}

function applyPromoCode() {
    const promoCode = document.getElementById('promoCode').value.trim();

    if (!promoCode) {
        showNotification('Please enter a promo code', 'error');
        return;
    }

    fetch('/cart/apply-promo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            promo_code: promoCode
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to show updated totals
        } else {
            showNotification(data.message || 'Invalid promo code', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error applying promo code', 'error');
    });
}

function updateCartCount() {
    fetch('/cart/count')
        .then(response => response.json())
        .then(data => {
            const cartCount = document.querySelector('.cart-count');
            if (cartCount) {
                cartCount.textContent = data.count;
            }
        });
}

function showNotification(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : type === 'error' ? 'alert-danger' : 'alert-info';
    const notification = document.createElement('div');
    notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(notification);

    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}
</script>
@endsection
