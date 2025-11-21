<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get all users with this role
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if this is an admin role
     */
    public function isAdmin(): bool
    {
        return $this->name === 'ADMIN';
    }

    /**
     * Check if this is a client role
     */
    public function isClient(): bool
    {
        return $this->name === 'CLIENT';
    }
}
