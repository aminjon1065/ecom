<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSubCategoryRequest;
use App\Http\Requests\Admin\UpdateSubCategoryRequest;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Str;

class SubCategoryController extends Controller
{
    public function index()
    {
        $subCategories = SubCategory::with('category')
            ->latest()
            ->paginate(10);
        $categories = Category::select('id', 'name')->get();

        return Inertia::render('admin/sub-category/all-sub-category', [
            'subCategories' => $subCategories,
            'categories' => $categories,
        ]);
    }

    public function store(StoreSubCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['status'] = $data['status'] ?? true;

        SubCategory::create($data);
        Cache::forget('categories_menu');

        return redirect()->back();
    }

    public function update(UpdateSubCategoryRequest $request, SubCategory $subCategory): RedirectResponse
    {
        $data = $request->validated();

        $subCategory->update($data);
        Cache::forget('categories_menu');

        return redirect()->back();
    }

    public function toggleStatus(SubCategory $subCategory): RedirectResponse
    {
        $subCategory->update([
            'status' => ! $subCategory->status,
        ]);
        Cache::forget('categories_menu');

        return redirect()->back();
    }

    public function destroy(SubCategory $subCategory): RedirectResponse
    {
        $subCategory->delete();
        Cache::forget('categories_menu');

        return redirect()->back()->with('success', 'Подкатегория удалена.');
    }
}
