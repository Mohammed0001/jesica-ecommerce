<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CollectionImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Collection extends Model
{
    // database/factories/CollectionFactory.php exists but was unreachable
    // without this trait.
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'release_date',
        'visible',
        'image_path',
        'pdf_path',
    ];

    protected $casts = [
        'release_date' => 'date',
        'visible' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Collection $collection) {
            if (empty($collection->slug)) {
                $collection->slug = static::generateUniqueSlug($collection->title);
            }
        });

        static::updating(function (Collection $collection) {
            if (empty($collection->slug)) {
                $collection->slug = static::generateUniqueSlug($collection->title, $collection->id);
            }
        });
    }

    /**
     * Build a URL slug that is unique across the collections table.
     *
     * Same reasoning as products: two collections may share a title, and the
     * slug column is uniquely indexed.
     *
     * @param  string    $title     Title to slugify.
     * @param  int|null  $ignoreId  Collection to exclude (the one being updated).
     */
    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'collection';
        $slug = $base;
        $suffix = 2;

        while (
            static::query()
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
     * Get the products in this collection
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get the images associated with this collection
     */
    public function images(): HasMany
    {
        return $this->hasMany(CollectionImage::class)->orderBy('order');
    }

    /**
     * Get visible products in this collection
     */
    public function visibleProducts(): HasMany
    {
        return $this->products()->where('visible', true);
    }

    /**
     * Scope a query to only include visible collections
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('visible', true);
    }

    /**
     * Scope a query to only include released collections
     */
    public function scopeReleased(Builder $query): void
    {
        $query->where('release_date', '<=', now());
    }

    /**
     * Get the collection's image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        // Use storage URL for public disk (e.g., /storage/collections/xxx.jpg)
        return Storage::url($this->image_path);
    }

    /**
     * Get PDF URL attribute
     */
    public function getPdfUrlAttribute(): ?string
    {
        if (! $this->pdf_path) {
            return null;
        }

        return Storage::url($this->pdf_path);
    }

    /**
     * Get name attribute (alias for title)
     */
    public function getNameAttribute(): string
    {
        return $this->title;
    }
}
