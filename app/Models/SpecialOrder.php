<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpecialOrder extends Model
{
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
