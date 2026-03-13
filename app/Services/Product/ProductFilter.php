<?php

namespace App\Services\Product;

use Illuminate\Database\Eloquent\Builder;

class ProductFilter
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function apply(Builder $query, array $filters): Builder
    {
        $search = isset($filters['search']) ? trim((string) $filters['search']) : '';
        if ($search !== '') {
            $bigrams = $this->getBigrams($search);
            $words = array_filter(explode(' ', $search));

            $query->where(function (Builder $builder) use ($search, $words, $bigrams): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");

                if (count($words) > 1) {
                    $builder->orWhere(function (Builder $subQuery) use ($words): void {
                        foreach ($words as $word) {
                            $subQuery->where(function (Builder $innerQuery) use ($word): void {
                                $innerQuery->where('name', 'like', "%{$word}%")
                                    ->orWhere('short_description', 'like', "%{$word}%");
                            });
                        }
                    });
                }

                if (! empty($bigrams)) {
                    $minMatches = max(1, (int) ceil(count($bigrams) * 0.5));
                    $conditions = [];
                    $bindings = [];

                    foreach ($bigrams as $bigram) {
                        $conditions[] = '(LOWER(name) LIKE ?)';
                        $bindings[] = '%'.mb_strtolower($bigram).'%';
                    }

                    $builder->orWhereRaw('('.implode(' + ', $conditions).') >= ?', [...$bindings, $minMatches]);
                }
            });
        }

        if (! empty($filters['category'])) {
            $query->where('category_id', $filters['category']);
        }

        if (! empty($filters['sub_category'])) {
            $query->where('sub_category_id', $filters['sub_category']);
        }

        if (! empty($filters['child_category'])) {
            $query->where('child_category_id', $filters['child_category']);
        }

        if (! empty($filters['brand'])) {
            $query->where('brand_id', $filters['brand']);
        }

        [$effectivePriceSql, $effectivePriceBindings] = $this->effectivePriceExpression();

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $query->where(function (Builder $builder) use ($filters): void {
                $builder->where(function (Builder $activeOfferQuery) use ($filters): void {
                    $this->applyActiveOfferConstraint($activeOfferQuery);
                    $activeOfferQuery->where('offer_price', '>=', $filters['min_price']);
                })->orWhere(function (Builder $regularPriceQuery) use ($filters): void {
                    $this->applyInactiveOfferConstraint($regularPriceQuery);
                    $regularPriceQuery->where('price', '>=', $filters['min_price']);
                });
            });
        }

        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $query->where(function (Builder $builder) use ($filters): void {
                $builder->where(function (Builder $activeOfferQuery) use ($filters): void {
                    $this->applyActiveOfferConstraint($activeOfferQuery);
                    $activeOfferQuery->where('offer_price', '<=', $filters['max_price']);
                })->orWhere(function (Builder $regularPriceQuery) use ($filters): void {
                    $this->applyInactiveOfferConstraint($regularPriceQuery);
                    $regularPriceQuery->where('price', '<=', $filters['max_price']);
                });
            });
        }

        $query->orderByRaw('qty = 0');

        $sort = $filters['sort'] ?? 'latest';
        match ($sort) {
            'price_asc' => $query->orderByRaw("{$effectivePriceSql} asc", $effectivePriceBindings),
            'price_desc' => $query->orderByRaw("{$effectivePriceSql} desc", $effectivePriceBindings),
            'popular' => $query->orderByDesc('reviews_count'),
            default => $query->latest(),
        };

        return $query;
    }

    /**
     * @return array<int, string>
     */
    private function getBigrams(string $value): array
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

    /**
     * @return array{0: string, 1: array<int, string>}
     */
    private function effectivePriceExpression(): array
    {
        $today = now()->toDateString();

        return [
            'case when offer_price is not null and offer_start_date is not null and offer_end_date is not null and offer_start_date <= ? and offer_end_date >= ? then offer_price else price end',
            [$today, $today],
        ];
    }

    private function applyActiveOfferConstraint(Builder $query): void
    {
        $today = now()->toDateString();

        $query
            ->whereNotNull('offer_price')
            ->whereNotNull('offer_start_date')
            ->whereNotNull('offer_end_date')
            ->whereDate('offer_start_date', '<=', $today)
            ->whereDate('offer_end_date', '>=', $today);
    }

    private function applyInactiveOfferConstraint(Builder $query): void
    {
        $today = now()->toDateString();

        $query->where(function (Builder $builder) use ($today): void {
            $builder->whereNull('offer_price')
                ->orWhereNull('offer_start_date')
                ->orWhereNull('offer_end_date')
                ->orWhereDate('offer_start_date', '>', $today)
                ->orWhereDate('offer_end_date', '<', $today);
        });
    }
}
