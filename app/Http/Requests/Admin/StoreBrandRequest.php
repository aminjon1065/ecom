<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('brands', 'slug')],
            'is_featured' => ['boolean'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'logo.required' => 'Логотип бренда обязателен.',
            'logo.image' => 'Логотип должен быть изображением.',
            'slug.unique' => 'Бренд с таким slug уже существует.',
        ];
    }
}
