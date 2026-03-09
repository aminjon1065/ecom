<?php

it('returns healthy status when db and cache are available', function () {
    $this->getJson(route('health'))
        ->assertOk()
        ->assertJson([
            'status' => 'ok',
            'db' => true,
            'cache' => true,
        ]);
});
