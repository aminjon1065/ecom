<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class StoreGooglePhoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{9,15}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Введите номер телефона.',
            'phone.regex' => 'Неверный формат номера телефона.',
        ];
    }
}
