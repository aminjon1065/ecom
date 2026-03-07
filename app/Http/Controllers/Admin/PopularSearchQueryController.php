<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePopularSearchQueryRequest;
use App\Http\Requests\Admin\UpdatePopularSearchQueryRequest;
use App\Models\PopularSearchQuery;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PopularSearchQueryController extends Controller
{
    public function index(): Response
    {
        $queries = PopularSearchQuery::query()
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/popular-search-query/index', [
            'queries' => $queries,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/popular-search-query/create');
    }

    public function store(StorePopularSearchQueryRequest $request): RedirectResponse
    {
        PopularSearchQuery::query()->create([
            'query' => $request->validated('query'),
            'priority' => (int) $request->validated('priority', 0),
            'is_active' => (bool) $request->validated('is_active', true),
        ]);

        return redirect()->route('admin.popular-search-query.index');
    }

    public function edit(PopularSearchQuery $popularSearchQuery): Response
    {
        return Inertia::render('admin/popular-search-query/edit', [
            'popularSearchQuery' => $popularSearchQuery,
        ]);
    }

    public function update(UpdatePopularSearchQueryRequest $request, PopularSearchQuery $popularSearchQuery): RedirectResponse
    {
        $popularSearchQuery->update([
            'query' => $request->validated('query'),
            'priority' => (int) $request->validated('priority', 0),
            'is_active' => (bool) $request->validated('is_active', true),
        ]);

        return redirect()->route('admin.popular-search-query.index');
    }

    public function toggleStatus(PopularSearchQuery $popularSearchQuery): RedirectResponse
    {
        $popularSearchQuery->update([
            'is_active' => ! $popularSearchQuery->is_active,
        ]);

        return redirect()->back();
    }

    public function destroy(PopularSearchQuery $popularSearchQuery): RedirectResponse
    {
        $popularSearchQuery->delete();

        return redirect()->back();
    }
}
