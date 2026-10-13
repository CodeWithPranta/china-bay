<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\LocaleSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed([
        RolesPermissionsSeeder::class,
        AdminUserSeeder::class,
        LocaleSeeder::class,
        SettingsSeeder::class,
    ]);

    $this->actingAs(User::firstWhere('email', 'admin@chinabaybd.com'));
});

function seedCatalogCategory(): Category
{
    $category = Category::create([
        'slug' => 'gadgets',
        'name_en' => 'Gadgets',
        'name_bn' => 'গ্যাজেট',
        'sort_order' => 0,
        'is_active' => true,
    ]);

    $category->translations()->create(['locale' => 'bn', 'name' => 'গ্যাজেট']);
    $category->translations()->create(['locale' => 'en', 'name' => 'Gadgets']);

    return $category;
}

test('products page renders the manager', function () {
    $this->get('/admin/products')->assertStatus(200)->assertSee('+ Add product', false);
});

test('default locale name is mandatory when creating a product', function () {
    seedCatalogCategory();

    Livewire::test('admin.products-manager')
        ->set('category_id', (string) Category::first()->id)
        ->set('price', '999')
        ->set('sku', 'TEST-1')
        ->set('names.en', 'English only')
        ->call('save')
        ->assertHasErrors(['names.bn']);

    expect(Product::count())->toBe(0);
});

test('product create stores translations for every filled locale', function () {
    $category = seedCatalogCategory();

    Livewire::test('admin.products-manager')
        ->set('category_id', (string) $category->id)
        ->set('price', '850')
        ->set('old_price', '1000')
        ->set('sku', 'TEST-TSHIRT')
        ->set('stock_qty', 25)
        ->set('names.bn', 'কটন টি-শার্ট')
        ->set('names.en', 'Cotton T-Shirt')
        ->set('descriptions.bn', 'বিবরণ')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('toast', 'Product created ✓');

    $product = Product::firstWhere('sku', 'TEST-TSHIRT');
    expect($product)->not->toBeNull();
    expect($product->name)->toBe('কটন টি-শার্ট');
    expect($product->translations->pluck('name', 'locale')->all())->toBe([
        'bn' => 'কটন টি-শার্ট',
        'en' => 'Cotton T-Shirt',
    ]);
    expect($product->localizedName('ar'))->toBe('কটন টি-শার্ট');
});

test('product image upload stores media with primary flag', function () {
    Storage::fake('public');
    $category = seedCatalogCategory();

    Livewire::test('admin.products-manager')
        ->set('category_id', (string) $category->id)
        ->set('price', '500')
        ->set('sku', 'TEST-IMG')
        ->set('names.bn', 'ছবি পণ্য')
        ->set('photos', [UploadedFile::fake()->image('photo.jpg')])
        ->call('save')
        ->assertHasNoErrors();

    $media = ProductMedia::first();
    expect($media)->not->toBeNull();
    expect($media->is_primary)->toBeTrue();
    Storage::disk('public')->assertExists(str_replace('/storage/', '', $media->url));
});

test('category create requires default name and blocks delete with products', function () {
    Livewire::test('admin.categories-manager')
        ->set('names.en', 'English only')
        ->call('save')
        ->assertHasErrors(['names.bn']);

    Livewire::test('admin.categories-manager')
        ->set('names.bn', 'ইলেকট্রনিক্স')
        ->set('names.en', 'Electronics')
        ->call('save')
        ->assertHasNoErrors();

    $category = Category::firstWhere('slug', 'ইলেকট্রনিক্স') ?? Category::where('name_en', 'Electronics')->first();
    expect($category)->not->toBeNull();
    expect($category->localizedName('bn'))->toBe('ইলেকট্রনিক্স');

    $category->products()->create([
        'name' => 'Demo', 'slug' => 'demo', 'sku' => 'CB-DEMO',
        'price' => 100, 'stock_qty' => 1, 'status' => 'active',
    ]);

    Livewire::test('admin.categories-manager')
        ->call('delete', $category->id)
        ->assertSet('toast', 'Cannot delete: category has products.');

    expect(Category::find($category->id))->not->toBeNull();
});

