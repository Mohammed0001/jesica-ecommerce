<?php

namespace App\Services\Gateways;

use App\Models\Payment;

interface PaymentGatewayInterface
{
    /**
     * Get the gateway name
     */
    public function getName(): string;

    /**
     * Process a payment
     */
    public function processPayment(Payment $payment): array;

    /**
     * Refund a payment
     */
    public function refundPayment(Payment $payment, float $amount = null): array;

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $transactionId): array;
}
