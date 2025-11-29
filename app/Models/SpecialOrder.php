<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Product;

class SpecialOrder extends Model
{
    // Centralized list of valid special order statuses (matches DB enum)
    public const STATUSES = [
        'requested',
        'under_review',
        'accepted',
        'in_progress',
        'completed',
        'rejected',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'measurements',
        'notes',
        'estimated_price',
        'deposit_amount',
        'desired_delivery_date',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'measurements' => 'array',
        'estimated_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'desired_delivery_date' => 'date',
    ];

    /**
     * Get the user who made this special order
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Optional product association (some views expect a product)
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Backwards-compatible customer name/email/phone accessors used by admin views
     */
    public function getCustomerNameAttribute(): ?string
    {
        return $this->user?->name ?? $this->attributes['customer_name'] ?? null;
    }

    public function getCustomerEmailAttribute(): ?string
    {
        return $this->user?->email ?? $this->attributes['customer_email'] ?? null;
    }

    public function getCustomerPhoneAttribute(): ?string
    {
        return $this->user?->phone ?? $this->attributes['customer_phone'] ?? null;
    }

    public function getMessageAttribute(): ?string
    {
        // some code writes customer message into `notes`
        return $this->attributes['message'] ?? $this->attributes['notes'] ?? null;
    }

    public function getBudgetAttribute(): ?float
    {
        return $this->estimated_price ?? null;
    }

    public function getDeadlineAttribute()
    {
        return $this->desired_delivery_date ?? null;
    }

    /**
     * Get formatted estimated price
     */
    public function getFormattedEstimatedPriceAttribute(): ?string
    {
        return $this->estimated_price ? '$' . number_format($this->estimated_price, 2) : null;
    }

    /**
     * Get formatted deposit amount
     */
    public function getFormattedDepositAmountAttribute(): ?string
    {
        return $this->deposit_amount ? '$' . number_format($this->deposit_amount, 2) : null;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'requested' => 'badge-info',
            'under_review' => 'badge-warning',
            'accepted' => 'badge-success',
            'in_progress' => 'badge-primary',
            'completed' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-secondary',
        };
    }
}
