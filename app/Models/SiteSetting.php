<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    use HasFactory;

    protected $table = 'site_settings';
    protected $fillable = ['key', 'value'];

    public $timestamps = true;

    public static function get(string $key, $default = null)
    {
        $cacheKey = 'site_setting_' . $key;
        return Cache::remember($cacheKey, 300, function () use ($key, $default) {
            $row = static::where('key', $key)->first();
            if (!$row) return $default;
            return $row->value;
        });
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string)$value]);
        Cache::forget('site_setting_' . $key);
    }
}
