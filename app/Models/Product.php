<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vendor_id', 'category_id', 'brand_id', 'name', 'slug', 'subtitle',
        'description_en', 'description_bn', 'price', 'old_price', 'cost',
        'import_cost', 'transport_cost', 'packaging_cost', 'ads_cost', 'other_cost',
        'sku', 'barcode', 'stock_qty', 'low_stock_at', 'emoji',
        'status', 'is_featured', 'rating_avg', 'reviews_count', 'sold_count',
        'seo_title', 'seo_description', 'seo_keywords',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'old_price' => 'decimal:2',
            'cost' => 'decimal:2',
            'import_cost' => 'decimal:2',
            'transport_cost' => 'decimal:2',
            'packaging_cost' => 'decimal:2',
            'ads_cost' => 'decimal:2',
            'other_cost' => 'decimal:2',
            'is_featured' => 'boolean',
            'rating_avg' => 'decimal:2',
        ];
    }

    /**
     * Landed cost = sum of the adjustable cost components.
     */
    public function totalCost(): float
    {
        return (float) ($this->import_cost ?? 0)
            + (float) ($this->transport_cost ?? 0)
            + (float) ($this->packaging_cost ?? 0)
            + (float) ($this->ads_cost ?? 0)
            + (float) ($this->other_cost ?? 0);
    }

    /**
     * Expected profit per unit at the current selling price.
     */
    public function unitProfit(): float
    {
        return (float) $this->price - $this->totalCost();
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return BelongsTo<Brand, $this> */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /** @return HasMany<ProductVariant, $this> */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /** @return HasMany<ProductMedia, $this> */
    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class)->orderBy('sort_order');
    }

    /** @return HasMany<ProductTranslation, $this> */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function translation(?string $locale = null): ?ProductTranslation
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
     * locale and finally the canonical name column.
     */
    public function localizedName(?string $locale = null): string
    {
        return $this->translation($locale)?->name ?: $this->name;
    }

    /**
     * Description in the requested locale with fallback to the default
     * locale and finally the legacy description_en/bn columns.
     */
    public function localizedDescription(?string $locale = null): ?string
    {
        $locale ??= app()->getLocale();

        if ($this->translation($locale)?->description) {
            return $this->translation($locale)->description;
        }

        return match ($locale) {
            'bn' => $this->description_bn ?: $this->description_en,
            default => $this->description_en ?: $this->description_bn,
        };
    }

    /** @param Builder<$this> $query */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /** @param Builder<$this> $query */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /** @param Builder<$this> $query */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock_qty', '>', 0);
    }

    public function discountPercent(): int
    {
        if (! $this->old_price || $this->old_price <= $this->price) {
            return 0;
        }

        return (int) round((1 - $this->price / $this->old_price) * 100);
    }

    public function primaryImage(): ?string
    {
        return $this->media->where('type', 'image')->sortBy('sort_order')->first()?->url;
    }

    /**
     * Demo-shaped array for product-card + client scripts
     * (data-add / data-wish / CB_PRODUCTS hooks stay unchanged).
     *
     * @return array<string, mixed>
     */
    public function toCardArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'sub' => $this->subtitle ?? '',
            'price' => (int) $this->price,
            'old' => (int) ($this->old_price ?? $this->price),
            'emoji' => $this->emoji ?? '',
            'image' => $this->primaryImage(),
            'cat' => $this->category?->slug ?? '',
            'rating' => (float) $this->rating_avg,
            'reviews' => $this->reviews_count,
            'stock' => $this->stock_qty,
            'off' => $this->discountPercent(),
        ];
    }
}
