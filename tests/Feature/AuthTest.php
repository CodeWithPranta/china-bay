<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolesPermissionsSeeder;

beforeEach(function () {
    $this->seed([RolesPermissionsSeeder::class, AdminUserSeeder::class]);
});

test('admin can login with email and reaches dashboard', function () {
    $response = $this->post('/auth/login', [
        'login' => 'admin@chinabaybd.com',
        'password' => 'password',
    ]);

    $response->assertRedirect('/admin');
    $this->assertAuthenticated();
    $this->get('/admin')->assertStatus(200);
});

test('admin can login with phone', function () {
    $response = $this->post('/auth/login', [
        'login' => '+8801849415277',
        'password' => 'password',
    ]);

    $response->assertRedirect('/admin');
    $this->assertAuthenticated();
});

test('wrong password is rejected', function () {
    $response = $this->from('/auth/login')->post('/auth/login', [
        'login' => 'admin@chinabaybd.com',
        'password' => 'wrong',
    ]);

    $response->assertRedirect('/auth/login');
    $response->assertInvalid('login');
    $this->assertGuest();
});

test('guests cannot open admin pages', function () {
    $this->get('/admin')->assertRedirect('/auth/login');
    $this->get('/admin/settings')->assertRedirect('/auth/login');
});

test('customer without permission gets 403 on admin pages', function () {
    $user = User::factory()->create(['email' => 'buyer@example.com']);

    $this->actingAs($user)->get('/admin')->assertForbidden();
    $this->actingAs($user)->get('/admin/settings')->assertForbidden();
});

test('logout ends the session', function () {
    $user = User::firstWhere('email', 'admin@chinabaybd.com');

    $this->actingAs($user)->post('/auth/logout')->assertRedirect('/');
    $this->assertGuest();
});

test('guests cannot open customer dashboard pages', function () {
    $this->get('/account')->assertRedirect('/auth/login');
    $this->get('/account/orders')->assertRedirect('/auth/login');
    $this->get('/account/profile')->assertRedirect('/auth/login');
});

test('customer login lands on account dashboard', function () {
    $user = User::factory()->create(['phone' => '01712345678']);
    $user->assignRole('customer');

    $this->post('/auth/login', [
        'login' => '01712345678',
        'password' => 'password',
    ])->assertRedirect('/account');

    $this->assertAuthenticated();
    $this->get('/account')->assertOk()->assertSee(e($user->name), false);
});

test('logged-in users are bounced off login and register pages', function () {
    $user = User::factory()->create();
    $user->assignRole('customer');

    $this->actingAs($user)->get('/auth/login')->assertRedirect();
    $this->actingAs($user)->get('/auth/register')->assertRedirect();
});

test('customer can register with phone and password', function () {
    $response = $this->post('/auth/register', [
        'name' => 'Rahim Uddin',
        'phone' => '01712345678',
        'email' => 'rahim@example.com',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ]);

    $response->assertRedirect('/account');
    $this->assertAuthenticated();

    $user = User::firstWhere('phone', '01712345678');
    expect($user)->not->toBeNull()
        ->and($user->hasRole('customer'))->toBeTrue()
        ->and($user->referral_code)->not->toBeNull()
        ->and($user->loyalty_points)->toBe(20);

    $this->get('/account')->assertOk()->assertSee('Rahim Uddin', false);
});

test('registration rejects duplicate phone and bad password', function () {
    User::factory()->create(['phone' => '01712345678']);

    $this->from('/auth/register')->post('/auth/register', [
        'name' => 'Duplicate',
        'phone' => '01712345678',
        'password' => 'secret123',
        'password_confirmation' => 'secret123',
    ])->assertRedirect('/auth/register')->assertInvalid('phone');

    $this->from('/auth/register')->post('/auth/register', [
        'name' => 'Mismatch',
        'phone' => '01812345678',
        'password' => 'secret123',
        'password_confirmation' => 'different',
    ])->assertRedirect('/auth/register')->assertInvalid('password');
});
