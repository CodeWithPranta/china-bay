<?php

namespace App\Translation;

use App\Models\Translation;
use Illuminate\Support\Facades\Cache;

/**
 * Cached DB translation overrides per locale+group. Safe to call before
 * migrations run (returns [] when the table is missing).
 */
class TranslationCache
{
    public static function key(string $locale, string $group): string
    {
        return "translations.{$locale}.{$group}";
    }

    /** @return array<string, string> */
    public static function overrides(string $locale, string $group = '*'): array
    {
        try {
            return Cache::rememberForever(static::key($locale, $group), fn () => Translation::query()
                ->where('locale', $locale)
                ->where('group', $group)
                ->whereNotNull('value')
                ->pluck('value', 'key')
                ->all());
        } catch (\Throwable) {
            return [];
        }
    }

    public static function flush(string $locale, string $group = '*'): void
    {
        Cache::forget(static::key($locale, $group));
    }

    public static function flushAll(): void
    {
        foreach (Translation::query()->select('locale', 'group')->distinct()->get() as $row) {
            static::flush($row->locale, $row->group);
        }
    }
}
