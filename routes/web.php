<?php

use App\Support\ShopDemo;
use Illuminate\Support\Facades\Route;

// Storefront
Route::view('/', 'pages.home');
Route::view('/shop', 'pages.shop');
Route::view('/cart', 'pages.cart');
Route::view('/checkout', 'pages.checkout');
Route::view('/order-success', 'pages.order-success');
Route::view('/track-order', 'pages.track-order');
Route::view('/flash-sale', 'pages.flash-sale');

Route::get('/product/{slug}', function (string $slug) {
    $product = collect(ShopDemo::products())->firstWhere('slug', $slug) ?? ShopDemo::products()[0];

    return view('pages.product', ['product' => $product]);
});

// Auth (static design preview)
Route::prefix('auth')->group(function () {
    Route::view('/login', 'pages.auth.login');
    Route::view('/register', 'pages.auth.register');
    Route::view('/verify', 'pages.auth.verify');
});
Route::redirect('/login', '/auth/login');
Route::redirect('/register', '/auth/register');

// Customer account (static design preview)
Route::prefix('account')->group(function () {
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

// Admin (static design preview)
Route::prefix('admin')->group(function () {
    Route::view('/', 'pages.admin.dashboard');
    Route::view('/orders', 'pages.admin.orders');
    Route::view('/orders/{id}', 'pages.admin.order-detail');
    Route::view('/products', 'pages.admin.products');
    Route::view('/stock', 'pages.admin.stock');
    Route::view('/customers', 'pages.admin.customers');
    Route::view('/coupons', 'pages.admin.coupons');
    Route::view('/flash-sales', 'pages.admin.flash-sales');
    Route::view('/reviews', 'pages.admin.reviews');
    Route::view('/settings', 'pages.admin.settings');
});
