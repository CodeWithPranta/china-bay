<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Support\ShopDemo;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    /**
     * Import the demo catalogue into real tables.
     * Re-runnable: matches on slug/sku and updates in place.
     */
    public function run(): void
    {
        $catIds = [];
        foreach (ShopDemo::categories() as $order => $c) {
            $cat = Category::updateOrCreate(
                ['slug' => $c['slug']],
                [
                    'name_en' => $c['en'],
                    'name_bn' => $c['bn'],
                    'icon' => $c['emoji'],
                    'sort_order' => $order,
                    'is_active' => true,
                ]
            );
            $catIds[$c['slug']] = $cat->id;
        }

        $featured = array_slice(array_column(ShopDemo::products(), 'id'), 0, 6);

        foreach (ShopDemo::products() as $p) {
            $product = Product::updateOrCreate(
                ['sku' => 'CB-'.$p['id']],
                [
                    'category_id' => $catIds[$p['cat']] ?? reset($catIds),
                    'name' => $p['name'],
                    'slug' => $p['slug'],
                    'subtitle' => $p['sub'],
                    'price' => $p['price'],
                    'old_price' => $p['old'],
                    'stock_qty' => $p['stock'],
                    'emoji' => $p['emoji'],
                    'status' => 'active',
                    'is_featured' => in_array($p['id'], $featured, true),
                    'rating_avg' => $p['rating'],
                    'reviews_count' => $p['reviews'],
                ]
            );

            ProductMedia::updateOrCreate(
                ['product_id' => $product->id, 'sort_order' => 0],
                [
                    'type' => 'image',
                    'url' => $p['image'],
                    'is_primary' => true,
                ]
            );
        }
    }
}
