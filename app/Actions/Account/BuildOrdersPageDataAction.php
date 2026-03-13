<?php

namespace App\Actions\Account;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BuildOrdersPageDataAction
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     orders: LengthAwarePaginator,
     *     filters: array<string, mixed>
     * }
     */
    public function handle(int $userId, array $filters): array
    {
        $query = Order::query()
            ->where('user_id', $userId)
            ->with('products.product:id,name,thumb_image')
            ->latest();

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where('invoice_id', 'like', "%{$search}%");
        }

        if (! empty($filters['status'])) {
            $query->where('order_status', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return [
            'orders' => $query->paginate(10)->withQueryString(),
            'filters' => collect($filters)
                ->only(['search', 'status', 'date_from', 'date_to'])
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->all(),
        ];
    }
}
