<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property mixed $qty
 * @property mixed $price
 * @property mixed $cost_price
 * @property mixed $offer_price
 * @property mixed $status
 * @property mixed $is_approved
 */
class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'qty' => (int)$this->qty,
            'price' => (float)$this->price,
            'cost_price' => $this->cost_price !== null
                ? (float)$this->cost_price
                : null,
            'offer_price' => $this->offer_price !== null
                ? (float)$this->offer_price
                : null,
            'status' => filter_var($this->status, FILTER_VALIDATE_BOOLEAN),
            'is_approved' => filter_var($this->is_approved, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // BASIC
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
            'sku' => ['nullable', 'string', 'max:100'],
            'qty' => ['required', 'integer', 'min:0'],

            // PRICES
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'offer_price' => ['nullable', 'numeric', 'lt:price'],

            // OFFER DATES
            'offer_start_date' => ['nullable', 'date'],
            'offer_end_date' => ['nullable', 'date', 'after_or_equal:offer_start_date'],

            // RELATIONS
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'child_category_id' => ['nullable', 'exists:child_categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],

            // DESCRIPTIONS
            'short_description' => ['nullable', 'string', 'max:1000'],
            'long_description' => ['required', 'json'],

            // SEO
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:1000'],

            // MEDIA
            'thumb_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],

            // FLAGS
            'status' => ['boolean'],
            'is_approved' => ['boolean'],

            // ENUM
            'product_type' => [
                'required',
                Rule::in(['Топ', 'Рекомендуемый', 'Новый', 'Лучший']),
            ],
        ];
    }
}
