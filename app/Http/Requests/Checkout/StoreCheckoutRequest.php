<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCheckoutRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'address_id' => [
                'required',
                Rule::exists('user_addresses', 'id')->where(
                    fn ($query) => $query->where('user_id', $this->user()->id)
                ),
            ],
            'payment_method' => ['required', 'in:cash,card'],
            'shipping_rule_id' => ['nullable', 'exists:shipping_rules,id'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'idempotency_key' => ['required', 'string', 'max:100'],
        ];
    }
}
