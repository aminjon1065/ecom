<?php

use App\Models\User;

function generateTelegramHash(array $data, string $botToken): string
{
    ksort($data);

    $parts = [];
    foreach ($data as $key => $value) {
        if ($value !== null && $value !== '') {
            $parts[] = "$key=$value";
        }
    }

    $dataCheckString = implode("\n", $parts);
    $secretKey = hash('sha256', $botToken, true);

    return hash_hmac('sha256', $dataCheckString, $secretKey);
}

it('logs in existing user with valid telegram hash', function () {
    $botToken = 'test-bot-token-12345';
    config(['services.telegram.bot_token' => $botToken]);

    $user = User::factory()->create([
        'telegram_id' => '999888',
        'telegram_username' => 'testuser',
    ]);

    $data = [
        'id' => '999888',
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'testuser',
        'auth_date' => (string) time(),
    ];

    $hash = generateTelegramHash($data, $botToken);
    $data['hash'] = $hash;

    $this->post(route('auth.telegram.callback'), $data)
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

it('creates new user when telegram id not found', function () {
    \Spatie\Permission\Models\Role::query()->firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

    $botToken = 'test-bot-token-12345';
    config(['services.telegram.bot_token' => $botToken]);

    $data = [
        'id' => '111222',
        'first_name' => 'New',
        'last_name' => 'Person',
        'username' => 'newperson',
        'auth_date' => (string) time(),
    ];

    $hash = generateTelegramHash($data, $botToken);
    $data['hash'] = $hash;

    $this->post(route('auth.telegram.callback'), $data)
        ->assertRedirect('/');

    $user = User::query()->where('telegram_id', '111222')->firstOrFail();

    expect($user->name)->toBe('New Person')
        ->and($user->telegram_username)->toBe('newperson');
});

it('rejects invalid telegram hash', function () {
    config(['services.telegram.bot_token' => 'real-token']);

    $data = [
        'id' => '555666',
        'first_name' => 'Evil',
        'auth_date' => (string) time(),
        'hash' => 'forged_invalid_hash_value',
    ];

    $this->post(route('auth.telegram.callback'), $data)
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('telegram');

    $this->assertGuest();
});

it('rejects expired auth date', function () {
    $botToken = 'test-bot-token-12345';
    config(['services.telegram.bot_token' => $botToken]);

    $data = [
        'id' => '777888',
        'first_name' => 'Stale',
        'auth_date' => (string) (time() - 90000), // > 86400 seconds ago
    ];

    $hash = generateTelegramHash($data, $botToken);
    $data['hash'] = $hash;

    $this->post(route('auth.telegram.callback'), $data)
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('telegram');

    $this->assertGuest();
});
