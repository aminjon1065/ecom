<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupons;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\ShippingRules;
use App\Models\UserAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function index(): Response
    {
        $cartItems = Cart::where('user_id', Auth::id())
            ->with(['product:id,name,thumb_image,price,offer_price,offer_start_date,offer_end_date,qty'])
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $addresses = UserAddress::where('user_id', Auth::id())->get();
        $shippingRules = ShippingRules::where('status', true)->get();

        return Inertia::render('client/checkout', [
            'cartItems' => $cartItems,
            'addresses' => $addresses,
            'shippingRules' => $shippingRules,
        ]);
    }

    public function applyCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
        ]);

        $coupon = Coupons::where('code', $validated['code'])
            ->where('status', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where('quantity', '>', 0)
            ->first();

        if (!$coupon) {
            return back()->withErrors(['code' => 'Недействительный или просроченный купон']);
        }

        if ($coupon->max_use > 0 && $coupon->total_used >= $coupon->max_use) {
            return back()->withErrors(['code' => 'Купон уже использован максимальное количество раз']);
        }

        return back()->with('appliedCoupon', [
            'code'          => $coupon->code,
            'discount_type' => $coupon->discount_type,
            'discount'      => $coupon->discount,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address_id' => 'required|exists:user_addresses,id',
            'payment_method' => 'required|in:cash,card',
            'shipping_rule_id' => 'nullable|exists:shipping_rules,id',
            'coupon_code' => 'nullable|string',
        ]);

        $cartItems = Cart::where('user_id', Auth::id())
            ->with('product')
            ->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index');
        }

        return DB::transaction(function () use ($validated, $cartItems) {
            $subtotal = $cartItems->sum(function ($item) {
                $price = $this->getEffectivePrice($item->product);
                return $price * $item->quantity;
            });

            // Apply shipping
            $shippingCost = 0;
            if (!empty($validated['shipping_rule_id'])) {
                $rule = ShippingRules::find($validated['shipping_rule_id']);
                if ($rule) {
                    $shippingCost = match ($rule->type) {
                        'flat'          => $rule->cost,
                        'free_shipping' => 0,
                        // free when subtotal meets threshold, otherwise charge cost
                        'min_cost'      => $subtotal >= ($rule->min_cost ?? 0) ? 0 : $rule->cost,
                        default         => 0,
                    };
                }
            }

            // Apply coupon
            $discount = 0;
            $couponText = null;
            if (!empty($validated['coupon_code'])) {
                $coupon = Coupons::where('code', $validated['coupon_code'])
                    ->where('status', true)
                    ->first();
                if ($coupon) {
                    $discount = $coupon->discount_type === 'percent'
                        ? ($subtotal * $coupon->discount / 100)
                        : $coupon->discount;
                    $coupon->increment('total_used');
                    $coupon->decrement('quantity');
                    $couponText = $coupon->code;
                }
            }

            $total = max(0, $subtotal + $shippingCost - $discount);

            $order = Order::create([
                'invoice_id' => mt_rand(100000, 999999),
                'transaction_id' => 'TXN-' . strtoupper(uniqid()),
                'user_id' => Auth::id(),
                'amount' => $total,
                'product_quantity' => $cartItems->sum('quantity'),
                'payment_method' => $validated['payment_method'],
                'payment_status' => false,
                'coupon' => $couponText,
                'order_status' => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'unit_price' => $this->getEffectivePrice($item->product),
                ]);

                // Decrease stock
                $item->product->decrement('qty', $item->quantity);
            }

            // Clear cart
            Cart::where('user_id', Auth::id())->delete();

            return redirect()->route('account.orders.show', $order->id);
        });
    }

    private function getEffectivePrice($product): float
    {
        if (
            $product->offer_price &&
            $product->offer_start_date &&
            $product->offer_end_date &&
            now()->between($product->offer_start_date, $product->offer_end_date)
        ) {
            return $product->offer_price;
        }
        return $product->price;
    }
}
