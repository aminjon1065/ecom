<?php

namespace App\Http\Requests\Vendor;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateVendorOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user?->hasRole('vendor') || $user->vendor === null) {
            return false;
        }

        /** @var Order|null $order */
        $order = $this->route('order');

        if (! $order) {
            return false;
        }

        $productIds = Product::query()
            ->where('vendor_id', $user->vendor->id)
            ->pluck('id');

        return $order->products()->whereIn('product_id', $productIds)->exists();
    }

    public function rules(): array
    {
        return [
            'order_status' => ['required', new Enum(OrderStatus::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'order_status.required' => 'Статус заказа обязателен.',
        ];
    }
}
