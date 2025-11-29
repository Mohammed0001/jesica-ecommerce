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
        $symbol = config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP';
        return $symbol . ' ' . number_format($this->price, 2);
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotalAttribute(): string
    {
        $symbol = config('currencies.symbols')[session('currency', 'EGP')] ?? 'EGP';
        return $symbol . ' ' . number_format($this->subtotal, 2);
    }
}
