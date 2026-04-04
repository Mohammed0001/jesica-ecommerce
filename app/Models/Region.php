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
     * Find the delivery fee for a given city/district and country.
     * If the country is not Egypt, returns the unified international rate.
     * Checks district first, then city against region names.
     * Falls back to the global site_setting delivery_fee if no region matches.
     */
    public static function getDeliveryFeeForLocation(?string $city, ?string $district = null, ?string $country = 'Egypt'): float
    {
        $countryLower = mb_strtolower(trim($country ?? ''));
        if (!in_array($countryLower, ['egypt', 'eg', 'مصر'])) {
            return (float) SiteSetting::get('international_delivery_fee', 50);
        }

        if (!$city && !$district) {
            return (float) SiteSetting::get('delivery_fee', 15);
        }

        $cityLower = $city ? mb_strtolower(trim($city)) : null;
        $districtLower = $district ? mb_strtolower(trim($district)) : null;

        $regions = static::whereNotNull('delivery_fee')
            ->whereNotNull('city_names')
            ->get();

        // Check district first (most specific)
        if ($districtLower) {
            foreach ($regions as $region) {
                $names = $region->city_names ?? [];
                foreach ($names as $name) {
                    if (mb_strtolower(trim($name)) === $districtLower) {
                        return (float) $region->delivery_fee;
                    }
                }
            }
        }

        // Then check city
        if ($cityLower) {
            foreach ($regions as $region) {
                $names = $region->city_names ?? [];
                foreach ($names as $name) {
                    if (mb_strtolower(trim($name)) === $cityLower) {
                        return (float) $region->delivery_fee;
                    }
                }
            }
        }

        // Fallback to global setting
        return (float) SiteSetting::get('delivery_fee', 15);
    }
}
