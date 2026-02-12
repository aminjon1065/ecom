import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import { type BreadcrumbItem } from '@/types';
import { type DashboardProps, type PendingVendor, type PendingProduct, type RecentOrder } from '@/types/dashboard';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DataTable, type Column } from '@/components/datatable';
import {
    DollarSign,
    ShoppingCart,
    Box,
    Users,
    Store,
    TrendingUp,
    TrendingDown,
    Star,
} from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Дашборд',
        href: dashboard().url,
    },
];

const ORDER_STATUS_LABELS: Record<string, string> = {
    pending: 'Ожидает',
    processing: 'В обработке',
    shipped: 'Отправлен',
    delivered: 'Доставлен',
    cancelled: 'Отменён',
};

const ORDER_STATUS_COLORS: Record<string, string> = {
    pending: 'bg-yellow-500',
    processing: 'bg-blue-500',
    shipped: 'bg-indigo-500',
    delivered: 'bg-green-500',
    cancelled: 'bg-red-500',
};

function formatCurrency(value: number): string {
    return value.toLocaleString('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('ru-RU');
}

function getStatusLabel(status: string): string {
    return ORDER_STATUS_LABELS[status] || status;
}

export default function Dashboard({
    statistics,
    orderStats,
    pendingVendors,
    pendingProducts,
    recentOrders,
    pendingReviews,
}: DashboardProps) {
    const revenueTrend = statistics.yesterday_revenue > 0
        ? ((statistics.today_revenue - statistics.yesterday_revenue) / statistics.yesterday_revenue) * 100
        : statistics.today_revenue > 0 ? 100 : 0;

    function handleApproveVendor(id: number) {
        router.post(`/admin/vendors/${id}/approve`, {}, { preserveScroll: true });
    }

    function handleRejectVendor(id: number) {
        router.delete(`/admin/vendors/${id}/reject`, { preserveScroll: true });
    }

    function handleApproveProduct(id: number) {
        router.post(`/admin/products/${id}/approve`, {}, { preserveScroll: true });
    }

    function handleApproveReview(id: number) {
        router.post(`/admin/reviews/${id}/approve`, {}, { preserveScroll: true });
    }

    function handleDeleteReview(id: number) {
        router.delete(`/admin/reviews/${id}`, { preserveScroll: true });
    }

    const vendorColumns: Column<PendingVendor>[] = [
        {
            key: 'user',
            label: 'Пользователь',
            render: (row) => (
                <div className="flex items-center gap-3">
                    {row.user.avatar ? (
                        <img
                            src={row.user.avatar}
                            alt={row.user.name}
                            className="h-8 w-8 rounded-full object-cover"
                        />
                    ) : (
                        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-muted text-xs font-medium">
                            {row.user.name.charAt(0).toUpperCase()}
                        </div>
                    )}
                    <div>
                        <div className="font-medium">{row.user.name}</div>
                        <div className="text-xs text-muted-foreground">{row.user.email}</div>
                    </div>
                </div>
            ),
        },
        {
            key: 'shop_name',
            label: 'Магазин',
        },
        {
            key: 'created_at',
            label: 'Дата заявки',
            render: (row) => formatDate(row.created_at),
        },
        {
            key: 'actions',
            label: '',
            className: 'text-right',
            render: (row) => (
                <div className="flex justify-end gap-2">
                    <Button size="sm" onClick={() => handleApproveVendor(row.id)}>
                        Одобрить
                    </Button>
                    <Button size="sm" variant="destructive" onClick={() => handleRejectVendor(row.id)}>
                        Отклонить
                    </Button>
                </div>
            ),
        },
    ];

    const productColumns: Column<PendingProduct>[] = [
        {
            key: 'thumb_image',
            label: 'Фото',
            render: (row) => (
                <img
                    src={row.thumb_image}
                    alt={row.name}
                    className="h-10 w-10 rounded object-cover"
                />
            ),
        },
        {
            key: 'name',
            label: 'Название',
        },
        {
            key: 'price',
            label: 'Цена',
            render: (row) => `${formatCurrency(row.price)} сом.`,
        },
        {
            key: 'vendor',
            label: 'Продавец',
            render: (row) => row.vendor?.user?.name ?? '—',
        },
        {
            key: 'category',
            label: 'Категория',
            render: (row) => row.category?.name ?? '—',
        },
        {
            key: 'created_at',
            label: 'Дата',
            render: (row) => formatDate(row.created_at),
        },
        {
            key: 'actions',
            label: '',
            className: 'text-right',
            render: (row) => (
                <Button size="sm" onClick={() => handleApproveProduct(row.id)}>
                    Одобрить
                </Button>
            ),
        },
    ];

    const orderColumns: Column<RecentOrder>[] = [
        {
            key: 'invoice_id',
            label: '№',
            render: (row) => <span className="font-mono text-sm">#{row.invoice_id}</span>,
        },
        {
            key: 'user',
            label: 'Клиент',
            render: (row) => (
                <div>
                    <div className="font-medium">{row.user.name}</div>
                    <div className="text-xs text-muted-foreground">{row.user.email}</div>
                </div>
            ),
        },
        {
            key: 'amount',
            label: 'Сумма',
            render: (row) => `${formatCurrency(row.amount)} сом.`,
        },
        {
            key: 'product_quantity',
            label: 'Кол-во',
        },
        {
            key: 'payment_method',
            label: 'Оплата',
            render: (row) => (
                <div className="flex flex-col gap-1">
                    <span className="text-xs capitalize">{row.payment_method}</span>
                    <Badge variant={row.payment_status ? 'default' : 'secondary'}>
                        {row.payment_status ? 'Оплачен' : 'Не оплачен'}
                    </Badge>
                </div>
            ),
        },
        {
            key: 'order_status',
            label: 'Статус',
            render: (row) => (
                <Badge variant="outline">
                    {getStatusLabel(row.order_status)}
                </Badge>
            ),
        },
        {
            key: 'created_at',
            label: 'Дата',
            render: (row) => formatDate(row.created_at),
        },
    ];

    const orderStatsTotal = Object.values(orderStats).reduce((a, b) => a + b, 0);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Дашборд" />

            <div className="space-y-6">
                {/* Statistics Cards */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    {/* Revenue */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Общая выручка
                            </CardTitle>
                            <DollarSign className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{formatCurrency(statistics.total_revenue)} сом.</div>
                            {revenueTrend !== 0 && (
                                <p className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                                    {revenueTrend > 0 ? (
                                        <TrendingUp className="h-3 w-3 text-green-500" />
                                    ) : (
                                        <TrendingDown className="h-3 w-3 text-red-500" />
                                    )}
                                    <span className={revenueTrend > 0 ? 'text-green-600' : 'text-red-600'}>
                                        {revenueTrend > 0 ? '+' : ''}{revenueTrend.toFixed(1)}%
                                    </span>
                                    {' '}от вчера
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Orders */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Заказы
                            </CardTitle>
                            <ShoppingCart className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{statistics.total_orders}</div>
                            {statistics.pending_orders > 0 && (
                                <p className="mt-1 text-xs text-yellow-600">
                                    {statistics.pending_orders} ожидают обработки
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Products */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Товары
                            </CardTitle>
                            <Box className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{statistics.total_products}</div>
                            {statistics.pending_products > 0 && (
                                <p className="mt-1 text-xs text-orange-600">
                                    {statistics.pending_products} на модерации
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Customers */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Покупатели
                            </CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{statistics.total_customers}</div>
                        </CardContent>
                    </Card>

                    {/* Vendors */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                Продавцы
                            </CardTitle>
                            <Store className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{statistics.total_vendors}</div>
                            {statistics.pending_vendors > 0 && (
                                <p className="mt-1 text-xs text-orange-600">
                                    {statistics.pending_vendors} новых заявок
                                </p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Order Status Breakdown */}
                {orderStatsTotal > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Статистика заказов</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                                {Object.entries(orderStats).map(([status, count]) => {
                                    const percentage = orderStatsTotal > 0
                                        ? (count / orderStatsTotal) * 100
                                        : 0;
                                    return (
                                        <div key={status} className="space-y-2">
                                            <div className="flex items-center justify-between">
                                                <span className="text-sm">{getStatusLabel(status)}</span>
                                                <span className="text-sm font-semibold">{count}</span>
                                            </div>
                                            <div className="h-2 overflow-hidden rounded-full bg-muted">
                                                <div
                                                    className={`h-full rounded-full transition-all ${ORDER_STATUS_COLORS[status] || 'bg-gray-500'}`}
                                                    style={{ width: `${percentage}%` }}
                                                />
                                            </div>
                                            <p className="text-xs text-muted-foreground">{percentage.toFixed(0)}%</p>
                                        </div>
                                    );
                                })}
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Pending Vendors */}
                {pendingVendors.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                Заявки продавцов
                                <Badge variant="secondary" className="ml-2">
                                    {pendingVendors.length}
                                </Badge>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <DataTable data={pendingVendors} columns={vendorColumns} />
                        </CardContent>
                    </Card>
                )}

                {/* Pending Products */}
                {pendingProducts.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                Товары на модерации
                                <Badge variant="secondary" className="ml-2">
                                    {pendingProducts.length}
                                </Badge>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <DataTable data={pendingProducts} columns={productColumns} />
                        </CardContent>
                    </Card>
                )}

                {/* Recent Orders */}
                {recentOrders.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Последние заказы</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <DataTable data={recentOrders} columns={orderColumns} />
                        </CardContent>
                    </Card>
                )}

                {/* Pending Reviews */}
                {pendingReviews.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>
                                Отзывы на модерации
                                <Badge variant="secondary" className="ml-2">
                                    {pendingReviews.length}
                                </Badge>
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div className="divide-y">
                                {pendingReviews.map((review) => (
                                    <div key={review.id} className="flex items-start justify-between gap-4 py-4 first:pt-0 last:pb-0">
                                        <div className="min-w-0 flex-1">
                                            <div className="mb-1 flex items-center gap-2">
                                                <span className="font-medium">{review.user.name}</span>
                                                <span className="text-muted-foreground">→</span>
                                                <span className="truncate text-sm text-muted-foreground">
                                                    {review.product.name}
                                                </span>
                                            </div>
                                            <div className="mb-2 flex items-center gap-0.5">
                                                {Array.from({ length: 5 }).map((_, i) => (
                                                    <Star
                                                        key={i}
                                                        className={`h-3.5 w-3.5 ${
                                                            i < review.rating
                                                                ? 'fill-yellow-400 text-yellow-400'
                                                                : 'text-muted-foreground/30'
                                                        }`}
                                                    />
                                                ))}
                                            </div>
                                            <p className="text-sm text-muted-foreground line-clamp-2">
                                                {review.review}
                                            </p>
                                            <p className="mt-1 text-xs text-muted-foreground">
                                                {formatDate(review.created_at)}
                                            </p>
                                        </div>
                                        <div className="flex shrink-0 gap-2">
                                            <Button size="sm" onClick={() => handleApproveReview(review.id)}>
                                                Одобрить
                                            </Button>
                                            <Button size="sm" variant="destructive" onClick={() => handleDeleteReview(review.id)}>
                                                Удалить
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
