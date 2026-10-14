<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\LocaleSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\SettingsSeeder;
use Livewire\Livewire;

function seedOrderProduct(int $stock = 10, int $price = 1000): Product
{
    $category = Category::create([
        'slug' => 'gadgets-'.uniqid(), 'name_en' => 'Gadgets', 'name_bn' => 'গ্যাজেট',
        'sort_order' => 0, 'is_active' => true,
    ]);

    return $category->products()->create([
        'name' => 'Orderable', 'slug' => 'orderable-'.uniqid(), 'sku' => 'CB-ORD-'.uniqid(),
        'price' => $price, 'old_price' => 1200, 'stock_qty' => $stock, 'status' => 'active',
    ]);
}

function checkoutPayload(Product $product, array $over = []): array
{
    return array_merge([
        'name' => 'Rahim Uddin',
        'phone' => '01711222333',
        'address' => 'House 12, Road 5, Dhanmondi',
        'district' => 'Dhaka',
        'thana' => 'Dhanmondi',
        'delivery_method' => 'inside',
        'payment_method' => 'cod',
        'items' => [['id' => $product->id, 'qty' => 2]],
    ], $over);
}

test('guest checkout creates order with server-side totals and decrements stock', function () {
    $product = seedOrderProduct(stock: 10, price: 1000);

    $response = $this->post(route('checkout.store'), checkoutPayload($product, ['coupon_code' => 'CHINABAY10']));

    $order = Order::first();
    expect($order)->not->toBeNull();
    expect($order->code)->toStartWith('CB-');
    expect((float) $order->subtotal)->toBe(2000.0);
    expect((float) $order->discount)->toBe(200.0);
    expect((float) $order->delivery_charge)->toBe(60.0);
    expect((float) $order->total)->toBe(1860.0);
    expect($order->status)->toBe('pending');
    expect($order->user_id)->toBeNull();
    expect($order->items->first()->qty)->toBe(2);

    $response->assertRedirect(route('order.success', ['code' => $order->code]));
    expect($product->fresh()->stock_qty)->toBe(8);
});

test('invalid coupon is ignored and outside-dhaka charge applies', function () {
    $product = seedOrderProduct(stock: 5, price: 500);

    $this->post(route('checkout.store'), checkoutPayload($product, [
        'coupon_code' => 'FAKE', 'district' => 'Chattogram', 'delivery_method' => 'outside',
    ]));

    $order = Order::first();
    expect((float) $order->discount)->toBe(0.0);
    expect($order->coupon_code)->toBeNull();
    expect((float) $order->delivery_charge)->toBe(120.0);
});

test('checkout validates phone and stock', function () {
    $product = seedOrderProduct(stock: 1);

    $this->post(route('checkout.store'), checkoutPayload($product, ['phone' => '123']))
        ->assertSessionHasErrors('phone');

    $this->post(route('checkout.store'), checkoutPayload($product, ['items' => [['id' => $product->id, 'qty' => 5]]]))
        ->assertSessionHasErrors('items');

    expect(Order::count())->toBe(0);
    expect($product->fresh()->stock_qty)->toBe(1);
});

test('checkout accepts form-encoded JSON items and voice-spaced phone', function () {
    $product = seedOrderProduct(stock: 10, price: 1000);

    // Browsers submit items as a JSON string via a hidden input, and voice
    // dictation inserts gaps between digits. Both must normalize server-side.
    $payload = checkoutPayload($product, [
        'phone' => '01 711-222 333',
        'items' => json_encode([['id' => $product->id, 'qty' => 2]]),
    ]);

    $response = $this->post(route('checkout.store'), $payload);

    $order = Order::first();
    expect($order)->not->toBeNull();
    expect($order->phone)->toBe('01711222333');
    $response->assertRedirect(route('order.success', ['code' => $order->code]));
});

test('checkout normalizes Bengali-digit phone numbers', function () {
    $product = seedOrderProduct(stock: 10, price: 1000);

    $payload = checkoutPayload($product, ['phone' => '০১৭১১২২২৩৩৩']);

    $this->post(route('checkout.store'), $payload);

    expect(Order::first()?->phone)->toBe('01711222333');
});

test('success page shows the placed order', function () {
    $product = seedOrderProduct();
    $this->post(route('checkout.store'), checkoutPayload($product));
    $order = Order::first();

    $this->get(route('order.success', ['code' => $order->code]))
        ->assertOk()
        ->assertSee($order->code, false)
        ->assertSee('2,060', false);
});

test('account orders are scoped to the owner', function () {
    $product = seedOrderProduct();
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $this->actingAs($owner)->post(route('checkout.store'), checkoutPayload($product));
    $order = Order::first();

    $this->actingAs($other)->get(route('account.orders.show', $order->code))->assertNotFound();
    $this->actingAs($owner)->get(route('account.orders.show', $order->code))->assertOk()->assertSee($order->code, false);
});

test('guests bounce to login on account orders', function () {
    $this->get(route('account.orders'))->assertRedirect('/auth/login');
});

test('owner can cancel pending order and stock is restored', function () {
    $product = seedOrderProduct(stock: 10);
    $owner = User::factory()->create();

    $this->actingAs($owner)->post(route('checkout.store'), checkoutPayload($product));
    $order = Order::first();

    $this->actingAs($owner)->post(route('account.orders.cancel', $order->code))->assertRedirect();

    expect($order->fresh()->status)->toBe('cancelled');
    expect($product->fresh()->stock_qty)->toBe(10);
});

test('shipped order cannot be cancelled', function () {
    $product = seedOrderProduct();
    $owner = User::factory()->create();

    $this->actingAs($owner)->post(route('checkout.store'), checkoutPayload($product));
    $order = Order::first();
    $order->update(['status' => 'shipped']);

    $this->actingAs($owner)->post(route('account.orders.cancel', $order->code))->assertStatus(422);
});

test('track-order finds order by code and phone', function () {
    $product = seedOrderProduct();
    $this->post(route('checkout.store'), checkoutPayload($product));
    $order = Order::first();

    $this->get(route('track', ['code' => $order->code, 'phone' => '01711222333']))
        ->assertOk()
        ->assertSee($order->code, false)
        ->assertSee('Placed', false);

    $this->get(route('track', ['code' => $order->code, 'phone' => '01999999999']))
        ->assertOk()
        ->assertSee('No order found', false);
});

test('admin advances order through the pipeline', function () {
    $this->seed([RolesPermissionsSeeder::class, AdminUserSeeder::class, LocaleSeeder::class, SettingsSeeder::class]);
    $this->actingAs(User::firstWhere('email', 'admin@chinabaybd.com'));

    $product = seedOrderProduct();
    $this->post(route('checkout.store'), checkoutPayload($product));
    $order = Order::first();

    $this->get('/admin/orders')->assertOk()->assertSee($order->code, false);

    Livewire::test('admin.orders-manager')
        ->call('advance', $order->id, 'confirmed')
        ->assertSet('toast', "{$order->code} → Confirmed ✓");

    expect($order->fresh()->status)->toBe('confirmed');

    Livewire::test('admin.orders-manager')
        ->call('cancel', $order->id);

    expect($order->fresh()->status)->toBe('cancelled');
    expect($product->fresh()->stock_qty)->toBe(10);
});
