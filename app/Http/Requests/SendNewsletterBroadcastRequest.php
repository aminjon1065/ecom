<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendNewsletterBroadcastRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:160'],
            'body' => ['required', 'string', 'max:20000'],
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required' => 'Укажите тему письма.',
            'subject.max' => 'Тема письма не должна превышать 160 символов.',
            'body.required' => 'Введите текст рассылки.',
            'body.max' => 'Текст рассылки слишком длинный.',
        ];
    }
}
