<?php

namespace App\Http\Controllers\Client;

use App\Enums\HomePageSectionType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FlashSale;
use App\Models\HomePageSection;
use App\Models\Product;
use App\Models\Slider;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function home(): Response
    {
        $sliders = Slider::where('status', true)
            ->orderBy('serial')
            ->get();

        $categories = Category::where('status', true)
            ->withCount(['products' => fn ($q) => $q->where('status', true)->where('is_approved', true)])
            ->orderByDesc('products_count')
            ->take(8)
            ->get(['id', 'name', 'icon']);

        $contentBlocks = HomePageSection::query()
            ->with('category:id,name')
            ->orderBy('position')
            ->get()
            ->map(fn (HomePageSection $section): ?array => $this->buildContentBlock($section))
            ->filter()
            ->values();

        return Inertia::render('welcome', [
            'sliders' => $sliders,
            'categories' => $categories,
            'contentBlocks' => $contentBlocks,
        ]);
    }

    protected function buildContentBlock(HomePageSection $section): ?array
    {
        return match ($section->type) {
            HomePageSectionType::Category->value => $this->buildCategoryBlock($section),
            HomePageSectionType::FlashSale->value => $this->buildFlashSaleBlock($section),
            HomePageSectionType::NewProducts->value => $this->buildProductTypeBlock(
                $section,
                HomePageSectionType::NewProducts,
                'latest',
            ),
            HomePageSectionType::TopProducts->value => $this->buildProductTypeBlock(
                $section,
                HomePageSectionType::TopProducts,
            ),
            HomePageSectionType::BestProducts->value => $this->buildProductTypeBlock(
                $section,
                HomePageSectionType::BestProducts,
                'popular',
            ),
            default => null,
        };
    }

    protected function buildCategoryBlock(HomePageSection $section): ?array
    {
        if (! $section->category) {
            return null;
        }

        $products = $this->baseProductQuery()
            ->where('category_id', $section->category_id)
            ->latest()
            ->take(8)
            ->get();

        if ($products->isEmpty()) {
            return null;
        }

        return [
            'id' => $section->id,
            'type' => $section->type,
            'title' => $section->category->name,
            'view_all_href' => '/products?category='.$section->category_id,
            'products' => $products,
        ];
    }

    protected function buildFlashSaleBlock(HomePageSection $section): ?array
    {
        $products = FlashSale::query()
            ->where('status', true)
            ->where('show_at_main', true)
            ->where('end_date', '>=', now())
            ->with([
                'product' => fn ($query) => $query
                    ->where('status', true)
                    ->where('is_approved', true)
                    ->with('category:id,name')
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews'),
            ])
            ->get()
            ->pluck('product')
            ->filter()
            ->take(8)
            ->values();

        if ($products->isEmpty()) {
            return null;
        }

        return [
            'id' => $section->id,
            'type' => $section->type,
            'title' => HomePageSectionType::FlashSale->label(),
            'view_all_href' => null,
            'products' => $products,
        ];
    }

    protected function buildProductTypeBlock(
        HomePageSection $section,
        HomePageSectionType $type,
        ?string $sort = null,
    ): ?array {
        $products = $this->baseProductQuery()
            ->where('product_type', $this->normalizeProductType($type))
            ->latest()
            ->take(8)
            ->get();

        if ($products->isEmpty()) {
            return null;
        }

        return [
            'id' => $section->id,
            'type' => $section->type,
            'title' => $type->label(),
            'view_all_href' => $sort ? '/products?sort='.$sort : null,
            'products' => $products,
        ];
    }

    protected function baseProductQuery(): Builder
    {
        $query = Product::query();

        return $this->applyBaseProductScope($query);
    }

    protected function applyBaseProductScope(Builder $query): Builder
    {
        return $query
            ->where('status', true)
            ->where('is_approved', true)
            ->with('category:id,name')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');
    }

    protected function normalizeProductType(HomePageSectionType $type): string
    {
        return match ($type) {
            HomePageSectionType::NewProducts => 'new',
            HomePageSectionType::TopProducts => 'top',
            HomePageSectionType::BestProducts => 'best',
            default => $type->value,
        };
    }
}
