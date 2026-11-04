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
        'buy_quantity',
        'get_quantity',
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

    /**
     * Discount for a promo of the given type/value.
     *
     * `$lines` is a list of [unit price, quantity] pairs; it is only needed for
     * 'bogo' (buy X get Y). Units are grouped from the most expensive down in
     * sets of X+Y, and the Y cheapest units of every complete set are
     * discounted by `$value` percent (100 = free).
     */
    public static function discountAmount(string $type, float $value, float $subtotal, array $lines = [], int $buy = 1, int $get = 1): float
    {
        $discount = match ($type) {
            'percentage' => $subtotal * ($value / 100),
            'bogo' => self::bogoDiscount($value, $lines, $buy, $get),
            default => $value,
        };

        return round(min($subtotal, max(0, $discount)), 2);
    }

    private static function bogoDiscount(float $percentOff, array $lines, int $buy, int $get): float
    {
        $buy = max(1, $buy);
        $get = max(1, $get);
        $group = $buy + $get;

        $units = [];
        foreach ($lines as [$price, $quantity]) {
            $units = array_merge($units, array_fill(0, max(0, (int) $quantity), (float) $price));
        }
        rsort($units);

        $discount = 0.0;
        for ($start = 0; $start + $group <= count($units); $start += $group) {
            for ($i = $start + $buy; $i < $start + $group; $i++) {
                $discount += $units[$i] * ($percentOff / 100);
            }
        }

        return $discount;
    }

    public function bogoLabel(): string
    {
        return 'Buy ' . ($this->buy_quantity ?? 1) . ' Get ' . ($this->get_quantity ?? 1);
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
