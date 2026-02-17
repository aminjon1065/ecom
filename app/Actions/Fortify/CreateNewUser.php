<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{9,15}$/'],
            'password' => $this->passwordRules(),
        ], [
            'phone.required' => 'Введите номер телефона',
            'phone.regex' => 'Неверный формат номера телефона',
        ])->validate();

        $phone = preg_replace('/[^0-9+]/', '', $input['phone']);

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $phone,
            'password' => $input['password'],
        ]);

        $user->assignRole('user');

        return $user;
    }
}
