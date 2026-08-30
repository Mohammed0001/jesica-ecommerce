<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'price',
        'quantity',
        'size_label',
        'color_name',
        'subtotal',
        'product_snapshot',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'product_snapshot' => 'array',
    ];

    /**
     * Get the order this item belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        $displayCurrency = session('currency', 'EGP');
        return number_format($this->price, 2) . ' ' . $displayCurrency;
    }

    /**
     * Human-readable variant, e.g. "Size: M / Colour: Ivory". Empty string
     * when the product has neither.
     */
    public function getVariantLabelAttribute(): string
    {
        $parts = [];

        if ($this->size_label) {
            $parts[] = 'Size: ' . $this->size_label;
        }

        if ($this->color_name) {
            $parts[] = 'Colour: ' . $this->color_name;
        }

        return implode(' / ', $parts);
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        $displayCurrency = session('currency', 'EGP');
        return number_format($this->subtotal, 2) . ' ' . $displayCurrency;
    }
}
