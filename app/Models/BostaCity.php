<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BostaCity extends Model
{
    protected $fillable = [
        'bosta_id',
        'name',
        'name_ar',
        'code',
        'alias',
        'sector',
        'pickup_availability',
        'drop_off_availability',
        'hub',
    ];

    protected $casts = [
        'pickup_availability' => 'boolean',
        'drop_off_availability' => 'boolean',
        'hub' => 'array',
    ];

    /**
     * Scope to get cities available for pickup
     */
    public function scopePickupAvailable($query)
    {
        return $query->where('pickup_availability', true);
    }

    /**
     * Scope to get cities available for delivery
     */
    public function scopeDropOffAvailable($query)
    {
        return $query->where('drop_off_availability', true);
    }

    /**
     * Find city by name (case-insensitive)
     */
    public static function findByName(string $name): ?self
    {
        return static::where('name', 'LIKE', $name)
            ->orWhere('name_ar', 'LIKE', $name)
            ->orWhere('alias', 'LIKE', $name)
            ->first();
    }

    /**
     * Get BOSTA city ID for a given city name
     */
    public static function getBostaId(string $cityName): ?string
    {
        $city = static::findByName($cityName);
        return $city?->bosta_id;
    }
}
