<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class DeleteUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Введите текущий пароль.',
            'password.current_password' => 'Указан неверный текущий пароль.',
        ];
    }
}
