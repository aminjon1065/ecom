<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFlashSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'end_date' => ['required', 'date'],
            'status' => ['boolean'],
            'show_at_main' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists' => 'Выбранный товар не найден.',
            'end_date.required' => 'Дата окончания flash sale обязательна.',
        ];
    }
}
