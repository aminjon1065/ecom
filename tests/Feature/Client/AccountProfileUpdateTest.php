<?php

use App\Models\User;

it('allows user to update account profile', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'phone' => '+992900000000',
    ]);

    $this->actingAs($user)->put(route('account.profile.update'), [
        'name' => 'New Name',
        'phone' => '+992911111111',
    ])->assertRedirect();

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->phone)->toBe('+992911111111');
});

it('requires name when updating account profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('account.profile.update'), [
        'name' => '',
        'phone' => '+992922222222',
    ])->assertSessionHasErrors('name');
});
