<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'exists:categories,id'],
            'sub_category' => ['nullable', 'exists:sub_categories,id'],
            'child_category' => ['nullable', 'exists:child_categories,id'],
            'brand' => ['nullable', 'exists:brands,id'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'gte:min_price'],
            'sort' => ['nullable', Rule::in(['latest', 'price_asc', 'price_desc', 'popular'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
