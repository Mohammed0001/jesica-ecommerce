<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'path',
        'alt_text',
        'order',
    ];

    /**
     * Get the product this image belongs to
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the image URL
     */
    public function getUrlAttribute(): string
    {
        // If path starts with http, it's an external URL
        if (str_starts_with($this->path, 'http')) {
            return $this->path;
        }

        // For placeholder images, generate picsum URL
        if (str_starts_with($this->path, 'picsum/')) {
            $parts = explode('-', basename($this->path, '.jpg'));
            $productId = $parts[1] ?? 1;
            $imageIndex = $parts[2] ?? 0;
            return 'https://picsum.photos/600/800?random=' . $productId . $imageIndex;
        }

        // Otherwise, use storage path - prefer Storage::url and check the file exists
        if (Storage::disk('public')->exists($this->path)) {
            return Storage::url($this->path);
        }

        // If the file is missing (no storage link or file gone), return a placeholder image
        return asset('images/picsum/600x800-1-0.jpg');
    }
}
