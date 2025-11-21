<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSize extends Model
{
    protected $fillable = [
        'product_id',
        'size_label',
        'quantity',
    ];

    /**
     * Get the product this size belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if this size is available
     */
    public function isAvailable(): bool
    {
        return $this->quantity > 0;
    }
}
