<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use Illuminate\Support\Str;

/**
 * Mock payment gateway for development and testing
 * This simulates payment processing without actually charging cards
 */
class MockGateway implements PaymentGatewayInterface
{
    /**
     * Get the gateway name
     */
    public function getName(): string
    {
        return 'mock';
    }

    /**
     * Process a payment
     *
     * @param Payment $payment
     * @return array ['success' => bool, 'transaction_id' => string, 'message' => string, 'meta' => array]
     */
    public function processPayment(Payment $payment): array
    {
        // Simulate processing delay
        usleep(500000); // 0.5 seconds

        // Generate a mock transaction ID
        $transactionId = 'mock_' . strtoupper(Str::random(10));

        // Simulate some failures (10% failure rate)
        if (rand(1, 10) === 1) {
            return [
                'success' => false,
                'transaction_id' => null,
                'message' => 'Payment declined by bank',
                'meta' => [
                    'decline_code' => 'insufficient_funds',
                    'gateway_response' => 'Simulated payment failure',
                    'processed_at' => now()->toISOString(),
                ]
            ];
        }

        // Simulate successful payment
        return [
            'success' => true,
            'transaction_id' => $transactionId,
            'message' => 'Payment processed successfully',
            'meta' => [
                'gateway_response' => 'Approved',
                'authorization_code' => strtoupper(Str::random(6)),
                'processed_at' => now()->toISOString(),
                'card_last_four' => '****' . rand(1000, 9999),
                'card_type' => $this->getRandomCardType(),
            ]
        ];
    }

    /**
     * Refund a payment
     */
    public function refundPayment(Payment $payment, float $amount = null): array
    {
        // Simulate processing delay
        usleep(300000); // 0.3 seconds

        $refundAmount = $amount ?? $payment->amount;
        $refundId = 'refund_' . strtoupper(Str::random(10));

        // Simulate some refund failures (5% failure rate)
        if (rand(1, 20) === 1) {
            return [
                'success' => false,
                'refund_id' => null,
                'message' => 'Refund failed',
                'meta' => [
                    'error_code' => 'refund_failed',
                    'gateway_response' => 'Unable to process refund',
                    'processed_at' => now()->toISOString(),
                ]
            ];
        }

        return [
            'success' => true,
            'refund_id' => $refundId,
            'amount' => $refundAmount,
            'message' => 'Refund processed successfully',
            'meta' => [
                'gateway_response' => 'Refund approved',
                'processed_at' => now()->toISOString(),
                'original_transaction_id' => $payment->provider_transaction_id,
            ]
        ];
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $transactionId): array
    {
        // For mock gateway, assume all transactions are successful
        // if they have a valid format
        if (str_starts_with($transactionId, 'mock_')) {
            return [
                'success' => true,
                'status' => 'succeeded',
                'transaction_id' => $transactionId,
                'meta' => [
                    'gateway_status' => 'completed',
                    'checked_at' => now()->toISOString(),
                ]
            ];
        }

        return [
            'success' => false,
            'status' => 'not_found',
            'message' => 'Transaction not found',
            'meta' => [
                'checked_at' => now()->toISOString(),
            ]
        ];
    }

    /**
     * Get a random card type for simulation
     */
    private function getRandomCardType(): string
    {
        $cardTypes = ['Visa', 'Mastercard', 'American Express', 'Discover'];
        return $cardTypes[array_rand($cardTypes)];
    }

    /**
     * Webhook handler for payment updates (mock)
     * In a real implementation, this would handle webhooks from the payment provider
     */
    public function handleWebhook(array $payload): array
    {
        return [
            'success' => true,
            'message' => 'Webhook processed (mock)',
            'meta' => [
                'webhook_id' => Str::random(10),
                'processed_at' => now()->toISOString(),
            ]
        ];
    }
}
