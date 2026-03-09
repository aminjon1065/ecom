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
        $input = $this->all();

        // Accept legacy field names from the frontend and remap to canonical.
        if (! isset($input['is_active']) && isset($input['status'])) {
            $input['is_active'] = filter_var($input['status'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $input['is_active'] = filter_var($input['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
        }

        if (! isset($input['usage_limit']) && isset($input['max_use'])) {
            $input['usage_limit'] = $input['max_use'];
        }

        if (! isset($input['starts_at']) && isset($input['start_date'])) {
            $input['starts_at'] = $input['start_date'];
        }

        if (! isset($input['ends_at']) && isset($input['end_date'])) {
            $input['ends_at'] = $input['end_date'];
        }

        $input['code'] = strtoupper((string) ($input['code'] ?? ''));

        $this->replace($input);
    }

    public function rules(): array
    {
        /** @var Coupons $coupon */
        $coupon = $this->route('coupon');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('coupons', 'code')->ignore($coupon->id)],
            'quantity' => ['required', 'integer', 'min:1'],
            'usage_limit' => ['required', 'integer', 'min:1'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'discount_type' => ['required', Rule::in(['percent', 'fixed'])],
            'discount' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Купон с таким кодом уже существует.',
            'ends_at.after_or_equal' => 'Дата окончания не может быть раньше даты начала.',
            'discount_type.in' => 'Неверный тип скидки.',
            'quantity.min' => 'Количество должно быть не меньше 1.',
            'usage_limit.min' => 'Лимит использований должен быть не меньше 1.',
        ];
    }
}
