<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = [
        'parent_id', 'name_en', 'name_bn', 'slug',
        'icon', 'image', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return BelongsTo<Category, $this> */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /** @return HasMany<Category, $this> */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    /** @return HasMany<Product, $this> */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** @return HasMany<CategoryTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function translation(?string $locale = null): ?CategoryTranslation
    {
        $locale ??= app()->getLocale();
        $loaded = $this->relationLoaded('translations')
            ? $this->translations
            : $this->translations()->get();

        return $loaded->firstWhere('locale', $locale)
            ?? $loaded->firstWhere('locale', SiteLocale::defaultCode());
    }

    /**
     * Name in the requested locale with fallback to the default
     * locale and finally the legacy name_en/name_bn columns.
     */
    public function localizedName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if ($this->translation($locale)?->name) {
            return $this->translation($locale)->name;
        }

        return match ($locale) {
            'bn' => $this->name_bn ?: $this->name_en,
            default => $this->name_en ?: $this->name_bn,
        };
    }

    /** @param Builder<$this> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @param Builder<$this> $query */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name_en');
    }
}
