<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductColor extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'hex_code',
        'order',
        'is_available',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get the product this colour belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Colour to paint the swatch with. Falls back to a neutral grey so a
     * colour added without a hex code still renders as a pickable chip.
     */
    public function getSwatchColorAttribute(): string
    {
        return $this->hex_code ?: '#d9d9d9';
    }

    /**
     * Whether the swatch needs a darker border to stay visible on white
     */
    public function getIsLightAttribute(): bool
    {
        $hex = ltrim($this->swatch_color, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return true;
        }

        // Perceived brightness (ITU-R BT.601)
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return (($r * 299) + ($g * 587) + ($b * 114)) / 1000 > 200;
    }
}
