<?php

use Inertia\Testing\AssertableInertia as Assert;

it('renders the delivery information page', function () {
    $response = $this->get(route('info.delivery'));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/info/show')
        ->where('title', 'Доставка')
        ->has('sections', 3)
    );
});

it('renders the payment information page', function () {
    $response = $this->get(route('info.payment'));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/info/show')
        ->where('title', 'Оплата')
        ->has('sections', 3)
    );
});

it('renders the returns information page', function () {
    $response = $this->get(route('info.returns'));

    $response->assertSuccessful();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('client/info/show')
        ->where('title', 'Возврат')
        ->has('sections', 3)
    );
});
