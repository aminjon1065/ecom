<?php

namespace App\Services\Product;

use App\Models\Order;
use App\Models\PriceAlert;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductViewEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProductDetailsService
{
    public function __construct(
        private readonly RecommendationService $recommendationService,
    ) {}

    public function findVisibleProduct(string $slug): Product
    {
        return Product::query()
            ->where('slug', $slug)
            ->where('status', true)
            ->where('is_approved', true)
            ->with([
                'category:id,name',
                'subCategory:id,name',
                'brand:id,name',
                'vendor.user:id,name',
                'images',
                'variantItems' => fn ($q) => $q->where('status', true)->orderBy('is_default', 'desc'),
            ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->firstOrFail();
    }

    public function recordView(Product $product, ?int $userId, string $sessionId): void
    {
        ProductViewEvent::query()->create([
            'product_id' => $product->id,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'viewed_at' => now(),
        ]);
    }

    /**
     * @return Collection<int, ProductReview>
     */
    public function reviews(Product $product, int $limit = 10): Collection
    {
        return ProductReview::query()
            ->where('product_id', $product->id)
            ->where('status', true)
            ->with('user:id,name,avatar')
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * @return array{
     *     relatedProducts: Collection<int, Product>,
     *     alsoBoughtProducts: Collection<int, Product>
     * }
     */
    public function recommendations(Product $product, int $limit = 4): array
    {
        return [
            'relatedProducts' => $this->recommendationService->relatedProducts($product, $limit),
            'alsoBoughtProducts' => $this->recommendationService->alsoBoughtProducts($product, $limit),
        ];
    }

    /**
     * @return array{
     *     isAuthenticated: bool,
     *     canReviewProduct: bool,
     *     isPriceAlertActive: bool,
     *     isInWishlist: bool,
     *     isInCart: bool
     * }
     */
    public function userState(Product $product, ?User $user): array
    {
        if ($user === null) {
            return [
                'isAuthenticated' => false,
                'canReviewProduct' => false,
                'isPriceAlertActive' => false,
                'isInWishlist' => false,
                'isInCart' => false,
            ];
        }

        return [
            'isAuthenticated' => true,
            'canReviewProduct' => Order::query()
                ->where('user_id', $user->id)
                ->where('order_status', '!=', 'cancelled')
                ->whereHas('products', function ($query) use ($product): void {
                    $query->where('product_id', $product->id);
                })
                ->exists(),
            'isPriceAlertActive' => PriceAlert::query()
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->where('is_active', true)
                ->exists(),
            'isInWishlist' => $user->wishlists()->where('product_id', $product->id)->exists(),
            'isInCart' => $user->carts()->where('product_id', $product->id)->exists(),
        ];
    }

    public function deliveryEstimate(Product $product): ?string
    {
        if ($product->qty <= 0) {
            return null;
        }

        if ($product->qty >= 20) {
            return 'Доставка завтра';
        }

        if ($product->qty >= 5) {
            return 'Доставка 1-2 дня';
        }

        return 'Доставка 2-4 дня';
    }

    /**
     * @return array{
     *     title: string,
     *     description: string|null,
     *     image: string|null,
     *     price: float,
     *     currency: string,
     *     availability: string,
     *     sku: string|null,
     *     brand: string|null
     * }
     */
    public function seo(Product $product): array
    {
        return [
            'title' => $product->seo_title ?: $product->name,
            'description' => $product->seo_description ?: $product->short_description,
            'image' => $product->thumb_image ? asset('storage/'.$product->thumb_image) : null,
            'price' => $product->effectivePrice(),
            'currency' => 'KGS',
            'availability' => $product->qty > 0 ? 'InStock' : 'OutOfStock',
            'sku' => $product->sku,
            'brand' => $product->brand?->name,
        ];
    }
}
