<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Collection extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'release_date',
        'visible',
        'image_path',
    ];

    protected $casts = [
        'release_date' => 'date',
        'visible' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Collection $collection) {
            if (empty($collection->slug)) {
                $collection->slug = Str::slug($collection->title);
            }
        });

        static::updating(function (Collection $collection) {
            if ($collection->isDirty('title') && empty($collection->slug)) {
                $collection->slug = Str::slug($collection->title);
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
     * Get the products in this collection
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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
        return $this->image_path ? asset('images/' . $this->image_path) : null;
    }

    /**
     * Get name attribute (alias for title)
     */
    public function getNameAttribute(): string
    {
        return $this->title;
    }
}
