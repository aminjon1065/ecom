<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPhoneOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{9,15}$/'],
            'otp' => ['required', 'string', 'size:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Введите номер телефона.',
            'phone.regex' => 'Неверный формат номера телефона.',
            'otp.required' => 'Введите код подтверждения.',
            'otp.size' => 'Код должен содержать 6 цифр.',
        ];
    }
}
