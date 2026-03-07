<?php

namespace App\Http\Requests\Admin;

use App\Models\Coupons;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper((string) $this->input('code', '')),
            'status' => filter_var($this->input('status', true), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        /** @var Coupons $coupon */
        $coupon = $this->route('coupon');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('coupons', 'code')->ignore($coupon->id)],
            'quantity' => ['required', 'integer', 'min:1'],
            'max_use' => ['required', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'discount_type' => ['required', Rule::in(['percent', 'fixed'])],
            'discount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Купон с таким кодом уже существует.',
            'end_date.after_or_equal' => 'Дата окончания не может быть раньше даты начала.',
            'discount_type.in' => 'Неверный тип скидки.',
            'quantity.min' => 'Количество должно быть не меньше 1.',
            'max_use.min' => 'Лимит использований должен быть не меньше 1.',
        ];
    }
}
