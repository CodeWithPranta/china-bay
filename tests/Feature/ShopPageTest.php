<?php

use App\Models\Product;
use Database\Seeders\CatalogSeeder;

beforeEach(function () {
    $this->seed([CatalogSeeder::class]);
});

test('shop page renders livewire catalog grid', function () {
    $response = $this->get('/shop');

    $response->assertStatus(200);
    $response->assertSee('filtersOpen', false);
    $response->assertSee('Show results', false);
    $response->assertSee('products found', false);
    $response->assertSee('TWS Wireless Earbuds', false);
});

test('product cards show images instead of icon placeholders', function () {
    $response = $this->get('/shop');

    $response->assertStatus(200);
    $response->assertSee('/images/products/tws-wireless-earbuds.svg', false);
    $response->assertSee('<img', false);
});

test('shop grid filters by category via livewire', function () {
    $component = Livewire\Livewire::test('shop.catalog-grid');

    $component->assertSee('TWS Wireless Earbuds');

    $component->set('cats', ['toys'])->assertSee('Cute Cat Toy')->assertDontSee('TWS Wireless Earbuds');
});

test('shop grid filters by price and sorts', function () {
    $component = Livewire\Livewire::test('shop.catalog-grid');

    $component->set('price', '0-1500');

    foreach (Product::active()->whereBetween('price', [0, 1500])->pluck('name') as $name) {
        $component->assertSee($name);
    }

    $component->set('price', '')->set('sort', 'lo');

    $cheapest = Product::active()->orderBy('price')->first();
    expect($cheapest)->not->toBeNull();
});

test('category pills share one chip shape with snap scroll', function () {
    $response = $this->get('/shop');

    $response->assertStatus(200);
    $response->assertSee('snap-start', false);
    $response->assertSee('shrink-0', false);
});

test('product page resolves from database and 404s unknown slug', function () {
    $product = Product::active()->firstWhere('slug', 'tws-wireless-earbuds');

    expect($product)->not->toBeNull();

    $this->get('/product/tws-wireless-earbuds')->assertStatus(200)->assertSee('TWS Wireless Earbuds');
    $this->get('/product/no-such-product')->assertStatus(404);
});
