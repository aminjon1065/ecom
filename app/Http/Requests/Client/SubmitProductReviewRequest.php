<?php

namespace App\Http\Requests\Client;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class SubmitProductReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        /** @var Product|null $product */
        $product = $this->route('product');

        if (! $user || ! $product) {
            return false;
        }

        return Order::query()
            ->where('user_id', $user->id)
            ->where('order_status', '!=', OrderStatus::Cancelled->value)
            ->whereHas('products', function ($query) use ($product): void {
                $query->where('product_id', $product->id);
            })
            ->exists();
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Укажите рейтинг.',
            'rating.integer' => 'Рейтинг должен быть числом.',
            'rating.min' => 'Минимальный рейтинг - 1.',
            'rating.max' => 'Максимальный рейтинг - 5.',
            'review.required' => 'Напишите текст отзыва.',
            'review.max' => 'Отзыв не должен превышать 1000 символов.',
        ];
    }
}
