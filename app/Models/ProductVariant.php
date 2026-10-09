<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'name', 'sku', 'price_override',
        'stock_qty', 'attributes', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price_override' => 'decimal:2',
            'attributes' => 'array',
        ];
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return HasMany<ProductMedia, $this> */
    public function media(): HasMany
    {
        return $this->hasMany(ProductMedia::class, 'variant_id');
    }
}
