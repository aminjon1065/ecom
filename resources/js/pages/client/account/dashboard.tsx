import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Skeleton } from '@/components/ui/skeleton';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import GetStatusBadge from '@/helper/getStatusBadge';
import AppAccountLayout from '@/layouts/app/client/account/app-account-layout';
import { Head, Link, router } from '@inertiajs/react';
import { CheckCircle, Clock, Package, Wallet } from 'lucide-react';
import { useState } from 'react';

interface Order {
    id: number;
    invoice_id: number;
    grand_total: number;
    order_status: string;
    payment_status: boolean;
    created_at: string;
    products: {
        id: number;
        product: {
            id: number;
            name: string;
            thumb_image: string;
        };
    }[];
}

interface Props {
    stats: {
        totalOrders: number;
        pendingOrders: number;
        completedOrders: number;
        totalSpent: number;
    };
    recentOrders: Order[];
    dashboardFilters: {
        search?: string;
        status?: string;
    };
}

const QUICK_STATUSES = [
    { value: 'all', label: 'Все' },
    { value: 'pending', label: 'Ожидают' },
    { value: 'processing', label: 'В обработке' },
    { value: 'delivered', label: 'Доставлены' },
    { value: 'cancelled', label: 'Отменённые' },
] as const;

function OrderRowSkeleton() {
    return (
        <div className="rounded-lg border p-4">
            <div className="flex flex-wrap items-start justify-between gap-3">
                <div className="space-y-2">
                    <Skeleton className="h-5 w-32" />
                    <Skeleton className="h-4 w-24" />
                </div>
                <div className="space-y-2">
                    <Skeleton className="h-6 w-28" />
                    <Skeleton className="h-5 w-20" />
                </div>
            </div>
            <div className="mt-3 flex gap-2">
                <Skeleton className="h-5 w-24" />
                <Skeleton className="h-4 w-16" />
            </div>
            <div className="mt-4 flex gap-2">
                <Skeleton className="h-8 w-24" />
                <Skeleton className="h-8 w-24" />
                <Skeleton className="h-8 w-16" />
            </div>
        </div>
    );
}

