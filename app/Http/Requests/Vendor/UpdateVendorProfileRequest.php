<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('vendor')
            && $this->user()?->vendor !== null;
    }

    public function rules(): array
    {
        return [
            'shop_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'address' => ['nullable', 'string', 'max:500'],
            'banner' => ['nullable', 'image', 'max:2048'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'telegram_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'shop_name.required' => 'Название магазина обязательно.',
            'facebook_url.url' => 'Укажите корректный Facebook URL.',
            'telegram_url.url' => 'Укажите корректный Telegram URL.',
            'instagram_url.url' => 'Укажите корректный Instagram URL.',
        ];
    }
}
