<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'address' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }
}
