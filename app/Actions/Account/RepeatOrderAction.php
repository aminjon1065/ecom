<?php

namespace App\Actions\Account;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;

class RepeatOrderAction
{
    public function handle(Order $order, int $userId): int
    {
        $order->load('products');

        $orderProductIds = $order->products->pluck('product_id')->all();

        $availableProducts = Product::query()
            ->whereIn('id', $orderProductIds)
            ->where('status', true)
            ->where('is_approved', true)
            ->get()
            ->keyBy('id');

        $existingCartItems = Cart::query()
            ->where('user_id', $userId)
            ->whereIn('product_id', $orderProductIds)
            ->get()
            ->keyBy('product_id');

        $addedItems = 0;

        foreach ($order->products as $orderItem) {
            /** @var Product|null $product */
            $product = $availableProducts->get($orderItem->product_id);

            if (! $product || $product->qty < 1) {
                continue;
            }

            $targetQty = min((int) $orderItem->quantity, (int) $product->qty, 100);

            if ($targetQty < 1) {
                continue;
            }

            $existingCart = $existingCartItems->get($product->id);

            if ($existingCart) {
                $newQuantity = min($existingCart->quantity + $targetQty, (int) $product->qty, 100);

                if ($newQuantity <= $existingCart->quantity) {
                    continue;
                }

                $existingCart->update(['quantity' => $newQuantity]);
            } else {
                Cart::query()->create([
                    'user_id' => $userId,
                    'product_id' => $product->id,
                    'quantity' => $targetQty,
                ]);
            }

            $addedItems++;
        }

        return $addedItems;
    }
}
