<?php

use App\Ai\Agents\ShopConcierge;
use App\Ai\Tools\SearchProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Laravel\Ai\Tools\Request;

function seedChatCatalogue(): void
{
    $cat = Category::create(['name_en' => 'Fashion', 'slug' => 'fashion']);

    $rows = [
        ['Classic Cotton T-Shirt', 'classic-cotton-t-shirt', 850, 1000],
        ['Premium Polo T-Shirt', 'premium-polo-t-shirt', 950, 1200],
        ['Graphic Print T-Shirt', 'graphic-print-t-shirt', 799, 999],
        ['Cute Cat Toy', 'cute-cat-toy', 1499, 1999],
        ['Designer Silk Saree', 'designer-silk-saree', 3499, 4299],
    ];

    foreach ($rows as [$name, $slug, $price, $old]) {
        Product::create([
            'category_id' => $cat->id,
            'name' => $name,
            'slug' => $slug,
            'sku' => 'TEST-'.strtoupper($slug),
            'price' => $price,
            'old_price' => $old,
            'stock_qty' => 10,
            'status' => 'active',
        ]);
    }
}

test('chat requires a message', function () {
    $this->postJson(route('ai.chat'), ['message' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors('message');

    ShopConcierge::assertNeverPrompted();
});

test('chat suggests budget products with live catalogue cards', function () {
    seedChatCatalogue();
    ShopConcierge::fake(['Here are my top picks under ৳1,000:']);

    $response = $this->postJson(route('ai.chat'), ['message' => 'I need a t-shirt in 1000']);

    $response->assertOk()
        ->assertJsonPath('source', 'ai')
        ->assertJsonPath('reply', 'Here are my top picks under ৳1,000:');

    $products = $response->json('products');
    expect($products)->toHaveCount(3);
    foreach ($products as $product) {
        expect($product['price'])->toBeLessThanOrEqual(1000);
        expect($product)->toHaveKeys(['name', 'slug', 'price', 'url']);
    }

    ShopConcierge::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 't-shirt'));
});

test('chat passes the logged-in shopper name to the agent', function () {
    seedChatCatalogue();
    ShopConcierge::fake(['Hello! Here are some picks:']);

    $user = User::factory()->create(['name' => 'Arif Hossain']);

    $this->actingAs($user)->postJson(route('ai.chat'), ['message' => 'hi'])
        ->assertOk();

    ShopConcierge::assertPrompted(fn ($prompt) => str_contains($prompt->prompt, 'Arif Hossain'));
});

test('chat finds kids gifts through synonyms', function () {
    seedChatCatalogue();
    ShopConcierge::fake(['Great choice! Kids love these:']);

    $response = $this->postJson(route('ai.chat'), ['message' => 'Gift for kids']);

    $response->assertOk()->assertJsonPath('source', 'ai');

    $slugs = collect($response->json('products'))->pluck('slug')->all();
    expect($slugs)->toContain('cute-cat-toy');
});

test('search products tool respects the budget cap', function () {
    seedChatCatalogue();

    $result = json_decode((string) (new SearchProducts)->handle(new Request([
        'query' => 't-shirt',
        'max_price' => 1000,
    ])), true);

    expect($result)->toHaveCount(3);
    foreach ($result as $product) {
        expect($product['price'])->toBeLessThanOrEqual(1000);
    }
});
