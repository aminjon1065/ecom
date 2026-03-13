<?php

use App\Models\User;

/**
 * Smoke tests — visit key pages in a real browser and assert no JavaScript exceptions.
 *
 * assertNoJavascriptErrors() checks for runtime JS exceptions only (not console.log output),
 * so it is resilient to debug messages the app may emit in development mode.
 */
it('public pages load without javascript errors', function () {
    $pages = visit(['/', '/products', '/login', '/track-order']);

    $pages->assertNoJavascriptErrors();
});

it('home page renders', function () {
    $page = visit('/');

    $page->assertPathIs('/')
        ->assertNoJavascriptErrors();
});

it('product listing page renders', function () {
    $page = visit('/products');

    $page->assertPathIs('/products')
        ->assertNoJavascriptErrors();
});

it('login page renders', function () {
    $page = visit('/login');

    $page->assertSee('Вход в аккаунт')
        ->assertNoJavascriptErrors();
});

it('order tracking page renders', function () {
    $page = visit('/track-order');

    $page->assertPathIs('/track-order')
        ->assertNoJavascriptErrors();
});

it('authenticated user can view cart page', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit('/cart');

    $page->assertPathIs('/cart')
        ->assertNoJavascriptErrors();
});

it('guest user is redirected from cart to login', function () {
    $page = visit('/cart');

    // Should redirect to the login page
    $page->assertPathIs('/login')
        ->assertNoJavascriptErrors();
});
