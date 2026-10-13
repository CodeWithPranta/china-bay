<?php

use App\Http\Controllers\AiChatController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LocaleController;
use App\Support\Catalog;
use Illuminate\Support\Facades\Route;

// Bay AI concierge (guests + logged-in shoppers; throttled)
Route::post('/api/ai/chat', AiChatController::class)->name('ai.chat')->middleware('throttle:30,1');

// Locale switcher (session based, supports LTR + RTL)
Route::get('/locale/{locale}', LocaleController::class)->name('locale.switch');

// Storefront
Route::view('/', 'pages.home');
Route::view('/shop', 'pages.shop');
Route::view('/cart', 'pages.cart');
Route::view('/checkout', 'pages.checkout');
Route::view('/order-success', 'pages.order-success');
Route::view('/track-order', 'pages.track-order');
Route::view('/flash-sale', 'pages.flash-sale');

Route::get('/product/{slug}', function (string $slug) {
    $product = Catalog::findBySlug($slug);

    abort_if($product === null, 404);

    return view('pages.product', ['product' => $product]);
});

// Auth (customers + admins; login/register are guest-only)
Route::prefix('auth')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login')->middleware('guest');
    Route::post('/login', [LoginController::class, 'store'])->middleware(['guest', 'throttle:5,1']);
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/register', [RegisterController::class, 'show'])->name('register')->middleware('guest');
    Route::post('/register', [RegisterController::class, 'store'])->middleware(['guest', 'throttle:5,1']);
    Route::view('/verify', 'pages.auth.verify');
});
Route::redirect('/login', '/auth/login');
Route::redirect('/register', '/auth/register');

// Customer account (login required — guests bounce to /auth/login)
Route::prefix('account')->middleware('auth')->group(function () {
    Route::view('/', 'pages.account.dashboard');
    Route::view('/orders', 'pages.account.orders');
    Route::view('/orders/{id}', 'pages.account.order-detail');
    Route::view('/addresses', 'pages.account.addresses');
    Route::view('/wishlist', 'pages.account.wishlist');
    Route::view('/reviews', 'pages.account.reviews');
    Route::view('/coupons', 'pages.account.coupons');
    Route::view('/loyalty', 'pages.account.loyalty');
    Route::view('/notifications', 'pages.account.notifications');
    Route::view('/profile', 'pages.account.profile');
});

// Info pages
Route::view('/about', 'pages.about');
Route::view('/contact', 'pages.contact');
Route::view('/shipping', 'pages.shipping');
Route::view('/returns', 'pages.returns');
Route::view('/terms', 'pages.terms');
Route::view('/privacy', 'pages.privacy');

// Admin (Livewire-powered; static previews remain for catalog/orders until Phase 1)
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::view('/', 'pages.admin.dashboard')->middleware('permission:dashboard.view');
    Route::view('/orders', 'pages.admin.orders')->middleware('permission:orders.view');
    Route::view('/orders/{id}', 'pages.admin.order-detail')->middleware('permission:orders.view');
    Route::view('/products', 'pages.admin.products')->middleware('permission:products.view');
    Route::view('/pos', 'pages.admin.pos')->middleware('permission:pos.view');
    Route::view('/categories', 'pages.admin.categories')->middleware('permission:categories.manage');
    Route::view('/brands', 'pages.admin.brands')->middleware('permission:categories.manage');
    Route::view('/stock', 'pages.admin.stock')->middleware('permission:stock.manage');
    Route::view('/customers', 'pages.admin.customers')->middleware('permission:customers.view');
    Route::view('/coupons', 'pages.admin.coupons')->middleware('permission:coupons.manage');
    Route::view('/flash-sales', 'pages.admin.flash-sales')->middleware('permission:flash-sales.manage');
    Route::view('/reviews', 'pages.admin.reviews')->middleware('permission:reviews.moderate');
    Route::view('/settings', 'pages.admin.settings')->middleware('permission:settings.manage');
    Route::view('/languages', 'pages.admin.languages')->middleware('permission:languages.manage');
    Route::view('/roles', 'pages.admin.roles')->middleware('permission:roles.manage');
    Route::view('/users', 'pages.admin.users')->middleware('permission:users.view');
});
