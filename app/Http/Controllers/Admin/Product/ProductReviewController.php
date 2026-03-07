<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductReviewIndexRequest;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductReviewController extends Controller
{
    public function index(ProductReviewIndexRequest $request): Response
    {
        $filters = $request->validated();

        $query = ProductReview::with(['product:id,name,thumb_image', 'user:id,name,email']);

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['rating']) && $filters['rating'] !== '') {
            $query->where('rating', $filters['rating']);
        }

        if (isset($filters['verified_purchase']) && $filters['verified_purchase'] !== '') {
            $query->where('verified_purchase', $filters['verified_purchase']);
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('admin/review/index', [
            'reviews' => $reviews,
            'filters' => collect($filters)
                ->only(['status', 'rating', 'verified_purchase'])
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->all(),
        ]);
    }

    public function toggleStatus(ProductReview $review): RedirectResponse
    {
        $review->update(['status' => ! $review->status]);

        return redirect()->back();
    }

    public function destroy(ProductReview $review): RedirectResponse
    {
        $review->delete();

        return redirect()->back();
    }
}
