<?php

namespace App\Services\Product;

use App\Enums\OrderStatus;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    /**
     * @return Collection<int, Product>
     */
    public function relatedProducts(Product $product, int $limit = 4): Collection
    {
        return Product::query()
            ->where('id', '!=', $product->id)
            ->where('status', true)
            ->where('is_approved', true)
            ->select('products.*')
            ->selectRaw(
                '(case when category_id = ? then 2 else 0 end) + (case when brand_id = ? then 1 else 0 end) as relevance_score',
                [$product->category_id, $product->brand_id]
            )
            ->selectRaw('abs(price - ?) as price_distance', [$product->price])
            ->with('category:id,name')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('relevance_score')
            ->orderBy('price_distance')
            ->orderByDesc('reviews_count')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    public function alsoBoughtProducts(Product $product, int $limit = 4): Collection
    {
        $sourceOrderIds = DB::table('order_products')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->where('order_products.product_id', $product->id)
            ->where('orders.order_status', '!=', OrderStatus::Cancelled->value)
            ->pluck('order_products.order_id');

        if ($sourceOrderIds->isEmpty()) {
            return Product::query()->whereRaw('1 = 0')->get();
        }

        return Product::query()
            ->join('order_products', 'order_products.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->whereIn('order_products.order_id', $sourceOrderIds)
            ->where('products.id', '!=', $product->id)
            ->where('products.status', true)
            ->where('products.is_approved', true)
            ->where('orders.order_status', '!=', OrderStatus::Cancelled->value)
            ->groupBy('products.id')
            ->select('products.*')
            ->selectRaw('count(distinct order_products.order_id) as co_order_count')
            ->with('category:id,name')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('co_order_count')
            ->orderByDesc('products.id')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<int, int>  $productIds
     * @return Collection<int, Product>
     */
    public function cartRecommendations(array $productIds, int $limit = 5): Collection
    {
        if ($productIds === []) {
            return Product::query()->whereRaw('1 = 0')->get();
        }

        $sourceOrderIds = DB::table('order_products')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->whereIn('order_products.product_id', $productIds)
            ->where('orders.order_status', '!=', OrderStatus::Cancelled->value)
            ->pluck('order_products.order_id');

        if ($sourceOrderIds->isEmpty()) {
            return Product::query()->whereRaw('1 = 0')->get();
        }

        return Product::query()
            ->join('order_products', 'order_products.product_id', '=', 'products.id')
            ->join('orders', 'orders.id', '=', 'order_products.order_id')
            ->whereIn('order_products.order_id', $sourceOrderIds)
            ->whereNotIn('products.id', $productIds)
            ->where('products.status', true)
            ->where('products.is_approved', true)
            ->where('orders.order_status', '!=', OrderStatus::Cancelled->value)
            ->groupBy('products.id')
            ->select('products.*')
            ->selectRaw('count(distinct order_products.order_id) as co_order_count')
            ->with('category:id,name')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('co_order_count')
            ->orderByDesc('products.id')
            ->limit($limit)
            ->get();
    }
}
