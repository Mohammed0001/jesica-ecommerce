// Payments helpers: polling and postMessage handling
export async function pollPaymentStatus(statusUrl, onUpdate) {
    try {
        const res = await fetch(statusUrl, { credentials: 'same-origin' });
        if (!res.ok) return null;
        const data = await res.json();
        if (typeof onUpdate === 'function') onUpdate(data);
        return data;
    } catch (e) {
        console.warn('pollPaymentStatus error', e);
        return null;
    }
}

export function startPaymentPolling(orderId, intervalMs = 3000, onPaid) {
    if (!orderId) return;
    const statusUrl = `/payments/status/${orderId}`;

    const tick = async () => {
        const data = await pollPaymentStatus(statusUrl, (d) => {
            // update UI if needed via event
            document.dispatchEvent(new CustomEvent('payment.status', { detail: d }));
        });

        if (data && data.is_paid) {
            if (typeof onPaid === 'function') onPaid(data);
            return true;
        }

        return false;
    };

    // run immediately and then on interval
    tick().then((paid) => {
        if (paid) return;
        const id = setInterval(async () => {
            const paidNow = await tick();
            if (paidNow) clearInterval(id);
        }, intervalMs);
    });
}

// listen for postMessage events from gateways that support it.
export function attachPostMessageHandler({ onSuccess, onFailure }) {
    window.addEventListener('message', (ev) => {
        // allow messages only from known origins? Not enforced here — optional
        try {
            const data = typeof ev.data === 'string' ? JSON.parse(ev.data) : ev.data;
            if (!data || typeof data !== 'object') return;

            // Paymob and other gateways may send different message shapes.
            // We support common patterns: { type: 'payment_success' } or { event: 'paid' }
            if (data.type === 'payment_success' || data.event === 'paid' || data.status === 'success') {
                if (typeof onSuccess === 'function') onSuccess(data);
            } else if (data.type === 'payment_failed' || data.status === 'failed') {
                if (typeof onFailure === 'function') onFailure(data);
            }
        } catch (e) {
            // ignore non-JSON messages
        }
    });
}

// Expose helpers on window for Blade-included pages that rely on the bundle
if (typeof window !== 'undefined') {
    window.Payments = window.Payments || {};
    window.Payments.pollPaymentStatus = pollPaymentStatus;
    window.Payments.startPaymentPolling = startPaymentPolling;
    window.Payments.attachPostMessageHandler = attachPostMessageHandler;
}
