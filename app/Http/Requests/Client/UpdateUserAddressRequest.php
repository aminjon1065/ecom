<?php

namespace App\Http\Requests\Client;

use App\Models\UserAddress;
use Illuminate\Foundation\Http\FormRequest;

class UpdateUserAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var UserAddress|null $address */
        $address = $this->route('address');

        return $this->user() !== null
            && $address !== null
            && $this->user()->can('update', $address);
    }

    public function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
