<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Order extends Model
{
    // Centralized list of valid order statuses — keep in sync with DB enum
    public const STATUSES = [
        'draft',
        'pending_deposit',
        'pending',
        'processing',
        'paid_deposit',
        'paid_full',
        'shipped',
        'delivered',
        'completed',
        'cancelled',
    ];

    protected $fillable = [
        'order_number',
        'user_id',
        'total_amount',
        'subtotal',
        'discount_amount',
        'shipping_amount',
        'service_fee',
        'tax_amount',
        'status',
        'shipping_address_id',
        'shipping_address_snapshot',
        'shipped_at',
        'completed_at',
        'notes',
        'stock_decremented',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_address_snapshot' => 'array',
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
        'stock_decremented' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . strtoupper(Str::random(8));
            }
        });
    }

    /**
     * Get the user this order belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shipping address
     */
    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    /**
     * Get the order items
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Compatibility alias for older code that expects `orderItems` relationship name
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payments for this order
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the shipment for this order
     */
    public function shipment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Shipment::class);
    }

    /**
     * Get all API logs for this order
     */
    public function apiLogs(): MorphMany
    {
        return $this->morphMany(ApiLog::class, 'loggable');
    }

    /**
     * Get successful payments
     */
    public function successfulPayments(): HasMany
    {
        return $this->payments()->where('status', 'succeeded');
    }

    /**
     * Get the total paid amount
     */
    public function getTotalPaidAttribute(): float
    {
        return (float) $this->successfulPayments()->sum('amount');
    }

    /**
     * Get the remaining balance
     */
    public function getRemainingBalanceAttribute(): float
    {
        return (float) ($this->total_amount - $this->total_paid);
    }

    /**
     * Check if order is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->remaining_balance <= 0;
    }

    /**
     * Check if order has deposit payment
     */
    public function hasDepositPayment(): bool
    {
        return $this->payments()->where('type', 'deposit')->where('status', 'succeeded')->exists();
    }

    /**
     * Get formatted total amount
     */
    public function getFormattedTotalAttribute(): string
    {
        $symbol = config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP';
        return $symbol . ' ' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted total paid
     */
    public function getFormattedTotalPaidAttribute(): string
    {
        $symbol = config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP';
        return $symbol . ' ' . number_format($this->total_paid, 2);
    }

    /**
     * Get formatted remaining balance
     */
    public function getFormattedRemainingBalanceAttribute(): string
    {
        $symbol = config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP';
        return $symbol . ' ' . number_format($this->remaining_balance, 2);
    }

    /**
     * Get formatted subtotal attribute
     */
    public function getFormattedSubtotalAttribute(): string
    {
        $symbol = config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP';
        return $symbol . ' ' . number_format($this->subtotal ?? 0, 2);
    }

    public function getFormattedShippingAttribute(): string
    {
        $symbol = config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP';
        return $symbol . ' ' . number_format($this->shipping_amount ?? 0, 2);
    }

    public function getFormattedServiceFeeAttribute(): string
    {
        $symbol = config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP';
        return $symbol . ' ' . number_format($this->service_fee ?? 0, 2);
    }

    public function getFormattedTaxAttribute(): string
    {
        $symbol = config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP';
        return $symbol . ' ' . number_format($this->tax_amount ?? 0, 2);
    }

    /**
     * Get formatted discount attribute
     */
    public function getFormattedDiscountAttribute(): string
    {
        $symbol = config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP';
        return $symbol . ' ' . number_format($this->discount_amount ?? 0, 2);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'badge-secondary',
            'pending_deposit' => 'badge-warning',
            'paid_deposit' => 'badge-info',
            'paid_full' => 'badge-success',
            'shipped' => 'badge-primary',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
