<?php

namespace App\Actions\Account;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

class BuildDashboardDataAction
{
    /**
     * @param  array<string, mixed>  $filters
     * @return array{
     *     stats: array{totalOrders: int, pendingOrders: int, completedOrders: int, totalSpent: float|int},
     *     recentOrders: \Illuminate\Database\Eloquent\Collection<int, Order>,
     *     dashboardFilters: array<string, mixed>
     * }
     */
    public function handle(User $user, array $filters): array
    {
        $totalOrders = Order::query()->where('user_id', $user->id)->count();
        $pendingOrders = Order::query()
            ->where('user_id', $user->id)
            ->where('order_status', OrderStatus::Pending->value)
            ->count();
        $completedOrders = Order::query()
            ->where('user_id', $user->id)
            ->where('order_status', OrderStatus::Delivered->value)
            ->count();
        $totalSpent = Order::query()
            ->where('user_id', $user->id)
            ->where('payment_status', true)
            ->sum('grand_total');

        $recentOrdersQuery = Order::query()
            ->where('user_id', $user->id)
            ->with('products.product:id,name,thumb_image')
            ->latest();

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $recentOrdersQuery->where('invoice_id', 'like', "%{$search}%");
        }

        if (! empty($filters['status'])) {
            $recentOrdersQuery->where('order_status', $filters['status']);
        }

        return [
            'stats' => [
                'totalOrders' => $totalOrders,
                'pendingOrders' => $pendingOrders,
                'completedOrders' => $completedOrders,
                'totalSpent' => $totalSpent,
            ],
            'recentOrders' => $recentOrdersQuery
                ->take(5)
                ->get(),
            'dashboardFilters' => collect($filters)
                ->only(['search', 'status'])
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->all(),
        ];
    }
}
