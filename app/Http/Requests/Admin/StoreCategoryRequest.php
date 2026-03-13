<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Название категории обязательно.',
            'icon.image' => 'Иконка категории должна быть изображением.',
        ];
    }
}
