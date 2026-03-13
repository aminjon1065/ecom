<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShippingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(['flat', 'free_shipping', 'min_cost'])],
            'min_cost' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['required', 'numeric', 'min:0'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.in' => 'Недопустимый тип правила доставки.',
            'cost.required' => 'Стоимость доставки обязательна.',
        ];
    }
}
