<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Languages managed from the admin panel. Adding a row (+ cloning the
 * fallback strings into `translations`) is all it takes to add a language.
 */
class SiteLocale extends Model
{
    protected $table = 'locales';

    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'code', 'name', 'native_name', 'direction',
        'is_active', 'is_default', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /** @return array<int, static> */
    public static function active(): array
    {
        try {
            $rows = Cache::rememberForever('locales.active', fn () => static::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('code')
                ->get()
                ->map(fn (self $l) => $l->attributesToArray())
                ->all());
        } catch (\Throwable) {
            $rows = [];
        }

        // Self-heal: a stale cache entry may hold unserializable payloads
        // (e.g. model objects cached by an older revision). Plain arrays only.
        $healed = false;
        if (! is_array($rows) || array_filter($rows, fn ($r) => ! is_array($r))) {
            static::flushCache();
            $rows = [];
            $healed = true;
        }

        if ($rows === [] && $healed) {
            try {
                $rows = static::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->orderBy('code')
                    ->get()
                    ->map(fn (self $l) => $l->attributesToArray())
                    ->all();
            } catch (\Throwable) {
                return [];
            }
        }

        return static::hydrate($rows)->all();
    }

    /** @return array<int, string> */
    public static function activeCodes(): array
    {
        return array_map(fn (self $l) => $l->code, static::active());
    }

    public static function defaultCode(): ?string
    {
        foreach (static::active() as $locale) {
            if ($locale->is_default) {
                return $locale->code;
            }
        }

        return null;
    }

    public static function directionOf(string $code): string
    {
        foreach (static::active() as $locale) {
            if ($locale->code === $code) {
                return $locale->direction;
            }
        }

        return in_array($code, config('app.rtl_locales', ['ar']), true) ? 'rtl' : 'ltr';
    }

    public static function flushCache(): void
    {
        Cache::forget('locales.active');
    }

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
