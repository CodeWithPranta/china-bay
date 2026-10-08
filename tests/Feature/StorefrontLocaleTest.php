<?php

test('hero slider has no prev next arrows but keeps dots', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertDontSee('prevSlide', false);
    $response->assertDontSee('nextSlide', false);
    $response->assertDontSee('Previous slide', false);
    $response->assertDontSee('Next slide', false);
    $response->assertSee('id="dots"', false);
    $response->assertSee('id="slides"', false);
});

test('storefront has a language switcher', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee(route('locale.switch', 'en'), false);
    $response->assertSee(route('locale.switch', 'bn'), false);
    $response->assertSee(route('locale.switch', 'ar'), false);
});

test('locale switch stores locale and renders rtl for arabic', function () {
    $response = $this->get('/locale/ar');

    $response->assertRedirect();
    $this->assertSame('ar', session('locale'));

    $rtl = $this->withSession(['locale' => 'ar'])->get('/');

    $rtl->assertStatus(200);
    $rtl->assertSee('dir="rtl"', false);
    $rtl->assertSee('lang="ar"', false);
});

test('locale switch renders ltr for english and bengali', function () {
    $en = $this->withSession(['locale' => 'en'])->get('/');

    $en->assertStatus(200);
    $en->assertSee('dir="ltr"', false);

    $bn = $this->withSession(['locale' => 'bn'])->get('/');

    $bn->assertStatus(200);
    $bn->assertSee('dir="ltr"', false);
    $bn->assertSee('lang="bn"', false);
});

test('invalid locale returns 404', function () {
    $this->get('/locale/xx')->assertStatus(404);
});

test('bangla is the default language', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('lang="bn"', false);
    $response->assertSee('dir="ltr"', false);
});

test('storefront uses brand logo and favicon', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
    $response->assertSee('china-bay-logo.png', false);
    $response->assertSee('favicon.ico', false);
    $response->assertSee('apple-touch-icon.png', false);
});
