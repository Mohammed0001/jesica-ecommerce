<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Shipment;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BostaService
{
    protected ?string $apiKey;
    protected string $baseUrl;
    protected bool $isSandbox;
    protected array $pickupAddress;

    public function __construct()
    {
        $this->apiKey = config('services.bosta.api_key');
        $this->isSandbox = config('services.bosta.sandbox', false);
        $this->baseUrl = $this->isSandbox
            ? 'https://app.bosta.co/api/v2'
            : 'https://app.bosta.co/api/v2';

        $this->pickupAddress = [
            'firstLine' => config('services.bosta.pickup_address.first_line', ''),
            'secondLine' => config('services.bosta.pickup_address.second_line', ''),
            'city' => config('services.bosta.pickup_address.city', ''),
            'zone' => config('services.bosta.pickup_address.zone', ''),
            'district' => config('services.bosta.pickup_address.district', ''),
        ];
    }

    /**
     * Check if BOSTA is configured
     */
    protected function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Create a shipment in BOSTA
     */
    public function createShipment(Order $order): ?Shipment
    {
        $startTime = microtime(true);

        try {
            // Check if BOSTA is configured
            if (!$this->isConfigured()) {
                Log::warning('BOSTA is not configured. Skipping shipment creation.', ['order_id' => $order->id]);
                return null;
            }

            // Get shipping address from order
            $shippingAddress = $order->shipping_address_snapshot ?? $order->shippingAddress?->toArray();

            if (!$shippingAddress) {
                Log::error('No shipping address found for order', ['order_id' => $order->id]);
                return null;
            }

            // Get customer phone number
            $customerPhone = $shippingAddress['phone']
                ?? $shippingAddress['phone_number']
                ?? $order->user->phone
                ?? null;

            // BOSTA requires phone number
            if (empty($customerPhone)) {
                Log::error('Missing customer phone number for BOSTA shipment', [
                    'order_id' => $order->id,
                    'shipping_address' => $shippingAddress,
                    'user_phone' => $order->user->phone ?? null,
                ]);
                return null;
            }

            // Prepare delivery details
            $deliveryData = $this->prepareDeliveryData($order, $shippingAddress, $customerPhone);

            // Make API call to BOSTA
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/deliveries?apiVersion=1", $deliveryData);

            $duration = microtime(true) - $startTime;

            // Log the API call
            $this->logApiCall(
                'POST',
                '/deliveries',
                $deliveryData,
                $response,
                $duration,
                $order
            );

            if ($response->successful()) {
                $data = $response->json();

                // Create or update shipment record
                return $this->createShipmentRecord($order, $data, $shippingAddress);
            }

            Log::error('BOSTA API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $order->id,
            ]);

            return null;
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;

            // Log the failed API call
            ApiLog::create([
                'service' => 'BOSTA',
                'method' => 'POST',
                'endpoint' => "{$this->baseUrl}/deliveries",
                'request_body' => json_encode($deliveryData ?? []),
                'response_status' => 0,
                'duration' => $duration,
                'loggable_type' => Order::class,
                'loggable_id' => $order->id,
                'error_message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
            ]);

            Log::error('Failed to create BOSTA shipment', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Prepare delivery data for BOSTA API
     */
    protected function prepareDeliveryData(Order $order, array $shippingAddress, string $customerPhone): array
    {
        // Determine if this is a COD order
        $isCOD = $this->isOrderCOD($order);
        $codAmount = $isCOD ? $order->total_amount : 0;

        // Get city information
        $cityName = $this->getCityName($shippingAddress);
        $cityId = $this->getCityId($shippingAddress);

        // Prepare address lines - BOSTA requires firstLine
        $firstLine = $shippingAddress['street_address']
            ?? $shippingAddress['address_line_1']
            ?? $shippingAddress['address']
            ?? '';

        $secondLine = $shippingAddress['address_line_2']
            ?? $shippingAddress['building_number']
            ?? '';

        // BOSTA requires at least firstLine and city
        if (empty($firstLine)) {
            $firstLine = ($shippingAddress['state_province'] ?? 'Cairo') . ', ' . ($shippingAddress['postal_code'] ?? '');
        }

        // Get zone/district - validate it's not just a single character
        $stateProvince = $shippingAddress['state_province']
            ?? $shippingAddress['state']
            ?? $shippingAddress['zone']
            ?? $shippingAddress['district']
            ?? '';

        // Validate zone/district has meaningful content (more than 2 characters)
        if (empty($stateProvince) || strlen($stateProvince) < 3) {
            $stateProvince = $cityName; // Use city name as fallback
        }

        $zone = $stateProvince;
        $districtName = $stateProvince;

        $data = [
            'type' => 10, // 10 for Deliver, 15 for Cash Collection, 30 for Exchange, 25 for CRP
            'specs' => [
                'packageType' => config('services.bosta.default_package_type', 'Parcel'),
                'size' => config('services.bosta.default_package_size', 'SMALL'),
                'packageDetails' => [
                    'itemsCount' => $order->items->sum('quantity'),
                    'description' => 'Fashion items from ' . config('app.name'),
                ],
            ],
            'dropOffAddress' => [
                'firstLine' => $firstLine,
                'secondLine' => $secondLine,
                'city' => $cityName,
                'zone' => $zone,
                'districtName' => $districtName,
                'cityId' => $cityId,
                'buildingNumber' => $shippingAddress['building_number'] ?? '',
                'floor' => $shippingAddress['floor'] ?? '',
                'apartment' => $shippingAddress['apartment'] ?? '',
            ],
            'receiver' => [
                'firstName' => $order->user->name ?? 'Customer',
                'lastName' => '',
                'phone' => $customerPhone, // Required by BOSTA
                'email' => $order->user->email ?? '',
            ],
            'cod' => $codAmount,
            'allowToOpenPackage' => config('services.bosta.allow_open_package', false),
            'notes' => $order->notes ?? "Order #{$order->order_number}",
            'businessReference' => $order->order_number,
        ];

        // Add business location ID if configured
        $businessLocationId = config('services.bosta.business_location_id');
        if (!empty($businessLocationId)) {
            $data['businessLocationId'] = $businessLocationId;
        }

        // Log address data for debugging
        Log::info('BOSTA Delivery Data', [
            'order_id' => $order->id,
            'dropOffAddress' => $data['dropOffAddress'],
            'original_address' => $shippingAddress,
        ]);

        return $data;
    }

    /**
     * Check if order is Cash on Delivery
     */
    protected function isOrderCOD(Order $order): bool
    {
        // Check if any payment is COD or if payment method is COD
        $codPayment = $order->payments()
            ->where('method', 'cod')
            ->orWhere('method', 'cash_on_delivery')
            ->first();

        return $codPayment !== null || $order->total_paid == 0;
    }

    /**
     * Create shipment record in database
     */
    protected function createShipmentRecord(Order $order, array $bostaData, array $shippingAddress): Shipment
    {
        $isCOD = $this->isOrderCOD($order);

        // Extract data from nested 'data' object in BOSTA response
        $shipmentData = $bostaData['data'] ?? $bostaData;

        return Shipment::create([
            'order_id' => $order->id,
            'provider' => 'bosta',
            'tracking_number' => $shipmentData['trackingNumber'] ?? null,
            'bosta_delivery_id' => $shipmentData['_id'] ?? null,
            'awb_number' => $shipmentData['awbNumber'] ?? null,
            'status' => $this->mapBostaStateToStatus($shipmentData['state'] ?? null),
            'cod_amount' => $isCOD ? $order->total_amount : 0,
            'is_cod' => $isCOD,
            'pickup_address' => json_encode($this->pickupAddress),
            'delivery_address' => json_encode($shippingAddress),
            'bosta_response' => $bostaData,
            'tracking_history' => [
                [
                    'status' => $shipmentData['state']['value'] ?? 'created',
                    'message' => $shipmentData['message'] ?? 'Shipment created in BOSTA',
                    'timestamp' => now()->toISOString(),
                ],
            ],
        ]);
    }

    /**
     * Map BOSTA state object to internal status
     */
    protected function mapBostaStateToStatus(?array $state): string
    {
        if (!$state || !isset($state['code'])) {
            return Shipment::STATUS_CREATED;
        }

        // Map BOSTA state codes to internal status
        $statusMap = [
            10 => Shipment::STATUS_PENDING,      // Pickup requested
            20 => Shipment::STATUS_IN_TRANSIT,   // Picked up
            30 => Shipment::STATUS_IN_TRANSIT,   // In transit
            40 => Shipment::STATUS_OUT_FOR_DELIVERY, // Out for delivery
            45 => Shipment::STATUS_DELIVERED,    // Delivered
            50 => Shipment::STATUS_CANCELLED,    // Cancelled
            60 => Shipment::STATUS_RETURNED,     // Returned
        ];

        return $statusMap[$state['code']] ?? Shipment::STATUS_CREATED;
    }

    /**
     * Track a shipment
     * BOSTA API: GET /deliveries/:trackingNumber
     */
    public function trackShipment(string $trackingNumber): ?array
    {
        $startTime = microtime(true);

        try {
            // BOSTA uses tracking number as the delivery ID
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/deliveries/{$trackingNumber}");

            $duration = microtime(true) - $startTime;

            // Log the API call (no related model for tracking)
            $this->logApiCall(
                'GET',
                "/deliveries/{$trackingNumber}",
                [],
                $response,
                $duration,
                null
            );

            if ($response->successful()) {
                $data = $response->json();

                Log::info('BOSTA shipment tracked successfully', [
                    'tracking_number' => $trackingNumber,
                    'current_status' => $data['CurrentStatus']['state'] ?? 'unknown',
                ]);

                return $data;
            }

            // Log failed response
            $errorData = $response->json();
            Log::warning('BOSTA tracking request failed', [
                'tracking_number' => $trackingNumber,
                'status_code' => $response->status(),
                'error' => $errorData,
            ]);

            return null;
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;

            ApiLog::create([
                'service' => 'BOSTA',
                'method' => 'GET',
                'endpoint' => "{$this->baseUrl}/deliveries/{$trackingNumber}",
                'duration' => $duration,
                'error_message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
            ]);

            Log::error('Exception while tracking BOSTA shipment', [
                'tracking_number' => $trackingNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Cancel a shipment
     * BOSTA API: PUT /deliveries/:id with status change or DELETE /deliveries/:id
     */
    public function cancelShipment(Shipment $shipment): bool
    {
        $startTime = microtime(true);

        try {
            if (!$shipment->bosta_delivery_id) {
                Log::warning('Cannot cancel shipment without BOSTA delivery ID', [
                    'shipment_id' => $shipment->id,
                ]);
                return false;
            }

            // Check if shipment can be cancelled based on current status
            if (!$shipment->canBeCancelled()) {
                Log::warning('Shipment cannot be cancelled due to current status', [
                    'shipment_id' => $shipment->id,
                    'current_status' => $shipment->status,
                ]);
                return false;
            }

            // Try DELETE request first (standard BOSTA approach)
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->delete("{$this->baseUrl}/deliveries/{$shipment->bosta_delivery_id}");

            $duration = microtime(true) - $startTime;

            // Log the API call
            $this->logApiCall(
                'DELETE',
                "/deliveries/{$shipment->bosta_delivery_id}",
                [],
                $response,
                $duration,
                $shipment
            );

            // Check if successful
            if ($response->successful()) {
                $shipment->update([
                    'status' => Shipment::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                ]);

                $shipment->addTrackingEvent([
                    'status' => 'cancelled',
                    'message' => 'Shipment cancelled successfully',
                    'timestamp' => now(),
                ]);

                Log::info('BOSTA shipment cancelled successfully', [
                    'shipment_id' => $shipment->id,
                    'bosta_delivery_id' => $shipment->bosta_delivery_id,
                ]);

                return true;
            }

            // Handle 404 - shipment not found or already cancelled in BOSTA
            if ($response->status() === 404) {
                // Update local status to cancelled since BOSTA doesn't have it
                $shipment->update([
                    'status' => Shipment::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                ]);

                $shipment->addTrackingEvent([
                    'status' => 'cancelled',
                    'message' => 'Shipment marked as cancelled (not found in BOSTA system)',
                    'timestamp' => now(),
                ]);

                Log::warning('BOSTA shipment not found (404), marked as cancelled locally', [
                    'shipment_id' => $shipment->id,
                    'bosta_delivery_id' => $shipment->bosta_delivery_id,
                ]);

                return true; // Consider this successful since the shipment is effectively cancelled
            }

            // For other errors, log and return false
            $errorData = $response->json();
            $errorMessage = $errorData['message'] ?? $errorData['error'] ?? 'Unknown error';

            Log::error('BOSTA shipment cancellation failed', [
                'shipment_id' => $shipment->id,
                'bosta_delivery_id' => $shipment->bosta_delivery_id,
                'status_code' => $response->status(),
                'error_message' => $errorMessage,
                'error_data' => $errorData,
            ]);

            return false;
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;

            ApiLog::create([
                'service' => 'BOSTA',
                'method' => 'DELETE',
                'endpoint' => "{$this->baseUrl}/deliveries/{$shipment->bosta_delivery_id}",
                'duration' => $duration,
                'loggable_type' => Shipment::class,
                'loggable_id' => $shipment->id,
                'error_message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
            ]);

            Log::error('Exception while cancelling BOSTA shipment', [
                'shipment_id' => $shipment->id,
                'bosta_delivery_id' => $shipment->bosta_delivery_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Get AWB (Air Waybill) label URL for printing
     */
    public function getAWBUrl(Shipment $shipment): ?string
    {
        if (!$shipment->tracking_number) {
            return null;
        }

        // BOSTA AWB label can be accessed through their tracking page with print parameter
        // Or through the business dashboard after login
        // For direct access, use the tracking page which has a print option
        return "https://bosta.co/en-eg/tracking-shipments?shipment-number={$shipment->tracking_number}&print=true";
    }

    /**
     * Update shipment from tracking data
     * Fetches latest tracking info from BOSTA and updates local shipment record
     */
    public function updateShipmentFromTracking(Shipment $shipment): bool
    {
        $startTime = microtime(true);

        try {
            // Validate shipment has tracking number
            if (!$shipment->tracking_number) {
                Log::warning('Cannot update tracking for shipment without tracking number', [
                    'shipment_id' => $shipment->id,
                ]);
                return false;
            }

            // Fetch tracking data from BOSTA
            $trackingData = $this->trackShipment($shipment->tracking_number);

            if (!$trackingData) {
                Log::warning('No tracking data returned from BOSTA', [
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                ]);
                return false;
            }

            // Extract current status
            $currentState = $trackingData['CurrentStatus']['state'] ?? '';
            $currentDescription = $trackingData['CurrentStatus']['description'] ?? '';

            if (!$currentState) {
                Log::warning('No current status found in tracking data', [
                    'shipment_id' => $shipment->id,
                    'tracking_number' => $shipment->tracking_number,
                ]);
                return false;
            }

            // Update shipment status
            $oldStatus = $shipment->status;
            $newStatus = $shipment->mapBostaStatus($currentState);
            $shipment->status = $newStatus;

            // Update delivered timestamp if delivered
            if ($newStatus === Shipment::STATUS_DELIVERED && !$shipment->delivered_at) {
                $shipment->delivered_at = now();
            }

            // Update cancelled timestamp if cancelled
            if ($newStatus === Shipment::STATUS_CANCELLED && !$shipment->cancelled_at) {
                $shipment->cancelled_at = now();
            }

            // Add current status to tracking events if it's new
            if ($oldStatus !== $newStatus) {
                $shipment->addTrackingEvent([
                    'status' => $currentState,
                    'message' => $currentDescription ?: 'Status updated from BOSTA',
                    'timestamp' => $trackingData['CurrentStatus']['timestamp'] ?? now()->toISOString(),
                ]);
            }

            // Add historical tracking events if available
            if (isset($trackingData['TrackingHistory']) && is_array($trackingData['TrackingHistory'])) {
                $eventsAdded = 0;
                foreach ($trackingData['TrackingHistory'] as $event) {
                    if (isset($event['state'])) {
                        $shipment->addTrackingEvent([
                            'status' => $event['state'],
                            'message' => $event['description'] ?? $event['state'],
                            'timestamp' => $event['timestamp'] ?? now()->toISOString(),
                        ]);
                        $eventsAdded++;
                    }
                }

                Log::info('Added tracking events from BOSTA history', [
                    'shipment_id' => $shipment->id,
                    'events_added' => $eventsAdded,
                ]);
            }

            $shipment->save();

            $duration = microtime(true) - $startTime;

            Log::info('BOSTA tracking updated successfully', [
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'duration' => round($duration, 3),
            ]);

            return true;
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;

            Log::error('Exception while updating shipment from tracking', [
                'shipment_id' => $shipment->id,
                'tracking_number' => $shipment->tracking_number ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'duration' => round($duration, 3),
            ]);

            return false;
        }
    }

    /**
     * Request a pickup
     */
    public function requestPickup(array $deliveryIds, string $pickupDate = null): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}/pickups", [
                'deliveryIds' => $deliveryIds,
                'scheduledDate' => $pickupDate ?? now()->addDay()->format('Y-m-d'),
                'scheduledTimeSlot' => config('services.bosta.default_pickup_slot', '10:00 to 13:00'),
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to request BOSTA pickup', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get list of BOSTA cities
     */
    public function getCities(): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
            ])->get("{$this->baseUrl}/cities");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get BOSTA cities', [
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get BOSTA city ID based on city name from database
     */
    private function getCityId(array $shippingAddress): string
    {
        $cityName = $shippingAddress['city'] ?? '';

        // Try to get from database first
        $bostaId = \App\Models\BostaCity::getBostaId($cityName);

        // Fallback to Cairo if not found
        return $bostaId ?? 'FceDyHXwpSYYF9zGW';
    }

    /**
     * Get proper city name for BOSTA
     */
    private function getCityName(array $shippingAddress): string
    {
        $cityName = $shippingAddress['city'] ?? '';

        // Validate city exists in BOSTA database
        $city = \App\Models\BostaCity::findByName($cityName);

        return $city?->name ?? 'Cairo';
    }

    /**
     * Log API call to database
     */
    private function logApiCall(
        string $method,
        string $endpoint,
        array $requestData,
        $response,
        float $duration,
        $relatedModel = null
    ): void {
        try {
            $logData = [
                'service' => 'BOSTA',
                'method' => $method,
                'endpoint' => $endpoint,
                'request_headers' => [
                    'Authorization' => 'Bearer ***',
                    'Content-Type' => 'application/json',
                ],
                'request_body' => !empty($requestData) ? json_encode($requestData) : null,
                'response_status' => $response->status(),
                'response_headers' => $response->headers(),
                'response_body' => $response->body(),
                'duration' => $duration,
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'loggable_type' => null,
                'loggable_id' => null,
            ];

            // Add polymorphic relation if provided
            if ($relatedModel) {
                $logData['loggable_type'] = get_class($relatedModel);
                $logData['loggable_id'] = $relatedModel->id;
            }

            ApiLog::create($logData);
        } catch (\Exception $e) {
            // Silently fail if logging fails - don't break the main flow
            Log::error('Failed to log API call', ['error' => $e->getMessage()]);
        }
    }
}
