<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'collection_id',
        'title',
        'slug',
        'description',
        'story',
        'price',
        'sku',
        'quantity',
        'is_one_of_a_kind',
        'visible',
        'size_chart_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_one_of_a_kind' => 'boolean',
        'visible' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->title);
            }
        });

        static::updating(function (Product $product) {
            if ($product->isDirty('title') && empty($product->slug)) {
                $product->slug = Str::slug($product->title);
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get the collection this product belongs to
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the product's size chart
     */
    public function sizeChart(): BelongsTo
    {
        return $this->belongsTo(SizeChart::class);
    }

    /**
     * Get the product's sizes
     */
    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class);
    }

    /**
     * Get the product's images
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    /**
     * Get the product's order items
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope a query to only include visible products
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('visible', true);
    }

    /**
     * Scope a query to only include available products
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('quantity', '>', 0);
    }

    /**
     * Check if product is available
     */
    public function isAvailable(): bool
    {
        return $this->quantity > 0;
    }

    /**
     * Check if product is sold out
     */
    public function isSoldOut(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Get the main product image
     */
    public function getMainImageAttribute(): ?ProductImage
    {
        return $this->images()->first();
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 2) . ' <span class="currency">EGP</span>';
    }

    /**
     * Get available sizes
     */
    public function getAvailableSizesAttribute(): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->is_one_of_a_kind) {
            return collect(['One Size']);
        }

        return $this->sizes()->where('quantity', '>', 0)->get();
    }

    /**
     * Get name attribute (alias for title)
     */
    public function getNameAttribute(): string
    {
        return $this->title;
    }
}
