<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ImportProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Выберите файл для импорта.',
            'file.mimes' => 'Поддерживаются только Excel-файлы формата xlsx или xls.',
        ];
    }
}
