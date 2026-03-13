<?php

namespace App\Http\Requests\Admin;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'order_status' => ['required', new Enum(OrderStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'order_status.required' => 'Статус заказа обязателен.',
        ];
    }
}