test('brand create with logo and toggle works', function () {
    Storage::fake('public');

    Livewire::test('admin.brands-manager')
        ->set('name', 'Xiaomi')
        ->set('logo', UploadedFile::fake()->image('logo.png'))
        ->call('save')
        ->assertHasNoErrors();

    $brand = Brand::firstWhere('name', 'Xiaomi');
    expect($brand)->not->toBeNull();
    expect($brand->slug)->toBe('xiaomi');
    Storage::disk('public')->assertExists(str_replace('/storage/', '', $brand->logo));

    Livewire::test('admin.brands-manager')
        ->call('toggleActive', $brand->id);

    expect($brand->fresh()->is_active)->toBeFalse();
});

test('stock manager persists quantity adjustments', function () {
    $category = seedCatalogCategory();
    $product = $category->products()->create([
        'name' => 'Stocked', 'slug' => 'stocked', 'sku' => 'CB-STOCK',
        'price' => 100, 'stock_qty' => 3, 'status' => 'active',
    ]);

    Livewire::test('admin.stock-manager')
        ->set("qty.{$product->id}", 40)
        ->call('saveProduct', $product->id)
        ->assertSet('toast', 'Stocked: stock set to 40 ✓');

    expect($product->fresh()->stock_qty)->toBe(40);
});

test('categories and brands pages render their managers', function () {
    $this->get('/admin/categories')->assertStatus(200)->assertSee('+ Add category', false);
    $this->get('/admin/brands')->assertStatus(200)->assertSee('+ Add brand', false);
});

test('sidebar links categories and brands for managers', function () {
    $this->get('/admin/products')->assertStatus(200)
        ->assertSee('/admin/categories', false)
        ->assertSee('/admin/brands', false);
});

test('product cost breakdown auto-totals into cost', function () {
    $category = seedCatalogCategory();

    Livewire::test('admin.products-manager')
        ->set('category_id', (string) $category->id)
        ->set('price', '1000')
        ->set('sku', 'TEST-COST')
        ->set('names.bn', 'খরচ পণ্য')
        ->set('import_cost', '500')
        ->set('transport_cost', '60')
        ->set('packaging_cost', '20')
        ->set('ads_cost', '100')
        ->set('other_cost', '10')
        ->call('save')
        ->assertHasNoErrors();

    $product = Product::firstWhere('sku', 'TEST-COST');
    expect((float) $product->cost)->toBe(690.0);
    expect($product->totalCost())->toBe(690.0);
    expect($product->unitProfit())->toBe(310.0);
});

test('pos page renders and completes a sale with stock decrement', function () {
    $this->get('/admin/pos')->assertStatus(200)->assertSee('Current sale', false);

    $category = seedCatalogCategory();
    $product = $category->products()->create([
        'name' => 'POS Item', 'slug' => 'pos-item', 'sku' => 'CB-POS',
        'price' => 500, 'stock_qty' => 10, 'status' => 'active',
        'import_cost' => 300,
    ]);

    $component = Livewire::test('admin.pos-manager')
        ->call('addToCart', $product->id)
        ->set('paid', 500)
        ->call('complete')
        ->assertHasNoErrors();

    $sale = Sale::first();
    expect($sale)->not->toBeNull();
    expect((float) $sale->total)->toBe(500.0);
    expect($sale->items->first()->unit_cost)->not->toBeNull();
    expect($product->fresh()->stock_qty)->toBe(9);
    expect($component->get('receiptId'))->toBe($sale->id);
});

test('pos void restores stock', function () {
    $category = seedCatalogCategory();
    $product = $category->products()->create([
        'name' => 'Void Item', 'slug' => 'void-item', 'sku' => 'CB-VOID',
        'price' => 200, 'stock_qty' => 5, 'status' => 'active',
    ]);

    Livewire::test('admin.pos-manager')
        ->call('addToCart', $product->id)
        ->set('paid', 200)
        ->call('complete');

    $sale = Sale::first();

    Livewire::test('admin.pos-manager')->call('void', $sale->id);

    expect($sale->fresh()->status)->toBe('voided');
    expect($product->fresh()->stock_qty)->toBe(5);
});

test('pos requires sell permission for completion', function () {
    $user = User::factory()->create();
    $user->assignRole('support');
    // Support has pos.sell by default; revoke to test the gate.
    $user->removeRole('support');

    $this->actingAs($user)->get('/admin/pos')->assertForbidden();
});

test('customers page lists real users', function () {
    $this->get('/admin/customers')->assertStatus(200)->assertSee('admin@chinabaybd.com', false);
});

test('stock page renders the manager', function () {
    $this->get('/admin/stock')->assertStatus(200)->assertSee('Low stock only', false);
});
