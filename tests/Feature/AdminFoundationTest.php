<?php

use App\Models\Setting;
use App\Models\SiteLocale;
use App\Models\Translation;
use App\Models\User;
use App\Translation\TranslationCache;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\LocaleSeeder;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed([
        RolesPermissionsSeeder::class,
        AdminUserSeeder::class,
        LocaleSeeder::class,
        SettingsSeeder::class,
    ]);

    $this->actingAs(User::firstWhere('email', 'admin@chinabaybd.com'));
});

test('admin settings page renders livewire manager', function () {
    $response = $this->get('/admin/settings');

    $response->assertStatus(200);
    $response->assertSee('Save all settings', false);
});

test('admin languages page renders locale and translation managers', function () {
    $response = $this->get('/admin/languages');

    $response->assertStatus(200);
    $response->assertSee('Add a language', false);
});

test('admin roles and team pages render', function () {
    $this->get('/admin/roles')->assertStatus(200)->assertSee('Create role', false);
    $this->get('/admin/users')->assertStatus(200)->assertSee('Team &amp; customers', false);
});

test('seeded roles and permissions exist', function () {
    expect(Role::whereIn('name', ['super-admin', 'admin', 'manager', 'support'])->count())->toBe(4);
    expect(Permission::where('name', 'settings.manage')->exists())->toBeTrue();
});

test('db translation overrides lang files', function () {
    Translation::updateOrCreate(
        ['locale' => 'en', 'group' => 'shop', 'key' => 'cart'],
        ['value' => 'DB-Cart-Override']
    );

    expect(__('shop.cart', locale: 'en'))->toBe('DB-Cart-Override');

    Translation::where(['locale' => 'en', 'group' => 'shop', 'key' => 'cart'])->delete();
    TranslationCache::flush('en', 'shop');
});

test('settings helper caches and theme vars render', function () {
    Setting::set('theme.brand', '#123456', 'theme');

    expect(Setting::get('theme.brand'))->toBe('#123456');

    $this->get('/')->assertStatus(200)->assertSee('--color-brand: #123456', false);

    Setting::set('theme.brand', '#c8102e', 'theme');
});

test('locales drive switcher and default', function () {
    expect(SiteLocale::defaultCode())->toBe('bn');
    expect(SiteLocale::activeCodes())->toContain('bn', 'en', 'ar');
    expect(SiteLocale::directionOf('ar'))->toBe('rtl');
});

test('stale locale cache with model objects self-heals', function () {
    // Simulate a poisoned entry (e.g. models cached by an older revision).
    Cache::put('locales.active', [new stdClass]);

    $codes = SiteLocale::activeCodes();

    expect($codes)->toContain('bn', 'en', 'ar');
});
