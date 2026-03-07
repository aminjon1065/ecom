<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductReviewIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::in(['0', '1'])],
            'rating' => ['nullable', Rule::in(['1', '2', '3', '4', '5'])],
            'verified_purchase' => ['nullable', Rule::in(['0', '1'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
