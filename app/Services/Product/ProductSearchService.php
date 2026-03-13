<?php

namespace App\Services\Product;

use App\Models\PopularSearchQuery;
use App\Models\Product;
use App\Models\ProductViewEvent;
use Illuminate\Database\Eloquent\Collection;

class ProductSearchService
{
    /**
     * @return Collection<int, Product>
     */
    public function suggestions(string $query, int $limit = 8): Collection
    {
        $search = trim($query);

        if (mb_strlen($search) < 2) {
            return new Collection;
        }

        $builder = Product::query()
            ->where('status', true)
            ->where('is_approved', true)
            ->select([
                'id',
                'name',
                'slug',
                'thumb_image',
                'price',
                'offer_price',
                'offer_start_date',
                'offer_end_date',
                'category_id',
                'brand_id',
                'qty',
            ])
            ->with(['category:id,name', 'brand:id,name']);

        $bigrams = $this->bigrams($search);

        if ($bigrams === []) {
            return $builder
                ->where('name', 'like', "%{$search}%")
                ->orderByRaw('qty = 0')
                ->limit($limit)
                ->get();
        }

        $scoreExpression = [];
        $scoreBindings = [];

        foreach ($bigrams as $bigram) {
            $scoreExpression[] = '(LOWER(name) LIKE ?)';
            $scoreBindings[] = '%'.mb_strtolower($bigram).'%';
        }

        $builder->where(function ($query) use ($search, $bigrams): void {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");

            $minMatches = max(1, (int) ceil(count($bigrams) * 0.5));
            $bigramConditions = [];
            $bigramBindings = [];

            foreach ($bigrams as $bigram) {
                $bigramConditions[] = '(LOWER(name) LIKE ?)';
                $bigramBindings[] = '%'.mb_strtolower($bigram).'%';
            }

            $query->orWhereRaw(
                '('.implode(' + ', $bigramConditions).') >= ?',
                [...$bigramBindings, $minMatches]
            );
        });

        return $builder
            ->orderByRaw('qty = 0')
            ->orderByRaw(
                'CASE WHEN LOWER(name) LIKE ? THEN 0 WHEN LOWER(name) LIKE ? THEN 1 ELSE 2 END',
                [mb_strtolower($search), mb_strtolower($search).'%']
            )
            ->orderByRaw('('.implode(' + ', $scoreExpression).') DESC', $scoreBindings)
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<int, string>
     */
    public function popularQueries(int $limit = 8): array
    {
        $manualQueries = PopularSearchQuery::query()
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->limit($limit)
            ->pluck('query')
            ->map(fn (string $query): string => trim($query))
            ->filter(fn (string $query): bool => $query !== '')
            ->values()
            ->all();

        if ($manualQueries !== []) {
            return $manualQueries;
        }

        $popularQueries = ProductViewEvent::query()
            ->join('products', 'products.id', '=', 'product_view_events.product_id')
            ->where('products.status', true)
            ->where('products.is_approved', true)
            ->groupBy('products.id', 'products.name')
            ->select('products.name')
            ->selectRaw('count(*) as views_count')
            ->orderByDesc('views_count')
            ->limit($limit)
            ->pluck('name')
            ->map(fn (string $name): string => trim($name))
            ->filter(fn (string $name): bool => $name !== '')
            ->values()
            ->all();

        if ($popularQueries !== []) {
            return $popularQueries;
        }

        return Product::query()
            ->where('status', true)
            ->where('is_approved', true)
            ->latest('id')
            ->limit($limit)
            ->pluck('name')
            ->map(fn (string $name): string => trim($name))
            ->filter(fn (string $name): bool => $name !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function bigrams(string $value): array
    {
        $value = mb_strtolower(trim($value));
        $length = mb_strlen($value);
        $bigrams = [];

        for ($index = 0; $index < $length - 1; $index++) {
            $bigram = mb_substr($value, $index, 2);

            if (trim($bigram) !== '' && mb_strlen(trim($bigram)) === 2) {
                $bigrams[] = $bigram;
            }
        }

        return array_unique($bigrams);
    }
}
