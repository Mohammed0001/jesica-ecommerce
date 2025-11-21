<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'total_amount',
        'status',
        'shipping_address_id',
        'shipping_address_snapshot',
        'shipped_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'shipping_address_snapshot' => 'array',
        'shipped_at' => 'datetime',
        'completed_at' => 'datetime',
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
     * Get the payments for this order
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
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
        return '$' . number_format($this->total_amount, 2);
    }

    /**
     * Get formatted total paid
     */
    public function getFormattedTotalPaidAttribute(): string
    {
        return '$' . number_format($this->total_paid, 2);
    }

    /**
     * Get formatted remaining balance
     */
    public function getFormattedRemainingBalanceAttribute(): string
    {
        return '$' . number_format($this->remaining_balance, 2);
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
