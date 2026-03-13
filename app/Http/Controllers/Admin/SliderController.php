<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSliderRequest;
use App\Http\Requests\Admin\UpdateSliderRequest;
use App\Models\Slider;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SliderController extends Controller
{
    public function __construct(
        private readonly ImageService $imageService,
    ) {}

    public function index(): Response
    {
        $sliders = Slider::orderBy('serial')->paginate(15)->withQueryString();

        return Inertia::render('admin/slider/index', [
            'sliders' => $sliders,
        ]);
    }

    public function store(StoreSliderRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['banner'] = $this->imageService->storeOptimized($request->file('banner'), 'sliders', 1400);
        $data['status'] = $data['status'] ?? true;

        Slider::create($data);

        return redirect()->back();
    }

    public function update(UpdateSliderRequest $request, Slider $slider): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('banner')) {
            Storage::disk('public')->delete($slider->banner);
            $data['banner'] = $this->imageService->storeOptimized($request->file('banner'), 'sliders', 1400);
        } else {
            unset($data['banner']);
        }

        $slider->update($data);

        return redirect()->back();
    }

    public function toggleStatus(Slider $slider): RedirectResponse
    {
        $slider->update(['status' => ! $slider->status]);

        return redirect()->back();
    }

    public function destroy(Slider $slider): RedirectResponse
    {
        Storage::disk('public')->delete($slider->banner);
        $slider->delete();

        return redirect()->back();
    }
}
