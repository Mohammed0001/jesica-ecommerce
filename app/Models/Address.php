<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'user_id',
        'type',
        'first_name',
        'last_name',
        'company',
        'address_line_1',
        'address_line_2',
        'city',
        'state_province',
        'postal_code',
        'country',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the user this address belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get orders using this address
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipping_address_id');
    }

    /**
     * Get the full name
     */
    public function getFullNameAttribute(): string
    {
        $name = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        if (!empty($name)) {
            return $name;
        }

        // Fallback to the user's name if no explicit first/last name saved
        return $this->user?->name ?? '';
    }

    /**
     * Get the formatted address
     */
    public function getFormattedAddressAttribute(): string
    {
        $address = $this->address_line_1;

        if ($this->address_line_2) {
            $address .= ', ' . $this->address_line_2;
        }

        $address .= ', ' . $this->city . ', ' . $this->state_province . ' ' . $this->postal_code;
        $address .= ', ' . $this->country;

        return $address;
    }
}
