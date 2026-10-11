<?php

namespace App\Support;

/**
 * Static demo catalogue for the design-review phase.
 *
 * Later replace calls with Eloquent models without changing Blade component APIs:
 * product = [id, slug, name, sub, price, old, emoji, cat, rating, reviews, stock, ...]
 *
 * @phpstan-type Product array{id:int,slug:string,name:string,sub:string,price:int,old:int,emoji:string,cat:string,rating:float,reviews:int,stock:int}
 * @phpstan-type Category array{slug:string,en:string,bn:string,emoji:string,bg:string}
 */
class ShopDemo
{
    /**
     * @return array<int, Category>
     */
    public static function categories(): array
    {
        return [
            ['slug' => 'electronics', 'en' => 'Electronics', 'bn' => 'ইলেকট্রনিক্স', 'emoji' => '📱', 'bg' => 'bg-sky-50'],
            ['slug' => 'gadgets', 'en' => 'Gadgets', 'bn' => 'গ্যাজেট', 'emoji' => '🎧', 'bg' => 'bg-cyan-50'],
            ['slug' => 'appliances', 'en' => 'Home Appliances', 'bn' => 'হোম অ্যাপ্লায়েন্স', 'emoji' => '🍳', 'bg' => 'bg-slate-100'],
            ['slug' => 'fashion', 'en' => 'Fashion & Lifestyle', 'bn' => 'ফ্যাশন ও লাইফস্টাইল', 'emoji' => '👜', 'bg' => 'bg-amber-50'],
            ['slug' => 'beauty', 'en' => 'Beauty & Personal Care', 'bn' => 'বিউটি ও পার্সোনাল কেয়ার', 'emoji' => '💄', 'bg' => 'bg-pink-50'],
            ['slug' => 'decor', 'en' => 'Home Decor', 'bn' => 'হোম ডেকোর', 'emoji' => '🛋️', 'bg' => 'bg-yellow-50'],
            ['slug' => 'toys', 'en' => 'Toys & Kids', 'bn' => 'টয়স ও কিডস', 'emoji' => '🧸', 'bg' => 'bg-orange-50'],
            ['slug' => 'kitchen', 'en' => 'Kitchen & Dining', 'bn' => 'কিচেন ও ডাইনিং', 'emoji' => '🍲', 'bg' => 'bg-slate-100'],
        ];
    }

    /**
     * @return array<int, Product>
     */
    public static function products(): array
    {
        $rows = [
            [1, 'tws-wireless-earbuds', 'TWS Wireless Earbuds', 'Bluetooth 5.3', 1999, 2499, '🎧', 'gadgets', 4.8, 124, 42],
            [2, 'smart-watch-fit', 'Smart Watch', 'Fitness Tracker', 2999, 3499, '⌚', 'electronics', 4.7, 96, 35],
            [3, 'air-fryer-55l', 'Air Fryer 5.5L', 'Healthy Cooking', 7399, 8999, '🍟', 'appliances', 4.9, 86, 12],
            [4, 'wireless-headphones', 'Wireless Headphones', 'Noise Cancelling', 2649, 2999, '🎧', 'gadgets', 4.6, 75, 28],
            [5, 'power-bank-20000', 'Power Bank 20000mAh', 'Fast Charging', 1619, 1799, '🔋', 'electronics', 4.5, 54, 60],
            [6, 'cute-cat-toy', 'Cute Cat Toy', 'With Sound', 1499, 1999, '🐱', 'toys', 4.9, 32, 80],
            [7, 'ceramic-cookware-set', 'Ceramic Cookware Set', 'Non-stick 5pc', 4599, 5499, '🍲', 'kitchen', 4.7, 41, 22],
            [8, 'led-desk-lamp', 'LED Desk Lamp', 'Eye care', 1299, 1699, '💡', 'decor', 4.4, 29, 55],
            [9, 'vitamin-c-serum', 'Vitamin C Serum', '30ml brightening', 899, 1199, '🧴', 'beauty', 4.6, 210, 90],
            [10, 'travel-backpack', 'Travel Backpack', 'Waterproof 40L', 2199, 2799, '🎒', 'fashion', 4.7, 63, 33],
            [11, 'robot-vacuum-mini', 'Robot Vacuum Mini', 'Smart mapping', 12999, 14999, '🤖', 'appliances', 4.8, 18, 8],
            [12, 'silk-saree-premium', 'Silk Saree Premium', 'With blouse pc', 3499, 4299, '🥻', 'fashion', 4.9, 77, 15],
        ];

        return array_map(fn ($r) => [
            'id' => $r[0], 'slug' => $r[1], 'name' => $r[2], 'sub' => $r[3],
            'price' => $r[4], 'old' => $r[5], 'emoji' => $r[6], 'cat' => $r[7],
            'rating' => $r[8], 'reviews' => $r[9], 'stock' => $r[10],
            'off' => (int) round((1 - $r[4] / $r[5]) * 100),
            'image' => '/images/products/'.$r[1].'.svg',
        ], $rows);
    }

    /**
     * @return array<int, array{tag:string,h1:string,h2:string,p:string,emoji:string,grad:string,cat:string}>
     */
    public static function slides(): array
    {
        return [
            ['tag' => '🇨🇳 China Imported Products', 'h1' => 'বিশ্বের সেরা পণ্য', 'h2' => 'এখন আপনার কাছে', 'p' => 'ইলেকট্রনিক্স, গ্যাজেট, হোম অ্যাপ্লায়েন্স, লাইফস্টাইল প্রোডাক্টস এবং আরও অনেক কিছু – সবই এক ছাদের নিচে।', 'emoji' => '🐼', 'grad' => 'from-red-600 to-red-800', 'cat' => 'all'],
            ['tag' => '🔥 Hot Deals', 'h1' => 'স্মার্ট গ্যাজেটে', 'h2' => 'বিশাল ছাড়', 'p' => 'সেরা দামে স্মার্ট ওয়াচ, ইয়ারবাড এবং পাওয়ার ব্যাংক – সীমিত সময়ের জন্য।', 'emoji' => '⌚', 'grad' => 'from-slate-700 to-slate-900', 'cat' => 'gadgets'],
            ['tag' => '🧸 Kids Corner', 'h1' => 'ছোটদের জন্য', 'h2' => 'মজার খেলনা', 'p' => 'আপনার ছোট্ট সোনার জন্য আদরের সব খেলনা এখন হাতের নাগালে।', 'emoji' => '🐱', 'grad' => 'from-pink-500 to-rose-600', 'cat' => 'toys'],
        ];
    }

    /**
     * @return array<int, array{district:string,charge_inside:int,charge_outside:int}>
     */
    public static function deliveryZones(): array
    {
        return [
            ['district' => 'Dhaka', 'charge_inside' => 60, 'charge_outside' => 100],
            ['district' => 'Chattogram', 'charge_inside' => 80, 'charge_outside' => 120],
            ['district' => 'Sylhet', 'charge_inside' => 90, 'charge_outside' => 130],
            ['district' => 'Rajshahi', 'charge_inside' => 90, 'charge_outside' => 130],
            ['district' => 'Khulna', 'charge_inside' => 90, 'charge_outside' => 130],
            ['district' => 'Barishal', 'charge_inside' => 100, 'charge_outside' => 140],
            ['district' => 'Rangpur', 'charge_inside' => 100, 'charge_outside' => 140],
            ['district' => 'Mymensingh', 'charge_inside' => 90, 'charge_outside' => 130],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function nav(): array
    {
        return ['Home', 'Shop', 'New Arrivals', 'Deals', 'Track Order', 'About Us', 'Contact'];
    }
}
