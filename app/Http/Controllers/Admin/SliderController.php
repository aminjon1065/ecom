<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SliderController extends Controller
{
    public function index(): Response
    {
        $sliders = Slider::orderBy('serial')->paginate(15)->withQueryString();

        return Inertia::render('admin/slider/index', [
            'sliders' => $sliders,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'banner' => ['required', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'type' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'starting_price' => ['required', 'string', 'max:255'],
            'btn_url' => ['required', 'string', 'max:255'],
            'serial' => ['required', 'integer', 'unique:sliders,serial'],
            'status' => ['boolean'],
        ]);

        $data['banner'] = $request->file('banner')->store('sliders', 'public');
        $data['status'] = $data['status'] ?? true;

        Slider::create($data);

        return redirect()->back();
    }

    public function update(Request $request, Slider $slider): RedirectResponse
    {
        $data = $request->validate([
            'banner' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'type' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'starting_price' => ['required', 'string', 'max:255'],
            'btn_url' => ['required', 'string', 'max:255'],
            'serial' => ['required', 'integer', 'unique:sliders,serial,' . $slider->id],
            'status' => ['boolean'],
        ]);

        if ($request->hasFile('banner')) {
            Storage::disk('public')->delete($slider->banner);
            $data['banner'] = $request->file('banner')->store('sliders', 'public');
        } else {
            unset($data['banner']);
        }

        $slider->update($data);

        return redirect()->back();
    }

    public function toggleStatus(Slider $slider): RedirectResponse
    {
        $slider->update(['status' => !$slider->status]);

        return redirect()->back();
    }

    public function destroy(Slider $slider): RedirectResponse
    {
        Storage::disk('public')->delete($slider->banner);
        $slider->delete();

        return redirect()->back();
    }
}
