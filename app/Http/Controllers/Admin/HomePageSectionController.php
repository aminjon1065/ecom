<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateHomePageSectionsRequest;
use App\Models\Category;
use App\Models\HomePageSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HomePageSectionController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/home-page-section/index', [
            'sections' => HomePageSection::query()
                ->with('category:id,name')
                ->orderBy('position')
                ->get()
                ->map(fn (HomePageSection $section): array => [
                    'id' => $section->id,
                    'position' => $section->position,
                    'type' => $section->type,
                    'category_id' => $section->category_id,
                    'category_name' => $section->category?->name,
                ]),
            'categories' => Category::query()
                ->where('status', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name' => $category->name,
                ]),
            'sectionTypes' => \App\Enums\HomePageSectionType::options(),
        ]);
    }

    public function update(UpdateHomePageSectionsRequest $request): RedirectResponse
    {
        /** @var array<int, array{type: string, category_id?: int|null}> $sections */
        $sections = $request->validated('sections');

        DB::transaction(function () use ($sections): void {
            HomePageSection::query()->delete();

            foreach (array_values($sections) as $index => $section) {
                HomePageSection::query()->create([
                    'position' => $index + 1,
                    'type' => $section['type'],
                    'category_id' => $section['type'] === \App\Enums\HomePageSectionType::Category->value
                        ? $section['category_id']
                        : null,
                ]);
            }
        });

        return redirect()->route('admin.home-page-section.index');
    }
}
