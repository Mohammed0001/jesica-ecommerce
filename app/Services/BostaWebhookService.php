<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\Order;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BostaWebhookService
{
    /**
     * BOSTA webhook event types
     */
    public const EVENT_DELIVERY_CREATED = 'delivery:created';
    public const EVENT_DELIVERY_UPDATED = 'delivery:updated';
    public const EVENT_DELIVERY_PICKED_UP = 'delivery:picked_up';
    public const EVENT_DELIVERY_IN_TRANSIT = 'delivery:in_transit';
    public const EVENT_DELIVERY_OUT_FOR_DELIVERY = 'delivery:out_for_delivery';
    public const EVENT_DELIVERY_DELIVERED = 'delivery:delivered';
    public const EVENT_DELIVERY_CANCELLED = 'delivery:cancelled';
    public const EVENT_DELIVERY_RETURNED = 'delivery:returned';
    public const EVENT_DELIVERY_FAILED = 'delivery:failed';

    /**
     * Process incoming webhook from BOSTA
     *
     * @param Request $request
     * @return array ['success' => bool, 'message' => string, 'shipment' => Shipment|null]
     */
    public function processWebhook(Request $request): array
    {
        $startTime = microtime(true);

        try {
            // Get raw payload
            $payload = $request->all();

            // Log incoming webhook
            Log::info('BOSTA webhook received', [
                'payload' => $payload,
                'headers' => $request->headers->all(),
            ]);

            // Verify webhook signature
            if (!$this->verifySignature($request)) {
                $this->logWebhook($request, null, 'signature_verification_failed', microtime(true) - $startTime);

                return [
                    'success' => false,
                    'message' => 'Invalid webhook signature',
                    'shipment' => null,
                ];
            }

            // Extract webhook data
            $webhookData = $this->extractWebhookData($payload);

            if (!$webhookData) {
                $this->logWebhook($request, null, 'invalid_payload', microtime(true) - $startTime);

                return [
                    'success' => false,
                    'message' => 'Invalid webhook payload',
                    'shipment' => null,
                ];
            }

            // Find shipment
            $shipment = $this->findShipment($webhookData);

            if (!$shipment) {
                $this->logWebhook($request, null, 'shipment_not_found', microtime(true) - $startTime);

                return [
                    'success' => false,
                    'message' => 'Shipment not found',
                    'shipment' => null,
                ];
            }

            // Process the webhook event
            $this->processEvent($shipment, $webhookData);

            // Update order status if needed
            $this->updateOrderStatus($shipment);

            // Log successful webhook processing
            $this->logWebhook($request, $shipment, 'success', microtime(true) - $startTime);

            return [
                'success' => true,
                'message' => 'Webhook processed successfully',
                'shipment' => $shipment,
            ];

        } catch (\Exception $e) {
            Log::error('BOSTA webhook processing error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $payload ?? null,
            ]);

            $this->logWebhook(
                $request,
                null,
                'exception: ' . $e->getMessage(),
                microtime(true) - $startTime
            );

            return [
                'success' => false,
                'message' => 'Internal error processing webhook',
                'shipment' => null,
            ];
        }
    }

    /**
     * Verify webhook signature
     *
     * @param Request $request
     * @return bool
     */
    protected function verifySignature(Request $request): bool
    {
        $webhookSecret = config('services.bosta.webhook_secret');

        // Skip verification if no secret is configured
        if (empty($webhookSecret)) {
            Log::warning('BOSTA webhook secret not configured - skipping signature verification');
            return true;
        }

        $signature = $request->header('X-Bosta-Signature')
                    ?? $request->header('X-BOSTA-Signature')
                    ?? $request->header('x-bosta-signature');

        if (!$signature) {
            Log::warning('BOSTA webhook signature header not found');
            return false;
        }

        // Calculate expected signature
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $webhookSecret);

        // Compare signatures securely
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Extract relevant data from webhook payload
     *
     * @param array $payload
     * @return array|null
     */
    protected function extractWebhookData(array $payload): ?array
    {
        // BOSTA webhook payload structure can vary
        // Common fields: trackingNumber, _id, state, type, etc.

        $trackingNumber = $payload['trackingNumber']
                        ?? $payload['tracking_number']
                        ?? $payload['data']['trackingNumber']
                        ?? null;

        $deliveryId = $payload['_id']
                    ?? $payload['delivery_id']
                    ?? $payload['deliveryId']
                    ?? $payload['data']['_id']
                    ?? null;

        $state = $payload['state']
                ?? $payload['status']
                ?? $payload['data']['state']
                ?? null;

        $type = $payload['type']
                ?? $payload['event_type']
                ?? $payload['eventType']
                ?? $payload['data']['type']
                ?? null;

        // Return null if we don't have minimum required data
        if (!$trackingNumber && !$deliveryId) {
            return null;
        }

        return [
            'tracking_number' => $trackingNumber,
            'delivery_id' => $deliveryId,
            'state' => $state,
            'type' => $type,
            'raw_payload' => $payload,
        ];
    }

    /**
     * Find shipment by tracking number or delivery ID
     *
     * @param array $webhookData
     * @return Shipment|null
     */
    protected function findShipment(array $webhookData): ?Shipment
    {
        $query = Shipment::query();

        if (!empty($webhookData['tracking_number'])) {
            $query->where('tracking_number', $webhookData['tracking_number']);
        } elseif (!empty($webhookData['delivery_id'])) {
            $query->orWhere('bosta_delivery_id', $webhookData['delivery_id']);
        }

        return $query->with('order')->first();
    }

    /**
     * Process the webhook event and update shipment
     *
     * @param Shipment $shipment
     * @param array $webhookData
     * @return void
     */
    protected function processEvent(Shipment $shipment, array $webhookData): void
    {
        $rawPayload = $webhookData['raw_payload'];
        $state = $webhookData['state'];
        $type = $webhookData['type'];

        // Map BOSTA state to our status
        $newStatus = $this->mapBostaStateToStatus($state);

        // Update shipment status
        if ($newStatus && $shipment->status !== $newStatus) {
            $oldStatus = $shipment->status;
            $shipment->status = $newStatus;

            Log::info('Shipment status updated', [
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'bosta_state' => $state,
            ]);
        }

        // Update timestamps based on status
        $this->updateShipmentTimestamps($shipment, $newStatus, $rawPayload);

        // Add tracking event to history
        $this->addTrackingEvent($shipment, $rawPayload);

        // Update tracking number if provided and missing
        if (!empty($webhookData['tracking_number']) && empty($shipment->tracking_number)) {
            $shipment->tracking_number = $webhookData['tracking_number'];
        }

        // Update delivery ID if provided and missing
        if (!empty($webhookData['delivery_id']) && empty($shipment->bosta_delivery_id)) {
            $shipment->bosta_delivery_id = $webhookData['delivery_id'];
        }

        // Save changes
        $shipment->save();
    }

    /**
     * Map BOSTA state/status to our internal status
     *
     * @param string|null $bostaState
     * @return string|null
     */
    protected function mapBostaStateToStatus(?string $bostaState): ?string
    {
        if (empty($bostaState)) {
            return null;
        }

        $normalized = strtolower(trim($bostaState));

        return match ($normalized) {
            // Created states
            'created',
            'scheduled for pickup',
            'pending pickup' => Shipment::STATUS_CREATED,

            // In transit states
            'picking up',
            'picked up',
            'in transit',
            'received at warehouse',
            'at warehouse',
            'in hub',
            'transferred' => Shipment::STATUS_IN_TRANSIT,

            // Out for delivery
            'out for delivery',
            'with courier' => Shipment::STATUS_OUT_FOR_DELIVERY,

            // Delivered
            'delivered',
            'delivered successfully' => Shipment::STATUS_DELIVERED,

            // Returned
            'returned',
            'return to sender',
            'returning',
            'returned to warehouse' => Shipment::STATUS_RETURNED,

            // Cancelled
            'cancelled',
            'canceled' => Shipment::STATUS_CANCELLED,

            // Failed
            'failed delivery',
            'delivery failed',
            'exception',
            'on hold' => Shipment::STATUS_FAILED,

            default => null,
        };
    }

    /**
     * Update shipment timestamps based on status change
     *
     * @param Shipment $shipment
     * @param string|null $newStatus
     * @param array $payload
     * @return void
     */
    protected function updateShipmentTimestamps(Shipment $shipment, ?string $newStatus, array $payload): void
    {
        // Extract timestamp from payload if available
        $timestamp = null;

        if (isset($payload['timestamp'])) {
            $timestamp = Carbon::parse($payload['timestamp']);
        } elseif (isset($payload['updatedAt'])) {
            $timestamp = Carbon::parse($payload['updatedAt']);
        } elseif (isset($payload['updated_at'])) {
            $timestamp = Carbon::parse($payload['updated_at']);
        }

        switch ($newStatus) {
            case Shipment::STATUS_IN_TRANSIT:
                if (!$shipment->picked_up_at) {
                    $shipment->picked_up_at = $timestamp ?? now();
                }
                break;

            case Shipment::STATUS_DELIVERED:
                if (!$shipment->delivered_at) {
                    $shipment->delivered_at = $timestamp ?? now();
                }
                break;

            case Shipment::STATUS_CANCELLED:
            case Shipment::STATUS_RETURNED:
                if (!$shipment->cancelled_at) {
                    $shipment->cancelled_at = $timestamp ?? now();
                }
                break;
        }
    }

    /**
     * Add tracking event to shipment history
     *
     * @param Shipment $shipment
     * @param array $payload
     * @return void
     */
    protected function addTrackingEvent(Shipment $shipment, array $payload): void
    {
        $history = $shipment->tracking_history ?? [];

        $event = [
            'timestamp' => $payload['timestamp'] ?? $payload['updatedAt'] ?? now()->toISOString(),
            'status' => $payload['state'] ?? $payload['status'] ?? 'Update',
            'message' => $payload['reason'] ?? $payload['message'] ?? $payload['notes'] ?? null,
            'location' => $payload['location'] ?? $payload['hub'] ?? null,
            'raw_data' => $payload,
        ];

        $history[] = $event;
        $shipment->tracking_history = $history;
    }

    /**
     * Update order status based on shipment status
     *
     * @param Shipment $shipment
     * @return void
     */
    protected function updateOrderStatus(Shipment $shipment): void
    {
        $order = $shipment->order;

        if (!$order) {
            return;
        }

        $statusChanged = false;

        // Update order status based on shipment status
        switch ($shipment->status) {
            case Shipment::STATUS_IN_TRANSIT:
            case Shipment::STATUS_OUT_FOR_DELIVERY:
                if ($order->status === 'processing') {
                    $order->status = 'shipped';
                    $order->shipped_at = $shipment->picked_up_at ?? now();
                    $statusChanged = true;
                }
                break;

            case Shipment::STATUS_DELIVERED:
                if ($order->status !== 'delivered') {
                    $order->status = 'delivered';
                    $order->shipped_at = $order->shipped_at ?? $shipment->picked_up_at ?? now();
                    $statusChanged = true;
                }
                break;

            case Shipment::STATUS_CANCELLED:
            case Shipment::STATUS_RETURNED:
                if (!in_array($order->status, ['cancelled', 'refunded'])) {
                    // You might want to handle this differently based on business logic
                    // For now, we'll just log it
                    Log::warning('Shipment cancelled/returned but order not cancelled', [
                        'order_id' => $order->id,
                        'shipment_id' => $shipment->id,
                        'shipment_status' => $shipment->status,
                        'order_status' => $order->status,
                    ]);
                }
                break;
        }

        if ($statusChanged) {
            $order->save();

            Log::info('Order status updated from shipment webhook', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'new_status' => $order->status,
                'shipment_status' => $shipment->status,
            ]);

            // Optionally send email notification to customer
            // event(new OrderStatusUpdated($order));
        }
    }

    /**
     * Log webhook processing to api_logs table
     *
     * @param Request $request
     * @param Shipment|null $shipment
     * @param string $status
     * @param float $duration
     * @return void
     */
    protected function logWebhook(Request $request, ?Shipment $shipment, string $status, float $duration): void
    {
        try {
            $apiLog = new ApiLog([
                'service' => 'bosta',
                'method' => 'POST',
                'endpoint' => '/webhooks/bosta',
                'request_data' => [
                    'payload' => $request->all(),
                    'headers' => $request->headers->all(),
                ],
                'response_data' => [
                    'status' => $status,
                    'shipment_id' => $shipment?->id,
                    'tracking_number' => $shipment?->tracking_number,
                ],
                'status_code' => $status === 'success' ? 200 : 400,
                'duration' => $duration,
            ]);

            if ($shipment) {
                $apiLog->loggable()->associate($shipment);
            }

            $apiLog->save();
        } catch (\Exception $e) {
            Log::error('Failed to log BOSTA webhook', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get webhook event type from payload
     *
     * @param array $payload
     * @return string|null
     */
    public function getEventType(array $payload): ?string
    {
        return $payload['type']
            ?? $payload['event_type']
            ?? $payload['eventType']
            ?? $payload['data']['type']
            ?? null;
    }

    /**
     * Check if webhook is a test event
     *
     * @param array $payload
     * @return bool
     */
    public function isTestEvent(array $payload): bool
    {
        return isset($payload['test']) && $payload['test'] === true
            || isset($payload['is_test']) && $payload['is_test'] === true
            || ($this->getEventType($payload) === 'test');
    }
}
