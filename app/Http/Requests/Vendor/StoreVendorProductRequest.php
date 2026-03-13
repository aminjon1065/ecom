<?php

namespace App\Http\Requests\Vendor;

use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreVendorProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('vendor')
            && $this->user()?->vendor !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'integer', 'unique:products,code'],
            'thumb_image' => ['required', 'image', 'max:5120'],
            'category_id' => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'child_category_id' => ['nullable', 'exists:child_categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'qty' => ['required', 'integer', 'min:0'],
            'sku' => ['nullable', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'offer_price' => ['nullable', 'numeric', 'min:0'],
            'offer_start_date' => ['nullable', 'date'],
            'offer_end_date' => ['nullable', 'date', 'after_or_equal:offer_start_date'],
            'short_description' => ['required', 'string', 'max:500'],
            'long_description' => ['required', 'string'],
            'video_link' => ['nullable', 'url', 'max:500'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'product_type' => ['nullable', new Enum(ProductType::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Товар с таким кодом уже существует.',
            'thumb_image.required' => 'Изображение товара обязательно.',
            'offer_end_date.after_or_equal' => 'Дата окончания акции должна быть не раньше даты начала.',
        ];
    }
}
