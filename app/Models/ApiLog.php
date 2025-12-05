<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiLog extends Model
{
    protected $fillable = [
        'service',
        'method',
        'endpoint',
        'request_headers',
        'request_body',
        'response_status',
        'response_headers',
        'response_body',
        'duration',
        'ip_address',
        'user_id',
        'loggable_id',
        'loggable_type',
        'error_message',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'response_headers' => 'array',
        'duration' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the owning loggable model (Order, Shipment, etc.)
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who triggered the API call
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the API call was successful
     */
    public function isSuccessful(): bool
    {
        return $this->response_status >= 200 && $this->response_status < 300;
    }

    /**
     * Get formatted duration
     */
    public function getFormattedDurationAttribute(): string
    {
        if (!$this->duration) {
            return 'N/A';
        }
        return number_format($this->duration, 2) . 's';
    }

    /**
     * Scope to filter by service
     */
    public function scopeForService($query, string $service)
    {
        return $query->where('service', $service);
    }

    /**
     * Scope to filter by success
     */
    public function scopeSuccessful($query)
    {
        return $query->whereBetween('response_status', [200, 299]);
    }

    /**
     * Scope to filter by failure
     */
    public function scopeFailed($query)
    {
        return $query->where(function ($q) {
            $q->where('response_status', '<', 200)
              ->orWhere('response_status', '>=', 300)
              ->orWhereNotNull('error_message');
        });
    }
}
