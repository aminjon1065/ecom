<?php

namespace App\Http\Controllers\Admin\Category;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Str;

class CategoryController extends Controller
{
    public function index(): \Inertia\Response
    {
        $categories = Category::query()
            ->select(['id', 'name', 'slug', 'icon', 'status', 'created_at'])
            ->latest()
            ->paginate(1)
            ->withQueryString();
        return Inertia::render('admin/category/all-categories', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'status' => ['boolean'],
        ]);

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('categories', 'public');
        }

        $data['slug'] = Str::slug($data['name']);
        $data['status'] = $data['status'] ?? true;

        Category::create($data);

        return redirect()->back()->with('success', 'Категория добавлена');
    }
}
