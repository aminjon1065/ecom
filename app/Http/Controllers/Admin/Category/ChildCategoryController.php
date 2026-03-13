<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreChildCategoryRequest;
use App\Http\Requests\Admin\UpdateChildCategoryRequest;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\SubCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Str;

class ChildCategoryController extends Controller
{
    public function index(Request $request)
    {
        $childCategories = ChildCategory::query()
            ->with([
                'category:id,name',
                'subCategory:id,name,category_id',
            ])
            ->select([
                'id',
                'category_id',
                'sub_category_id',
                'name',
                'slug',
                'status',
                'created_at',
            ])
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->sub_category_id, function ($query, $subCategoryId) {
                $query->where('sub_category_id', $subCategoryId);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/child-category/all-child-category', [
            'childCategories' => $childCategories,
            'categories' => Category::select('id', 'name')->get(),
            'subCategories' => SubCategory::select('id', 'name', 'category_id')->get(),
            'filters' => $request->only([
                'category_id',
                'sub_category_id',
            ]),
        ]);
    }

    public function store(StoreChildCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['status'] = $data['status'] ?? true;

        ChildCategory::create($data);
        Cache::forget('categories_menu');

        return redirect()->back();
    }

    public function update(UpdateChildCategoryRequest $request, ChildCategory $childCategory)
    {
        $data = $request->validated();

        $childCategory->update($data);
        Cache::forget('categories_menu');

        return redirect()->back();
    }

    public function toggleStatus(ChildCategory $childCategory): RedirectResponse
    {
        $childCategory->update([
            'status' => ! $childCategory->status,
        ]);
        Cache::forget('categories_menu');

        return redirect()->back();
    }

    public function destroy(ChildCategory $childCategory): RedirectResponse
    {
        $childCategory->delete();
        Cache::forget('categories_menu');

        return redirect()->back();
    }
}
