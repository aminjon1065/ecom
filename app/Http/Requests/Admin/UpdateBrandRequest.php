<?php

namespace App\Http\Requests\Admin;

use App\Models\Brand;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        /** @var Brand $brand */
        $brand = $this->route('brand');

        return [
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($brand)],
            'is_featured' => ['boolean'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'logo.image' => 'Логотип должен быть изображением.',
            'slug.unique' => 'Бренд с таким slug уже существует.',
        ];
    }
}
