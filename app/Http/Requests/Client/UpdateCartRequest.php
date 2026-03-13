<?php

namespace App\Http\Requests\Client;

use App\Models\Cart;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Cart|null $cart */
        $cart = $this->route('cart');

        return $this->user() !== null
            && $cart !== null
            && $this->user()->can('update', $cart);
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }
}
