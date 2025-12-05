<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Shipment extends Model
{
    // BOSTA shipment statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_CREATED = 'created';
    public const STATUS_IN_TRANSIT = 'in_transit';
    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_RETURNED = 'returned';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'order_id',
        'provider',
        'tracking_number',
        'bosta_delivery_id',
        'awb_number',
        'status',
        'cod_amount',
        'is_cod',
        'pickup_address',
        'delivery_address',
        'bosta_response',
        'tracking_history',
        'picked_up_at',
        'delivered_at',
        'cancelled_at',
        'notes',
    ];

    protected $casts = [
        'cod_amount' => 'decimal:2',
        'is_cod' => 'boolean',
        'bosta_response' => 'array',
        'tracking_history' => 'array',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the order this shipment belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get all API logs for this shipment
     */
    public function apiLogs(): MorphMany
    {
        return $this->morphMany(ApiLog::class, 'loggable');
    }

    /**
     * Check if shipment is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if shipment is in transit
     */
    public function isInTransit(): bool
    {
        return in_array($this->status, [
            self::STATUS_IN_TRANSIT,
            self::STATUS_OUT_FOR_DELIVERY,
        ]);
    }

    /**
     * Check if shipment is delivered
     */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Check if shipment is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if shipment can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return !in_array($this->status, [
            self::STATUS_DELIVERED,
            self::STATUS_CANCELLED,
            self::STATUS_RETURNED,
        ]);
    }

    /**
     * Get the tracking URL for BOSTA
     */
    public function getTrackingUrl(): ?string
    {
        if (!$this->tracking_number) {
            return null;
        }

        return "https://bosta.co/en-eg/tracking-shipments?shipment-number={$this->tracking_number}";
    }

    /**
     * Add tracking event to history
     */
    public function addTrackingEvent(array $event): void
    {
        $history = $this->tracking_history ?? [];
        $history[] = array_merge($event, ['timestamp' => now()->toISOString()]);
        $this->tracking_history = $history;
        $this->save();
    }

    /**
     * Update shipment status from BOSTA webhook
     */
    public function updateFromBostaWebhook(array $data): void
    {
        $this->status = $this->mapBostaStatus($data['state'] ?? '');

        if (isset($data['trackingNumber'])) {
            $this->tracking_number = $data['trackingNumber'];
        }

        if ($this->status === self::STATUS_DELIVERED && !$this->delivered_at) {
            $this->delivered_at = now();
        }

        $this->addTrackingEvent($data);
        $this->save();
    }

    /**
     * Map BOSTA status to our internal status
     */
    protected function mapBostaStatus(string $bostaStatus): string
    {
        return match (strtolower($bostaStatus)) {
            'created', 'scheduled for pickup' => self::STATUS_CREATED,
            'picking up', 'picked up', 'in transit', 'received at warehouse' => self::STATUS_IN_TRANSIT,
            'out for delivery' => self::STATUS_OUT_FOR_DELIVERY,
            'delivered' => self::STATUS_DELIVERED,
            'returned', 'return to sender' => self::STATUS_RETURNED,
            'cancelled' => self::STATUS_CANCELLED,
            'failed delivery' => self::STATUS_FAILED,
            default => self::STATUS_PENDING,
        };
    }
}
