<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Admin-editable key/value store. Everything the admin can change
 * (site name, theme colors, timezone, payments, marketing keys)
 * lives here instead of .env / hardcoded Blade.
 */
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value', 'group'];

    public static function cacheKey(): string
    {
        return 'settings.all';
    }

    /** @return array<string, string|null> */
    public static function allCached(): array
    {
        try {
            return Cache::rememberForever(static::cacheKey(), fn () => static::pluck('value', 'key')->all());
        } catch (\Throwable) {
            return [];
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::allCached()[$key] ?? $default;
    }

    public static function set(string $key, ?string $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
        static::flushCache();
    }

    public static function flushCache(): void
    {
        Cache::forget(static::cacheKey());
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }
}
