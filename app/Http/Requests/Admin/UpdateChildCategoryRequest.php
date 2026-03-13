<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChildCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['required', 'exists:sub_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Выберите категорию.',
            'sub_category_id.required' => 'Выберите подкатегорию.',
            'sub_category_id.exists' => 'Выбранная подкатегория не найдена.',
        ];
    }
}
