<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Gateways\MockGateway;
use App\Services\Gateways\PaymobGateway;
use App\Services\Gateways\PaymentGatewayInterface;
use Exception;

class PaymentService
{
    protected PaymentGatewayInterface $gateway;

    public function __construct()
    {
        // Choose gateway implementation based on configuration (PAYMENT_PROVIDER)
        $provider = env('PAYMENT_PROVIDER', 'mock');

        switch (strtolower($provider)) {
            case 'paymob':
                $this->gateway = new PaymobGateway();
                break;
            case 'mock':
            default:
                $this->gateway = new MockGateway();
                break;
        }
    }

    /**
     * Calculate deposit amount for an order or an object/array containing a total_amount key/property.
     * Accepts an `Order` model, a stdClass/object with `total_amount`, or an array with `total_amount`.
     *
     * @param  Order|object|array  $order
     */
    public function calculateDeposit($order, float|int $depositPercentageOrFixed): float
    {
        // Extract total amount from different possible input types
        if ($order instanceof Order) {
            $total = $order->total_amount;
        } elseif (is_object($order) && property_exists($order, 'total_amount')) {
            $total = $order->total_amount;
        } elseif (is_array($order) && array_key_exists('total_amount', $order)) {
            $total = $order['total_amount'];
        } else {
            throw new \InvalidArgumentException('Order must be an Order instance or contain a total_amount value.');
        }

        // If the value is less than 1, treat it as a percentage
        if ($depositPercentageOrFixed < 1) {
            return round($total * $depositPercentageOrFixed, 2);
        }

        // If greater than or equal to 1, treat as fixed amount
        return round($depositPercentageOrFixed, 2);
    }

    /**
     * Create a payment record
     */
    public function createPaymentRecord(
        Order $order,
        float $amount,
        string $method,
        string $status = 'pending',
        string $type = 'payment',
        array $meta = []
    ): Payment {
        return Payment::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'method' => $method,
            'provider' => $this->gateway->getName(),
            'status' => $status,
            'type' => $type,
            'meta' => $meta,
        ]);
    }

    /**
     * Process a deposit payment
     */
    public function processDeposit(Order $order, float $amount, string $method): array
    {
        try {
            // Create payment record
            $payment = $this->createPaymentRecord($order, $amount, $method, 'pending', 'deposit');

            // Process payment through gateway
            $result = $this->gateway->processPayment($payment);

            if ($result['success']) {
                $status = $result['status'] ?? 'succeeded';

                $payment->update([
                    'status' => $status,
                    'provider_transaction_id' => $result['transaction_id'] ?? null,
                    'meta' => array_merge($payment->meta ?? [], $result['meta'] ?? [])
                ]);

                // Only mark order paid when gateway reports final success
                if ($status === 'succeeded') {
                    $order->update(['status' => 'paid_deposit']);
                }

                return [
                    'success' => true,
                    'payment' => $payment,
                    'message' => $result['message'] ?? 'Deposit payment processed',
                    'status' => $status,
                    'meta' => $result['meta'] ?? []
                ];
            } else {
                $payment->update([
                    'status' => 'failed',
                    'meta' => array_merge($payment->meta ?? [], $result['meta'] ?? [])
                ]);

                return [
                    'success' => false,
                    'payment' => $payment,
                    'message' => $result['message'] ?? 'Payment failed'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment processing error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process full payment
     */
    public function processFullPayment(Order $order, string $method): array
    {
        try {
            // Create payment record
            $payment = $this->createPaymentRecord($order, $order->total_amount, $method, 'pending', 'payment');

            // Process payment through gateway
            $result = $this->gateway->processPayment($payment);

            if ($result['success']) {
                $status = $result['status'] ?? 'succeeded';

                $payment->update([
                    'status' => $status,
                    'provider_transaction_id' => $result['transaction_id'] ?? null,
                    'meta' => array_merge($payment->meta ?? [], $result['meta'] ?? [])
                ]);

                // Only mark order paid when gateway reports final success
                if ($status === 'succeeded') {
                    $order->update(['status' => 'paid_full']);
                }

                return [
                    'success' => true,
                    'payment' => $payment,
                    'message' => $result['message'] ?? 'Full payment processed',
                    'status' => $status,
                    'meta' => $result['meta'] ?? []
                ];
            } else {
                $payment->update([
                    'status' => 'failed',
                    'meta' => array_merge($payment->meta ?? [], $result['meta'] ?? [])
                ]);

                return [
                    'success' => false,
                    'payment' => $payment,
                    'message' => $result['message'] ?? 'Payment failed'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment processing error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Capture remaining balance for an order
     */
    public function captureRemaining(Order $order, string $method = 'credit_card'): array
    {
        $remainingAmount = $order->remaining_balance;

        if ($remainingAmount <= 0) {
            return [
                'success' => false,
                'message' => 'No remaining balance to capture'
            ];
        }

        try {
            // Create payment record for remaining amount
            $payment = $this->createPaymentRecord($order, $remainingAmount, $method, 'pending', 'remaining');

            // Process payment through gateway
            $result = $this->gateway->processPayment($payment);

            if ($result['success']) {
                $status = $result['status'] ?? 'succeeded';

                $payment->update([
                    'status' => $status,
                    'provider_transaction_id' => $result['transaction_id'] ?? null,
                    'meta' => array_merge($payment->meta ?? [], $result['meta'] ?? [])
                ]);

                // Only mark order paid when gateway reports final success
                if ($status === 'succeeded') {
                    $order->update(['status' => 'paid_full']);
                }

                return [
                    'success' => true,
                    'payment' => $payment,
                    'message' => $result['message'] ?? 'Remaining balance captured',
                    'status' => $status,
                    'meta' => $result['meta'] ?? []
                ];
            } else {
                $payment->update([
                    'status' => 'failed',
                    'meta' => array_merge($payment->meta ?? [], $result['meta'] ?? [])
                ]);

                return [
                    'success' => false,
                    'payment' => $payment,
                    'message' => $result['message'] ?? 'Payment failed'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Payment processing error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get the default deposit percentage from config
     */
    public function getDefaultDepositPercentage(): float
    {
        return (float) config('app.default_deposit_percentage', 30) / 100;
    }
}
