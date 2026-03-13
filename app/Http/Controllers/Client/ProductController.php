<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\ProductIndexRequest;
use App\Http\Requests\Client\SubmitProductReviewRequest;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\Product\ProductCatalogService;
use App\Services\Product\ProductDetailsService;
use App\Services\Product\ProductSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $productCatalogService,
        private readonly ProductSearchService $productSearchService,
        private readonly ProductDetailsService $productDetailsService,
    ) {}

    public function index(ProductIndexRequest $request): Response
    {
        return Inertia::render('client/products/index', $this->productCatalogService->pageData($request->validated()));
    }

    public function show(string $slug): Response
    {
        $product = $this->productDetailsService->findVisibleProduct($slug);
        $this->productDetailsService->recordView($product, Auth::id(), session()->getId());

        return Inertia::render('client/products/show', [
            'product' => $product,
            'reviews' => $this->productDetailsService->reviews($product),
            ...$this->productDetailsService->recommendations($product),
            ...$this->productDetailsService->userState($product, Auth::user()),
            'deliveryEstimate' => $this->productDetailsService->deliveryEstimate($product),
            'seo' => $this->productDetailsService->seo($product),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        return response()->json(
            $this->productSearchService->suggestions((string) $request->input('q', ''))
        );
    }

    public function popularSearches(): JsonResponse
    {
        return response()->json($this->productSearchService->popularQueries());
    }

    public function submitReview(SubmitProductReviewRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        ProductReview::updateOrCreate(
            ['user_id' => Auth::id(), 'product_id' => $product->id],
            [
                'rating' => $validated['rating'],
                'review' => $validated['review'],
                'status' => false,
                'verified_purchase' => true,
            ],
        );

        return redirect()->back();
    }
}
