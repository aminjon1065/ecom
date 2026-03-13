<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductFieldRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'field' => ['required', 'string', 'in:price,qty,sku'],
            'value' => array_merge(['present'], $this->valueRules()),
        ];
    }

    public function messages(): array
    {
        return [
            'field.in' => 'Разрешено обновлять только поля price, qty или sku.',
            'value.numeric' => 'Значение должно быть числом.',
            'value.integer' => 'Количество должно быть целым числом.',
            'value.min' => 'Значение не может быть отрицательным.',
            'value.max' => 'SKU не должен превышать 100 символов.',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function valueRules(): array
    {
        return match ($this->input('field')) {
            'price' => ['required', 'numeric', 'min:0'],
            'qty' => ['required', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            default => [],
        };
    }
}
