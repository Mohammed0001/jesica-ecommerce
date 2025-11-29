@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Complete Payment</h1>

    <p>Please complete your payment in the frame below. After completion you'll be redirected back.</p>

    <div style="width:100%;height:800px;border:1px solid #ddd;">
            <iframe id="paymob-iframe" src="{{ $iframeUrl }}" style="width:100%;height:100%;border:0;" allowfullscreen></iframe>
    </div>

        <div class="d-flex align-items-center mt-3">
            <div id="payment-status" class="me-3">
                <span class="badge bg-warning">Waiting for payment</span>
            </div>
            <div id="payment-actions">
                <a href="{{ $iframeUrl }}" target="_blank" class="btn btn-link">Open in new tab</a>
                <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-secondary">View order</a>
            </div>
        </div>

        <p class="mt-2 text-muted">If the iframe does not load, use the link above.</p>
</div>
@endsection

@push('scripts')
<script>
    (function(){
        const orderId = {{ isset($order) ? $order->id : 'null' }};
        const redirectUrl = '{{ route('orders.show', $order ?? null) }}';

        function updateStatusBadge(isPaid) {
            const el = document.getElementById('payment-status');
            if (!el) return;
            if (isPaid) {
                el.innerHTML = '<span class="badge bg-success">Payment received</span>';
            } else {
                el.innerHTML = '<span class="badge bg-warning">Waiting for payment</span>';
            }
        }

        // Wait until `window.Payments` is available (app.js/Vite bundle)
        function whenPaymentsReady(cb, maxWait = 5000) {
            const start = Date.now();
            (function check() {
                if (window.Payments && typeof window.Payments.startPaymentPolling === 'function') {
                    return cb();
                }
                if (Date.now() - start > maxWait) return; // give up silently
                setTimeout(check, 50);
            })();
        }

        if (!orderId) return;

        whenPaymentsReady(() => {
            // start polling
            window.Payments.startPaymentPolling(orderId, 3000, () => {
                updateStatusBadge(true);
                setTimeout(() => window.location.href = redirectUrl, 800);
            });

            // update badge on each status event
            document.addEventListener('payment.status', (e) => {
                const d = e.detail;
                updateStatusBadge(d.is_paid);
            });

            // attach postMessage handler
            window.Payments.attachPostMessageHandler({
                onSuccess: () => {
                    updateStatusBadge(true);
                    setTimeout(() => window.location.href = redirectUrl, 500);
                },
                onFailure: () => {
                    alert('Payment failed. Please try another method.');
                }
            });
        });
    })();
</script>
@endpush