export default function AccountDashboard({ stats, recentOrders, dashboardFilters }: Props) {
    const [filters, setFilters] = useState({
        search: dashboardFilters.search ?? '',
        status: dashboardFilters.status ?? 'all',
    });
    const [isLoadingOrders, setIsLoadingOrders] = useState(false);

    function applyFilters(): void {
        setIsLoadingOrders(true);
        router.get(
            '/account',
            {
                search: filters.search || undefined,
                status: filters.status === 'all' ? undefined : filters.status,
            },
            { preserveScroll: true, preserveState: true, onFinish: () => setIsLoadingOrders(false) },
        );
    }

    function resetFilters(): void {
        setFilters({
            search: '',
            status: 'all',
        });
        setIsLoadingOrders(true);
        router.get('/account', {}, { preserveScroll: true, onFinish: () => setIsLoadingOrders(false) });
    }

    function setQuickStatus(status: string): void {
        setFilters((current) => ({ ...current, status }));
        setIsLoadingOrders(true);
        router.get(
            '/account',
            {
                search: filters.search || undefined,
                status: status === 'all' ? undefined : status,
            },
            { preserveScroll: true, preserveState: true, onFinish: () => setIsLoadingOrders(false) },
        );
    }

    function canCancel(orderStatus: string): boolean {
        return orderStatus === 'pending' || orderStatus === 'processing';
    }

    return (
        <AppAccountLayout activePath={'/account'} title={'Личный кабинет'}>
            <Head title="Личный кабинет" />
            {/* Stats Cards */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Всего заказов
                        </CardTitle>
                        <Package className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {stats.totalOrders}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            В обработке
                        </CardTitle>
                        <Clock className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {stats.pendingOrders}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Завершено
                        </CardTitle>
                        <CheckCircle className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {stats.completedOrders}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Потрачено
                        </CardTitle>
                        <Wallet className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {stats.totalSpent.toLocaleString()} сом
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Recent Orders */}
            <Card>
                <CardHeader>
                    <CardTitle>Последние заказы</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="mb-4 rounded-lg border p-4">
                        <div className="grid gap-3 md:grid-cols-4">
                            <Input
                                placeholder="Поиск по номеру"
                                value={filters.search}
                                onChange={(event) =>
                                    setFilters((current) => ({
                                        ...current,
                                        search: event.target.value,
                                    }))
                                }
                                onKeyDown={(event) => {
                                    if (event.key === 'Enter') {
                                        applyFilters();
                                    }
                                }}
                            />
                            <Select
                                value={filters.status}
                                onValueChange={(value) =>
                                    setFilters((current) => ({
                                        ...current,
                                        status: value,
                                    }))
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Все статусы" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Все статусы</SelectItem>
                                    <SelectItem value="pending">В обработке</SelectItem>
                                    <SelectItem value="processing">Обрабатывается</SelectItem>
                                    <SelectItem value="shipped">Отправлен</SelectItem>
                                    <SelectItem value="delivered">Доставлен</SelectItem>
                                    <SelectItem value="cancelled">Отменён</SelectItem>
                                </SelectContent>
                            </Select>
                            <Button onClick={applyFilters}>Применить</Button>
                            <Button variant="outline" onClick={resetFilters}>
                                Сбросить
                            </Button>
                        </div>
                        <div className="mt-3 flex flex-wrap gap-2">
                            {QUICK_STATUSES.map((status) => (
                                <Button
                                    key={status.value}
                                    variant={filters.status === status.value ? 'default' : 'outline'}
                                    size="sm"
                                    onClick={() => setQuickStatus(status.value)}
                                >
                                    {status.label}
                                </Button>
                            ))}
                        </div>
                    </div>

                    {isLoadingOrders ? (
                        <div className="space-y-3">
                            {Array.from({ length: 3 }).map((_, i) => (
                                <OrderRowSkeleton key={i} />
                            ))}
                        </div>
                    ) : recentOrders.length > 0 ? (
                        <div className="space-y-4">
                            <div className="text-sm text-muted-foreground">
                                Показано заказов: {recentOrders.length}
                            </div>
                            <div className="space-y-3">
                                {recentOrders.map((order) => (
                                    <div
                                        key={order.id}
                                        className="rounded-lg border p-4"
                                    >
                                        <div className="flex flex-wrap items-start justify-between gap-3">
                                            <div>
                                                <p className="font-semibold">
                                                    Заказ #{order.invoice_id}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {new Date(order.created_at).toLocaleDateString('ru-RU')}
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-lg font-bold">
                                                    {order.grand_total.toLocaleString()} сом
                                                </p>
                                                <Badge
                                                    variant={order.payment_status ? 'default' : 'secondary'}
                                                    className="mt-1"
                                                >
                                                    {order.payment_status ? 'Оплачен' : 'Не оплачен'}
                                                </Badge>
                                            </div>
                                        </div>
                                        <div className="mt-3 flex flex-wrap items-center gap-2">
                                            <GetStatusBadge order_status={order.order_status} />
                                            <span className="text-xs text-muted-foreground">
                                                Товаров: {order.products.length}
                                            </span>
                                        </div>
                                        <div className="mt-4 flex flex-wrap gap-2">
                                            {canCancel(order.order_status) && (
                                                <Button
                                                    variant="destructive"
                                                    size="sm"
                                                    onClick={() => router.patch(`/account/orders/${order.id}/cancel`)}
                                                >
                                                    Отменить
                                                </Button>
                                            )}
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() => router.post(`/account/orders/${order.id}/repeat`)}
                                            >
                                                Повторить
                                            </Button>
                                            <Button asChild variant="outline" size="sm">
                                                <a href={`/account/orders/${order.id}/invoice`}>Чек</a>
                                            </Button>
                                            <Button asChild variant="ghost" size="sm">
                                                <Link href={`/account/orders/${order.id}`}>Подробнее</Link>
                                            </Button>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            <div className="flex justify-end">
                                <Link href="/account/orders">
                                    <Button variant="outline">
                                        Все заказы
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    ) : (
                        <div className="py-8 text-center text-muted-foreground">
                            У вас пока нет заказов
                        </div>
                    )}
                </CardContent>
            </Card>
        </AppAccountLayout>
    );
}
