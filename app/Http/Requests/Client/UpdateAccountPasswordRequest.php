<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateAccountPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $rules = [
            'password' => ['required', 'string', Password::min(8), 'confirmed'],
        ];

        if (! $this->isSocialOnlyUser()) {
            $rules['current_password'] = ['required', 'string', 'current_password:web'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Введите текущий пароль.',
            'current_password.current_password' => 'Текущий пароль указан неверно.',
            'password.required' => 'Введите новый пароль.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
        ];
    }

    private function isSocialOnlyUser(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        return (bool) ($user->telegram_id || $user->google_id)
            && str_ends_with((string) $user->email, '@telegram.local');
    }
}
