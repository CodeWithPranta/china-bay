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
