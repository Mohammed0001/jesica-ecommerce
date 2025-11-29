<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class PaymentWebhookController extends BaseController
{
    /**
     * Handle Paymob webhook notifications
     */
    public function handlePaymob(Request $request, OrderService $orderService)
    {
        // Verify webhook signature if configured
        $secret = config('services.paymob.webhook_secret');
        if ($secret) {
            $signature = $request->header('X-Paymob-Signature') ?? $request->header('X-Signature') ?? $request->header('X-Hub-Signature') ?? $request->header('Signature');
            if (empty($signature)) {
                logger()->warning('Paymob webhook missing signature header.');
                return response()->json(['success' => false, 'message' => 'Missing signature header'], 403);
            }

            $payloadRaw = $request->getContent();

            // compute likely HMACs
            $computedSha512 = hash_hmac('sha512', $payloadRaw, $secret);
            $computedSha256 = hash_hmac('sha256', $payloadRaw, $secret);

            $sig = preg_replace('/^sha(?:256|512)=/i', '', $signature);

            $valid = false;
            if (hash_equals($computedSha512, $sig) || hash_equals($computedSha256, $sig)) {
                $valid = true;
            } else {
                // try base64 decode signature and compare with sha512
                $decoded = base64_decode($sig, true);
                if ($decoded !== false && hash_equals($computedSha512, bin2hex($decoded))) {
                    $valid = true;
                }
            }

            if (!$valid) {
                logger()->warning('Paymob webhook signature verification failed.', ['header' => $signature]);
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 403);
            }
        }

        $payload = $request->all();

        // Attempt to extract candidate transaction identifiers
        $candidates = [];
        if (isset($payload['id'])) $candidates[] = $payload['id'];
        if (isset($payload['token'])) $candidates[] = $payload['token'];
        if (Arr::get($payload, 'obj.id')) $candidates[] = Arr::get($payload, 'obj.id');
        if (Arr::get($payload, 'obj.transaction_id')) $candidates[] = Arr::get($payload, 'obj.transaction_id');
        if (Arr::get($payload, 'obj.payload.transaction.id')) $candidates[] = Arr::get($payload, 'obj.payload.transaction.id');
        if (Arr::get($payload, 'obj.payload.entity.id')) $candidates[] = Arr::get($payload, 'obj.payload.entity.id');

        $candidates = array_filter(array_unique($candidates));

        $payment = null;

        // Try to find payment by provider_transaction_id or meta.payment_token
        foreach ($candidates as $c) {
            $payment = Payment::where('provider_transaction_id', $c)->first();
            if ($payment) break;

            $payment = Payment::where('meta->payment_token', $c)->first();
            if ($payment) break;

            // Fallback: meta contains the token somewhere as string
            $payment = Payment::where('meta', 'like', '%"' . $c . '"%')->first();
            if ($payment) break;
        }

        // If still not found, try looking up by merchant order id inside payload
        if (!$payment) {
            $merchantOrderId = Arr::get($payload, 'obj.payload.order.merchant_order_id') ?? Arr::get($payload, 'obj.payload.order.id') ?? Arr::get($payload, 'order_id');
            if ($merchantOrderId) {
                $payment = Payment::where('order_id', $merchantOrderId)->latest()->first();
            }
        }

        if (!$payment) {
            // Unknown payment — log minimal info and return success to avoid retries
            logger()->warning('Payment webhook received but no matching payment found.', ['payload' => $payload]);
            return response()->json(['success' => false, 'message' => 'No matching payment found'], 200);
        }

        // Determine status from payload (best-effort)
        $status = 'pending';
        if (isset($payload['success']) && $payload['success']) {
            $status = 'succeeded';
        } elseif (Arr::get($payload, 'obj.is_paid') === true) {
            $status = 'succeeded';
        } elseif (Arr::get($payload, 'obj.payload.transaction.status')) {
            $txStatus = strtolower(Arr::get($payload, 'obj.payload.transaction.status'));
            if (in_array($txStatus, ['captured', 'success', 'succeeded', 'approved'])) {
                $status = 'succeeded';
            } elseif (in_array($txStatus, ['failed', 'declined', 'rejected'])) {
                $status = 'failed';
            } else {
                $status = 'pending';
            }
        }

        // Update payment record
        $newMeta = array_merge($payment->meta ?? [], $payload);
        $updateData = ['status' => $status, 'meta' => $newMeta];

        // If provider transaction id is present in payload and not already set, set it
        if (empty($payment->provider_transaction_id)) {
            if (!empty($candidates)) {
                $updateData['provider_transaction_id'] = $candidates[0];
            } elseif (!empty($payload['id'])) {
                $updateData['provider_transaction_id'] = $payload['id'];
            }
        }

        $payment->update($updateData);

        // If payment succeeded, update order and decrement stock (only once)
        if ($status === 'succeeded' && $payment->order) {
            $order = $payment->order;

            if (!in_array($order->status, ['paid_deposit', 'paid_full'])) {
                // Mark order paid depending on payment type
                if ($payment->type === 'deposit') {
                    $orderService->updateOrderStatus($order, 'paid_deposit');
                } else {
                    $orderService->updateOrderStatus($order, 'paid_full');
                }

                // Decrement stock
                $orderService->decrementStock($order);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Polling endpoint to check payment/order status for a given order.
     * Requires authentication and ownership of the order.
     */
    public function checkStatus(Request $request, Order $order)
    {
        // Ensure the authenticated user owns the order
        $user = Auth::user();
        if (!$user || ($user->id !== $order->user_id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $order->loadMissing('payments');

        return response()->json([
            'status' => $order->status,
            'is_paid' => $order->isFullyPaid(),
            'total_paid' => $order->total_paid,
            'remaining' => $order->remaining_balance,
        ]);
    }
}
