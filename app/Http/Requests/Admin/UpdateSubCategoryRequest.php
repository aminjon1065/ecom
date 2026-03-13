<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Выберите категорию.',
            'category_id.exists' => 'Выбранная категория не найдена.',
        ];
    }
}
