<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CollectionImage extends Model
{
    protected $fillable = [ 'collection_id', 'path', 'alt_text', 'order' ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function getUrlAttribute(): ?string
    {
        if (!$this->path) return null;
        if (Storage::disk('public')->exists($this->path)) {
            return Storage::url($this->path);
        }
        return asset('images/picsum/600x800-1-0.jpg');
    }
}
