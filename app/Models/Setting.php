<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Get a setting value by key with caching.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = Cache::remember("setting_{$key}", 3600, function () use ($key) {
            return static::where('key', $key)->value('value');
        });

        return $value !== null && $value !== '' ? $value : $default;
    }

    /**
     * Set/update a setting value and clear cache.
     */
    public static function set(string $key, mixed $value, string $group = 'general'): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => (string) $value,
                'group' => $group,
            ]
        );

        Cache::forget("setting_{$key}");

        return $setting;
    }

    /**
     * Helper to get active USD to KHR exchange rate.
     */
    public static function getExchangeRate(): float
    {
        $rate = static::get('usd_to_khr_rate', 4000);

        return (float) ($rate ?: 4000);
    }

    /**
     * Helper to set USD to KHR exchange rate.
     */
    public static function setExchangeRate(float|int|string $rate): self
    {
        return static::set('usd_to_khr_rate', $rate, 'currency');
    }
}
