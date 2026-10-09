<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Demo-shaped catalogue backed by the database, with ShopDemo as
 * fallback so fresh installs and empty states keep rendering.
 *
 * @phpstan-type Card array{id:int,slug:string,name:string,sub:string,price:int,old:int,emoji:string,image:?string,cat:string,rating:float,reviews:int,stock:int,off:int}
 */
class Catalog
{
    /** @var array<int, string> */
    private const BG = [
        'bg-sky-50', 'bg-cyan-50', 'bg-slate-100', 'bg-amber-50',
        'bg-pink-50', 'bg-yellow-50', 'bg-orange-50', 'bg-emerald-50',
    ];

    /**
     * @return array<int, array{slug:string,en:string,bn:string,emoji:string,bg:string}>
     */
    public static function categories(): array
    {
        try {
            $rows = Category::active()->ordered()->get();
        } catch (\Throwable) {
            return ShopDemo::categories();
        }

        if ($rows->isEmpty()) {
            return ShopDemo::categories();
        }

        return $rows->values()->map(fn (Category $c, int $i) => [
            'slug' => $c->slug,
            'en' => $c->name_en,
            'bn' => $c->name_bn ?? $c->name_en,
            'emoji' => $c->icon ?? '',
            'bg' => self::BG[$i % count(self::BG)],
        ])->all();
    }

    /**
     * @return array<int, Card>
     */
    public static function products(): array
    {
        try {
            $rows = Product::active()->with(['category', 'media'])->orderBy('id')->get();
        } catch (\Throwable) {
            return ShopDemo::products();
        }

        if ($rows->isEmpty()) {
            return ShopDemo::products();
        }

        return $rows->map(fn (Product $p) => $p->toCardArray())->all();
    }

    /**
     * @return array<int, Card>
     */
    public static function featured(int $take = 6): array
    {
        try {
            $rows = Product::active()->featured()->with(['category', 'media'])->orderBy('id')->take($take)->get();

            if ($rows->isEmpty()) {
                $rows = Product::active()->with(['category', 'media'])->orderBy('id')->take($take)->get();
            }

            if ($rows->isNotEmpty()) {
                return $rows->map(fn (Product $p) => $p->toCardArray())->all();
            }
        } catch (\Throwable) {
            // Fall through to demo.
        }

        return array_slice(ShopDemo::products(), 0, $take);
    }

    /**
     * @return Card|null
     */
    public static function findBySlug(string $slug): ?array
    {
        try {
            $product = Product::active()->where('slug', $slug)->with(['category', 'media'])->first();

            if ($product) {
                return $product->toCardArray();
            }
        } catch (\Throwable) {
            // Fall through to demo.
        }

        return collect(ShopDemo::products())->firstWhere('slug', $slug);
    }

    /** @return Collection<int, mixed> */
    public static function keyedById(): Collection
    {
        return collect(static::products())->mapWithKeys(fn ($p) => [$p['id'] => $p]);
    }
}
