<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class PromoCode extends Model
{
    protected $fillable = [
        'owner_user_id',
        'code',
        'description',
        'type',
        'value',
        'usage_count',
        'max_uses',
        'active',
        'expires_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'value' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    /**
     * The affiliate this promo code is assigned to, if any
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Orders placed using this promo code
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        if (!$this->active) return false;
        if ($this->isExpired()) return false;
        if ($this->max_uses !== null && $this->usage_count >= $this->max_uses) return false;
        return true;
    }
}
