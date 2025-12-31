<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:sub_categories,slug'],
            'status' => ['boolean'],
        ]);

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['status'] = $data['status'] ?? true;

        SubCategory::create($data);

        return redirect()->back();
    }


    public function update(Request $request, SubCategory $subCategory): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'status' => ['boolean'],
        ]);

        $subCategory->update($data);

        return redirect()->back();
    }

    public function toggleStatus(SubCategory $subCategory): RedirectResponse
    {
        $subCategory->update([
            'status' => !$subCategory->status,
        ]);
        return redirect()->back();
    }

    public function destroy(SubCategory $subCategory): RedirectResponse
    {
        $subCategory->delete();
        return redirect()->back()->with('success', 'Подкатегория удалена.');
    }

}
