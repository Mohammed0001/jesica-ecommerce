<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'collection_id',
        'title',
        'slug',
        'description',
        'story',
        'price',
        'sale_price',
        'sale_starts_at',
        'sale_ends_at',
        'currency',
        'sku',
        'quantity',
        'is_one_of_a_kind',
        'is_sold_out',
        'visible',
        'size_chart_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'sale_starts_at' => 'datetime',
        'sale_ends_at' => 'datetime',
        'is_one_of_a_kind' => 'boolean',
        'is_sold_out' => 'boolean',
        'visible' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->title);
            }
        });

        static::updating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->title, $product->id);
            }
        });
    }

    /**
     * Build a URL slug that is unique across the products table.
     *
     * Two products may legitimately share a title, so a numeric suffix is
     * appended until the slug is free (silk-dress, silk-dress-2, ...).
     * Soft-deleted rows are included because the unique index still covers
     * them.
     *
     * @param  string    $title     Title to slugify.
     * @param  int|null  $ignoreId  Product to exclude (the one being updated).
     */
    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);

        // Titles made entirely of non-latin characters slugify to an empty
        // string, so fall back to something addressable.
        if ($base === '') {
            $base = 'product';
        }

        $slug = $base;
        $suffix = 2;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn (Builder $q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
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
     * Get the product's colours, in display order
     */
    public function colors(): HasMany
    {
        return $this->hasMany(ProductColor::class)->orderBy('order')->orderBy('id');
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
     * Scope a query to only include products with a live sale price
     */
    public function scopeOnSale(Builder $query): void
    {
        $now = now();

        $query->whereNotNull('sale_price')
            ->whereColumn('sale_price', '<', 'price')
            ->where(fn (Builder $q) => $q->whereNull('sale_starts_at')->orWhere('sale_starts_at', '<=', $now))
            ->where(fn (Builder $q) => $q->whereNull('sale_ends_at')->orWhere('sale_ends_at', '>=', $now));
    }

    /**
     * Scope a query to only include available products
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('is_sold_out', false)->where(function (Builder $q) {
            $q->where(function (Builder $q2) {
                $q2->where('is_one_of_a_kind', true)->where('quantity', '>', 0);
            })->orWhere(function (Builder $q2) {
                $q2->where('is_one_of_a_kind', false)
                    ->whereHas('sizes', fn (Builder $sq) => $sq->where('quantity', '>', 0));
            });
        });
    }

    /**
     * Check if product is available. For multi-size products, stock lives on
     * ProductSize rows rather than the product's own quantity column.
     */
    public function isAvailable(): bool
    {
        if ($this->is_sold_out) {
            return false;
        }

        if ($this->is_one_of_a_kind) {
            return $this->quantity > 0;
        }

        $sizes = $this->relationLoaded('sizes') ? $this->sizes : $this->sizes()->get();
        return $sizes->contains(fn (ProductSize $size) => $size->quantity > 0);
    }

    /**
     * Check if product is sold out
     */
    public function isSoldOut(): bool
    {
        return !$this->isAvailable();
    }

    /**
     * Is a discount currently running on this product?
     *
     * A sale counts as live when a sale price is set, it actually undercuts
     * the list price, and now falls inside the (optional) sale window.
     */
    public function isOnSale(): bool
    {
        if ($this->sale_price === null || (float) $this->sale_price <= 0) {
            return false;
        }

        if ((float) $this->sale_price >= (float) $this->price) {
            return false;
        }

        $now = now();

        if ($this->sale_starts_at && $now->lt($this->sale_starts_at)) {
            return false;
        }

        if ($this->sale_ends_at && $now->gt($this->sale_ends_at)) {
            return false;
        }

        return true;
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->isOnSale();
    }

    /**
     * The price the customer actually pays, in the product's own currency.
     * Everything that charges money must go through this, never `price`.
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->isOnSale() ? (float) $this->sale_price : (float) $this->price;
    }

    /**
     * Percentage off the list price, rounded to a whole number (0 when not on sale)
     */
    public function getDiscountPercentageAttribute(): int
    {
        if (!$this->isOnSale() || (float) $this->price <= 0) {
            return 0;
        }

        return (int) round(((float) $this->price - (float) $this->sale_price) / (float) $this->price * 100);
    }

    /**
     * Get the main product image
     */
    public function getMainImageAttribute(): ?ProductImage
    {
        return $this->images->first();
    }

    /**
     * Get formatted price: the amount actually charged (sale price when on sale)
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->formatPrice($this->effective_price);
    }

    /**
     * Get the formatted list price, for showing struck through beside a sale price
     */
    public function getFormattedOriginalPriceAttribute(): string
    {
        return $this->formatPrice((float) $this->price);
    }

    /**
     * Render an amount (given in this product's currency) in the visitor's currency
     */
    protected function formatPrice(float $amount): string
    {
        $displayCurrency = session('currency', 'EGP');
        $converted = $this->convertToCurrency($displayCurrency, $amount);
        return number_format($converted, 2) . " <span class=\"currency\">{$displayCurrency}</span>";
    }

    /**
     * Convert a price from the product currency to the target currency.
     *
     * @param  string      $targetCurrency  Currency to convert into.
     * @param  float|null  $amount          Amount to convert; defaults to the
     *                                      effective (sale-aware) price.
     */
    public function convertToCurrency(string $targetCurrency, ?float $amount = null): float
    {
        $amount ??= $this->effective_price;

        $rates = config('currencies.rates');
        $sourceRate = $rates[$this->currency] ?? 1;
        $targetRate = $rates[$targetCurrency] ?? 1;
        // Convert: price_in_target = price * (targetRate / sourceRate)
        $converted = $amount * ($targetRate / $sourceRate);
        return round($converted, 2);
    }

    /**
     * Get available sizes
     */
    public function getAvailableSizesAttribute(): \Illuminate\Support\Collection
    {
        if ($this->is_one_of_a_kind) {
            return collect(['One Size']);
        }

        return $this->sizes()->where('quantity', '>', 0)->get();
    }

    /**
     * Get the colours a customer can actually pick
     */
    public function getAvailableColorsAttribute(): \Illuminate\Support\Collection
    {
        $colors = $this->relationLoaded('colors') ? $this->colors : $this->colors()->get();

        return $colors->where('is_available', true)->values();
    }

    /**
     * Get name attribute (alias for title)
     */
    public function getNameAttribute(): string
    {
        return $this->title;
    }
}
