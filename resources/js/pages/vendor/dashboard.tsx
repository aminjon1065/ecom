import VendorLayout from '@/layouts/app/vendor/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    DollarSign,
    ShoppingCart,
    Box,
    Star,
    TrendingUp,
    TrendingDown,
    CheckCircle,
    Clock,
} from 'lucide-react';

interface Statistics {
    total_revenue: number;
    today_revenue: number;
    yesterday_revenue: number;
    total_orders: number;
    pending_orders: number;
    total_products: number;
    approved_products: number;
    pending_products: number;
    total_reviews: number;
    average_rating: number;
}

interface RecentOrder {
    id: number;
    invoice_id: number;
    amount: number;
    product_quantity: number;
    payment_method: string;
    payment_status: boolean;
    order_status: string;
    created_at: string;
    user: { id: number; name: string; email: string };
}

interface TopProduct {
    id: number;
    name: string;
    thumb_image: string;
    price: number;
    qty: number;
    status: boolean;
    reviews_count: number;
    reviews_avg_rating: number | null;
}

interface Props {
    statistics: Statistics | null;
    recentOrders: RecentOrder[];
    topProducts: TopProduct[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: '/vendor' },
];

const ORDER_STATUS_LABELS: Record<string, string> = {
    pending: 'Ожидает',
    processing: 'В обработке',
    shipped: 'Отправлен',
    delivered: 'Доставлен',
    cancelled: 'Отменён',
};

function formatCurrency(value: number): string {
    return value.toLocaleString('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('ru-RU');
}

export default function VendorDashboard({ statistics, recentOrders, topProducts }: Props) {
    if (!statistics) {
        return (
            <VendorLayout breadcrumbs={breadcrumbs}>
                <Head title="Дашборд продавца" />
                <div className="flex min-h-[400px] items-center justify-center">
                    <Card className="max-w-md">
                        <CardContent className="pt-6 text-center">
                            <Box className="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                            <h3 className="text-lg font-semibold">Профиль продавца не найден</h3>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Обратитесь к администратору для активации вашего аккаунта продавца.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </VendorLayout>
        );
    }

    const revenueTrend = statistics.yesterday_revenue > 0
        ? ((statistics.today_revenue - statistics.yesterday_revenue) / statistics.yesterday_revenue) * 100
        : statistics.today_revenue > 0 ? 100 : 0;

    return (
        <VendorLayout breadcrumbs={breadcrumbs}>
            <Head title="Дашборд продавца" />

            <div className="space-y-6">
                {/* Stats Cards */}
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Выручка</CardTitle>
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

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Заказы</CardTitle>
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

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Товары</CardTitle>
                            <Box className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{statistics.total_products}</div>
                            <div className="mt-1 flex items-center gap-3 text-xs">
                                <span className="flex items-center gap-1 text-green-600">
                                    <CheckCircle className="h-3 w-3" />
                                    {statistics.approved_products}
                                </span>
                                <span className="flex items-center gap-1 text-yellow-600">
                                    <Clock className="h-3 w-3" />
                                    {statistics.pending_products}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Рейтинг</CardTitle>
                            <Star className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{statistics.average_rating}</div>
                            <p className="mt-1 text-xs text-muted-foreground">
                                {statistics.total_reviews} отзывов
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    {/* Recent Orders */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Последние заказы</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {recentOrders.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">Заказов пока нет</p>
                            ) : (
                                <div className="space-y-3">
                                    {recentOrders.slice(0, 5).map((order) => (
                                        <a
                                            key={order.id}
                                            href={`/vendor/orders/${order.id}`}
                                            className="flex items-center justify-between rounded-lg border p-3 transition-colors hover:bg-muted/50"
                                        >
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <span className="font-mono text-sm font-medium">
                                                        #{order.invoice_id}
                                                    </span>
                                                    <Badge variant="outline" className="text-xs">
                                                        {ORDER_STATUS_LABELS[order.order_status] || order.order_status}
                                                    </Badge>
                                                </div>
                                                <p className="mt-1 text-xs text-muted-foreground">
                                                    {order.user.name} &middot; {formatDate(order.created_at)}
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <div className="font-medium">{formatCurrency(order.amount)} сом.</div>
                                                <Badge variant={order.payment_status ? 'default' : 'secondary'} className="text-xs">
                                                    {order.payment_status ? 'Оплачен' : 'Не оплачен'}
                                                </Badge>
                                            </div>
                                        </a>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                    {/* Top Products */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Популярные товары</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {topProducts.length === 0 ? (
                                <p className="py-8 text-center text-sm text-muted-foreground">Товаров пока нет</p>
                            ) : (
                                <div className="space-y-3">
                                    {topProducts.map((product) => (
                                        <div
                                            key={product.id}
                                            className="flex items-center gap-3 rounded-lg border p-3"
                                        >
                                            <img
                                                src={product.thumb_image}
                                                alt={product.name}
                                                className="h-12 w-12 rounded object-cover"
                                            />
                                            <div className="min-w-0 flex-1">
                                                <p className="truncate font-medium">{product.name}</p>
                                                <div className="mt-1 flex items-center gap-2 text-xs text-muted-foreground">
                                                    <span>{formatCurrency(product.price)} сом.</span>
                                                    <span>&middot;</span>
                                                    <span>Остаток: {product.qty}</span>
                                                    {product.reviews_count > 0 && (
                                                        <>
                                                            <span>&middot;</span>
                                                            <span className="flex items-center gap-0.5">
                                                                <Star className="h-3 w-3 fill-yellow-400 text-yellow-400" />
                                                                {(product.reviews_avg_rating ?? 0).toFixed(1)}
                                                                ({product.reviews_count})
                                                            </span>
                                                        </>
                                                    )}
                                                </div>
                                            </div>
                                            <Badge variant={product.status ? 'default' : 'secondary'}>
                                                {product.status ? 'Активен' : 'Скрыт'}
                                            </Badge>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </VendorLayout>
    );
}
