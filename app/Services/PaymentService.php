<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Gateways\MockGateway;
use App\Services\Gateways\PaymentGatewayInterface;
use Exception;

class PaymentService
{
    protected PaymentGatewayInterface $gateway;

    public function __construct()
    {
        // For now, we'll use MockGateway. Later can be swapped with Stripe, PayPal, etc.
        $this->gateway = new MockGateway();
    }

    /**
     * Calculate deposit amount for an order
     */
    public function calculateDeposit(Order $order, float|int $depositPercentageOrFixed): float
    {
        // If the value is less than 1, treat it as a percentage
        if ($depositPercentageOrFixed < 1) {
            return round($order->total_amount * $depositPercentageOrFixed, 2);
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
                $payment->update([
                    'status' => 'succeeded',
                    'provider_transaction_id' => $result['transaction_id'],
                    'meta' => array_merge($payment->meta ?? [], $result['meta'] ?? [])
                ]);

                // Update order status
                $order->update(['status' => 'paid_deposit']);

                return [
                    'success' => true,
                    'payment' => $payment,
                    'message' => 'Deposit payment processed successfully'
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
                $payment->update([
                    'status' => 'succeeded',
                    'provider_transaction_id' => $result['transaction_id'],
                    'meta' => array_merge($payment->meta ?? [], $result['meta'] ?? [])
                ]);

                // Update order status
                $order->update(['status' => 'paid_full']);

                return [
                    'success' => true,
                    'payment' => $payment,
                    'message' => 'Full payment processed successfully'
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
                $payment->update([
                    'status' => 'succeeded',
                    'provider_transaction_id' => $result['transaction_id'],
                    'meta' => array_merge($payment->meta ?? [], $result['meta'] ?? [])
                ]);

                // Update order status
                $order->update(['status' => 'paid_full']);

                return [
                    'success' => true,
                    'payment' => $payment,
                    'message' => 'Remaining balance captured successfully'
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
