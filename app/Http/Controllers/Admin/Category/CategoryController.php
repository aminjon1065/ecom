<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Str;

class CategoryController extends Controller
{
    public function __construct(
        private readonly ImageService $imageService,
    ) {}

    public function index(): \Inertia\Response
    {
        $categories = Category::query()
            ->select(['id', 'name', 'slug', 'icon', 'status', 'created_at'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/category/all-categories', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'status' => ['boolean'],
        ]);

        if ($request->hasFile('icon')) {
            $icon = $request->file('icon');
            $data['icon'] = $this->imageService->isRaster($icon)
                ? $this->imageService->storeThumb($icon, 'categories', 80, 80)
                : $icon->store('categories', 'public');
        }

        $data['slug'] = Str::slug($data['name']);
        $data['status'] = $data['status'] ?? true;

        Category::create($data);
        Cache::forget('categories_menu');

        return redirect()->back()->with('success', 'Категория добавлена');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'unique:categories,slug,'.$category->id,
            ],
            'icon' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp,svg',
                'max:2048',
            ],
            'status' => ['boolean'],
        ]);
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('icon')) {
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }

            $icon = $request->file('icon');
            $data['icon'] = $this->imageService->isRaster($icon)
                ? $this->imageService->storeThumb($icon, 'categories', 80, 80)
                : $icon->store('categories', 'public');
        } else {
            unset($data['icon']);
        }
        $category->update($data);
        Cache::forget('categories_menu');

        return redirect()->back();
    }

    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->update([
            'status' => ! $category->status,
        ]);
        Cache::forget('categories_menu');

        return redirect()->back();
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }
        $category->delete();
        Cache::forget('categories_menu');

        return redirect()->back();
    }
}
