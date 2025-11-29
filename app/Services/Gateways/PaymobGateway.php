<?php

namespace App\Services\Gateways;

use App\Models\Payment;
use App\Models\Order;
use GuzzleHttp\Client;
use Illuminate\Support\Str;

/**
 * Paymob gateway integration (basic)
 *
 * This implementation performs the basic flows needed to create a Paymob
 * payment iframe token and returns an iframe URL in the result meta.
 *
 * Note: Requires environment variables `PAYMOB_API_KEY`, `PAYMOB_INTEGRATION_ID`,
 * and `PAYMOB_IFRAME_ID` to be set in your environment or `.env`.
 */
class PaymobGateway implements PaymentGatewayInterface
{
    protected string $apiKey;
    protected string $integrationId;
    protected string $iframeId;
    protected string $baseUrl;
    protected Client $http;

    public function __construct()
    {
        $this->apiKey = config('services.paymob.api_key');
        $this->integrationId = config('services.paymob.integration_id');
        $this->iframeId = config('services.paymob.iframe_id');
        $this->baseUrl = rtrim(config('services.paymob.base_url', 'https://accept.paymob.com'), '/');

        $this->http = new Client(['base_uri' => $this->baseUrl, 'timeout' => 10]);
    }

    public function getName(): string
    {
        return 'paymob';
    }

    /**
     * Process a payment by creating a Paymob order and payment key then returning iframe URL.
     */
    public function processPayment(Payment $payment): array
    {
        if (!$this->apiKey || !$this->integrationId || !$this->iframeId) {
            return [
                'success' => false,
                'transaction_id' => null,
                'message' => 'Paymob is not configured (missing API keys)',
                'meta' => []
            ];
        }

        try {
            // 1) authenticate and get auth token
            $authResp = $this->http->post('/api/auth/tokens', [
                'json' => ['api_key' => $this->apiKey],
            ]);

            $authBody = json_decode($authResp->getBody()->getContents(), true);
            $token = $authBody['token'] ?? null;

            if (!$token) {
                return ['success' => false, 'transaction_id' => null, 'message' => 'Failed to authenticate with Paymob', 'meta' => $authBody];
            }

            // 2) create an order in Paymob
            $amountCents = (int) round($payment->amount * 100);
            $orderResp = $this->http->post('/api/ecommerce/orders', [
                'json' => [
                    'auth_token' => $token,
                    'delivery_needed' => false,
                    'amount_cents' => $amountCents,
                    'currency' => 'EGP',
                    'merchant_order_id' => $payment->order_id,
                    'items' => [],
                ],
            ]);

            $orderBody = json_decode($orderResp->getBody()->getContents(), true);
            $orderId = $orderBody['id'] ?? null;

            if (!$orderId) {
                return ['success' => false, 'transaction_id' => null, 'message' => 'Failed to create Paymob order', 'meta' => $orderBody];
            }

            // 3) request payment key
            $billingData = [
                'apartment' => '',
                'email' => $payment->meta['billing_email'] ?? '',
                'floor' => '',
                'first_name' => $payment->meta['billing_first_name'] ?? '',
                'street' => $payment->meta['billing_street'] ?? '',
                'building' => '',
                'phone_number' => $payment->meta['billing_phone'] ?? '',
                'shipping_method' => 'NO',
                'postal_code' => $payment->meta['billing_postal'] ?? '',
                'city' => $payment->meta['billing_city'] ?? '',
                'country' => $payment->meta['billing_country'] ?? 'EG',
                'last_name' => $payment->meta['billing_last_name'] ?? '',
                'state' => '',
            ];

            $paymentKeyResp = $this->http->post('/acceptance/payment_keys', [
                'json' => [
                    'auth_token' => $token,
                    'amount_cents' => $amountCents,
                    'expiration' => 3600,
                    'order_id' => $orderId,
                    'billing_data' => $billingData,
                    'integration_id' => (int)$this->integrationId,
                ],
            ]);

            $paymentKeyBody = json_decode($paymentKeyResp->getBody()->getContents(), true);
            $paymentToken = $paymentKeyBody['token'] ?? null;

            if (!$paymentToken) {
                return ['success' => false, 'transaction_id' => null, 'message' => 'Failed to generate Paymob payment token', 'meta' => $paymentKeyBody];
            }

            $iframeUrl = $this->baseUrl . '/api/acceptance/iframes/' . $this->iframeId . '?payment_token=' . $paymentToken;

            return [
                'success' => true,
                // We use the payment token as a temporary transaction identifier
                'transaction_id' => $paymentToken,
                'message' => 'Redirect to Paymob iframe',
                'meta' => [
                    'iframe_url' => $iframeUrl,
                    'order_response' => $orderBody,
                    'payment_key_response' => $paymentKeyBody,
                ],
                // indicate this requires a redirect / pending state
                'status' => 'pending',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'transaction_id' => null,
                'message' => 'Paymob integration error: ' . $e->getMessage(),
                'meta' => [],
            ];
        }
    }

    public function refundPayment(Payment $payment, float $amount = null): array
    {
        // Attempt a best-effort refund via Paymob API. Endpoint and payloads may
        // differ depending on Paymob account setup; this attempts a common pattern
        // and returns the gateway response for debugging.
        try {
            // authenticate
            $authResp = $this->http->post('/api/auth/tokens', [
                'json' => ['api_key' => $this->apiKey],
            ]);
            $authBody = json_decode($authResp->getBody()->getContents(), true);
            $token = $authBody['token'] ?? null;

            if (!$token) {
                return ['success' => false, 'message' => 'Failed to authenticate with Paymob', 'meta' => $authBody];
            }

            if (!$payment->provider_transaction_id) {
                return ['success' => false, 'message' => 'Missing provider transaction id', 'meta' => []];
            }

            $refundAmountCents = $amount ? (int) round($amount * 100) : (int) round($payment->amount * 100);

            // Try a generic refund endpoint (account-specific). If this fails,
            // return the error for inspection.
            $resp = $this->http->post('/acceptance/transactions/' . $payment->provider_transaction_id . '/refund', [
                'json' => [
                    'auth_token' => $token,
                    'amount_cents' => $refundAmountCents,
                ],
            ]);

            $body = json_decode($resp->getBody()->getContents(), true);

            return ['success' => true, 'refund_response' => $body];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Refund error: ' . $e->getMessage(), 'meta' => []];
        }
    }

    public function getPaymentStatus(string $transactionId): array
    {
        // Attempt to query payment status using Paymob payments API
        try {
            $authResp = $this->http->post('/api/auth/tokens', [
                'json' => ['api_key' => $this->apiKey],
            ]);
            $authBody = json_decode($authResp->getBody()->getContents(), true);
            $token = $authBody['token'] ?? null;

            if (!$token) {
                return ['success' => false, 'message' => 'Failed to authenticate with Paymob', 'meta' => $authBody];
            }

            // Paymob provides payment retrieval endpoints; here we attempt a best-effort GET
            $resp = $this->http->get('/api/acceptance/payments/' . $transactionId, [
                'query' => ['token' => $token],
            ]);

            $body = json_decode($resp->getBody()->getContents(), true);
            return ['success' => true, 'status' => $body['success'] ? ($body['is_paid'] ?? 'succeeded') : 'unknown', 'meta' => $body];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Status check error: ' . $e->getMessage(), 'meta' => []];
        }
    }
}
