<?php

namespace App\Models;

use App\Translation\TranslationCache;
use Illuminate\Database\Eloquent\Model;

/**
 * Per-locale UI strings. DB rows override lang files with the same key.
 */
class Translation extends Model
{
    protected $fillable = ['locale', 'group', 'key', 'value'];

    protected static function booted(): void
    {
        static::saved(fn (self $t) => TranslationCache::flush($t->locale, $t->group));
        static::deleted(fn (self $t) => TranslationCache::flush($t->locale, $t->group));
    }
}
