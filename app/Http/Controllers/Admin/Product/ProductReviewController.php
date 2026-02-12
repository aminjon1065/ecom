<?php

namespace App\Http\Controllers\Admin\Product;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductReviewController extends Controller
{
    public function index(Request $request): Response
    {
        $query = ProductReview::with(['product:id,name,thumb_image', 'user:id,name,email']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->latest()->paginate(15)->withQueryString();

        return Inertia::render('admin/review/index', [
            'reviews' => $reviews,
            'filters' => $request->only(['status', 'rating']),
        ]);
    }

    public function toggleStatus(ProductReview $review): RedirectResponse
    {
        $review->update(['status' => !$review->status]);

        return redirect()->back();
    }

    public function destroy(ProductReview $review): RedirectResponse
    {
        $review->delete();

        return redirect()->back();
    }
}
