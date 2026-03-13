<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlashSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'end_date' => ['required', 'date', 'after:today'],
            'status' => ['boolean'],
            'show_at_main' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists' => 'Выбранный товар не найден.',
            'end_date.after' => 'Дата окончания flash sale должна быть позже сегодняшнего дня.',
        ];
    }
}
