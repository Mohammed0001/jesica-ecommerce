<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class PromoCode extends Model
{
    protected $fillable = [
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
