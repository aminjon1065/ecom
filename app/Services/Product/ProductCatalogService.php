<?php

namespace App\Services\Product;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Inertia\MergeProp;

class ProductCatalogService
{
    public function __construct(
        private readonly ProductFilter $productFilter,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     products: array<int, Product>|MergeProp,
     *     productsMeta: array{current_page: int, last_page: int, total: int},
     *     categories: \Illuminate\Database\Eloquent\Collection<int, Category>,
     *     brands: \Illuminate\Database\Eloquent\Collection<int, Brand>,
     *     filters: array<string, mixed>
     * }
     */
    public function pageData(array $filters): array
    {
        $query = Product::query()
            ->where('status', true)
            ->where('is_approved', true)
            ->with(['category:id,name', 'brand:id,name'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        $this->productFilter->apply($query, $filters);

        $paginated = $query->paginate(12)->withQueryString();

        return [
            'products' => $paginated->currentPage() > 1
                ? inertia()->merge($paginated->items())
                : $paginated->items(),
            'productsMeta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'total' => $paginated->total(),
            ],
            'categories' => Category::query()
                ->where('status', true)
                ->withCount(['products' => fn ($query) => $query->where('status', true)->where('is_approved', true)])
                ->whereHas('products', fn ($query) => $query->where('status', true)->where('is_approved', true))
                ->get(['id', 'name']),
            'brands' => Brand::query()
                ->where('status', true)
                ->withCount(['products' => fn ($query) => $query->where('status', true)->where('is_approved', true)])
                ->whereHas('products', fn ($query) => $query->where('status', true)->where('is_approved', true))
                ->get(['id', 'name']),
            'filters' => collect($filters)
                ->only(['search', 'category', 'sub_category', 'child_category', 'brand', 'min_price', 'max_price', 'sort'])
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->all(),
        ];
    }
}
