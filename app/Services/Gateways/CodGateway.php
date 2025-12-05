<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use Illuminate\Support\Str;

/**
 * Cash on Delivery (COD) payment gateway
 * No actual payment processing - order is marked as pending payment
 */
class CodGateway implements PaymentGatewayInterface
{
    /**
     * Get the gateway name
     */
    public function getName(): string
    {
        return 'cod';
    }

    /**
     * Process a COD payment
     * COD doesn't charge immediately - payment is collected on delivery
     *
     * @param Payment $payment
     * @return array ['success' => bool, 'transaction_id' => string, 'message' => string, 'meta' => array]
     */
    public function processPayment(Payment $payment): array
    {
        // Generate a COD reference ID
        $referenceId = 'COD_' . strtoupper(Str::random(10));

        // COD is always successful at checkout - payment collected later
        return [
            'success' => true,
            'status' => 'succeeded', // Mark as succeeded to allow order processing
            'transaction_id' => $referenceId,
            'message' => 'Order placed successfully. Payment will be collected on delivery.',
            'meta' => [
                'payment_method' => 'cash_on_delivery',
                'gateway_response' => 'COD order created',
                'processed_at' => now()->toISOString(),
                'payment_due_on' => 'delivery',
            ]
        ];
    }

    /**
     * Refund a COD payment
     * For COD, refunds are handled manually
     *
     * @param Payment $payment
     * @param float|null $amount
     * @return array
     */
    public function refundPayment(Payment $payment, float $amount = null): array
    {
        return [
            'success' => false,
            'message' => 'COD payments must be refunded manually',
            'meta' => [
                'requires_manual_refund' => true,
            ]
        ];
    }

    /**
     * Get payment status
     * For COD, status is managed internally
     *
     * @param string $transactionId
     * @return array
     */
    public function getPaymentStatus(string $transactionId): array
    {
        return [
            'success' => true,
            'status' => 'pending_collection',
            'message' => 'Payment will be collected on delivery',
            'meta' => [
                'payment_method' => 'cash_on_delivery',
            ]
        ];
    }
}
