<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsletterSubscriber extends Model
{
    use HasFactory;

    protected $table = 'newsletter_subscribers';
    protected $fillable = ['email', 'name', 'is_subscribed', 'unsubscribe_token'];

    protected $casts = [
        'is_subscribed' => 'boolean',
    ];

    public static function subscribe(string $email, ?string $name = null): self
    {
        $token = Str::random(32);
        return static::updateOrCreate(
            ['email' => $email],
            ['name' => $name, 'is_subscribed' => true, 'unsubscribe_token' => $token]
        );
    }

    public function unsubscribe(): void
    {
        $this->update(['is_subscribed' => false]);
    }
}
