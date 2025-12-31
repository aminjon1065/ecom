<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Str;

class BrandController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $brands = Brand::query()
            ->select([
                'id',
                'logo',
                'name',
                'slug',
                'is_featured',
                'status',
                'created_at',
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('admin/brand/index', [
            'brands' => $brands,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'logo' => ['required', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:brands,slug'],
            'is_featured' => ['boolean'],
            'status' => ['boolean'],
        ]);

        $data['logo'] = $request->file('logo')->store('brands', 'public');
        $data['slug'] = Str::slug($data['name']);
        $data['is_featured'] = $data['is_featured'] ?? true;
        $data['status'] = $data['status'] ?? true;

        Brand::create($data);

        return redirect()->back();
    }

    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $data = $request->validate([
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:brands,slug,' . $brand->id],
            'is_featured' => ['boolean'],
            'status' => ['boolean'],
        ]);

        if ($request->hasFile('logo')) {
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }

            $data['logo'] = $request->file('logo')->store('brands', 'public');
        } else {
            unset($data['logo']);
        }

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $brand->update($data);

        return redirect()->back();
    }

    public function toggleStatus(Brand $brand): RedirectResponse
    {
        $brand->update([
            'status' => !$brand->status,
        ]);

        return redirect()->back();
    }

    public function destroy(Brand $brand): RedirectResponse
    {
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        $brand->delete();

        return redirect()->back();
    }

    public function toggleFeature(Brand $brand): RedirectResponse
    {
        $brand->update([
            'is_featured' => !$brand->is_featured,
        ]);

        return redirect()->back();
    }
}
