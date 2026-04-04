<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = [
        'name',
        'code',
        'delivery_fee',
        'city_names',
    ];

    protected $casts = [
        'delivery_fee' => 'float',
        'city_names'   => 'array',
    ];

    /**
     * Get all users in this region
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Find the delivery fee for a given city name.
     * Matches case-insensitively against each region's city_names JSON array.
     * Falls back to the global site_setting delivery_fee if no region matches.
     */
    public static function getDeliveryFeeForCity(?string $city): float
    {
        if (!$city) {
            return (float) SiteSetting::get('delivery_fee', 15);
        }

        $cityLower = mb_strtolower(trim($city));

        // Load all regions that have a delivery_fee configured
        $regions = static::whereNotNull('delivery_fee')
            ->whereNotNull('city_names')
            ->get();

        foreach ($regions as $region) {
            $names = $region->city_names ?? [];
            foreach ($names as $name) {
                if (mb_strtolower(trim($name)) === $cityLower) {
                    return (float) $region->delivery_fee;
                }
            }
        }

        // Fallback to global setting
        return (float) SiteSetting::get('delivery_fee', 15);
    }
}
