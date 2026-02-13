import VendorLayout from '@/layouts/app/vendor/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Pagination } from '@/components/pagination';
import { Eye } from 'lucide-react';
import { useState } from 'react';

interface Order {
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

interface PaginatedOrders {
    data: Order[];
    current_page: number;
    last_page: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
    orders: PaginatedOrders;
    filters: {
        search?: string;
        order_status?: string;
        payment_status?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: '/vendor' },
    { title: 'Заказы', href: '/vendor/orders' },
];

const ORDER_STATUS_LABELS: Record<string, string> = {
    pending: 'Ожидает',
    processing: 'В обработке',
    shipped: 'Отправлен',
    delivered: 'Доставлен',
    cancelled: 'Отменён',
};

const ORDER_STATUS_VARIANTS: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
    pending: 'secondary',
    processing: 'outline',
    shipped: 'outline',
    delivered: 'default',
    cancelled: 'destructive',
};

function formatCurrency(value: number): string {
    return value.toLocaleString('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('ru-RU');
}

export default function VendorOrders({ orders, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    function applyFilters(newFilters: Record<string, string>) {
        router.get('/vendor/orders', { ...filters, ...newFilters }, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    function handleSearch(e: React.FormEvent) {
        e.preventDefault();
        applyFilters({ search });
    }

    return (
        <VendorLayout breadcrumbs={breadcrumbs}>
            <Head title="Мои заказы" />

            <div className="space-y-4">
                <h2 className="text-2xl font-bold">Заказы</h2>

                <Card>
                    <CardHeader>
                        <div className="flex flex-wrap items-center gap-3">
                            <form onSubmit={handleSearch} className="flex gap-2">
                                <Input
                                    placeholder="Поиск по номеру / клиенту..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-64"
                                />
                                <Button type="submit" variant="secondary">Найти</Button>
                            </form>
                            <Select
                                value={filters.order_status ?? 'all'}
                                onValueChange={(v) => applyFilters({ order_status: v === 'all' ? '' : v })}
                            >
                                <SelectTrigger className="w-44">
                                    <SelectValue placeholder="Статус заказа" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Все статусы</SelectItem>
                                    {Object.entries(ORDER_STATUS_LABELS).map(([key, label]) => (
                                        <SelectItem key={key} value={key}>{label}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <Select
                                value={filters.payment_status ?? 'all'}
                                onValueChange={(v) => applyFilters({ payment_status: v === 'all' ? '' : v })}
                            >
                                <SelectTrigger className="w-44">
                                    <SelectValue placeholder="Оплата" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Все</SelectItem>
                                    <SelectItem value="1">Оплачен</SelectItem>
                                    <SelectItem value="0">Не оплачен</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {orders.data.length === 0 ? (
                            <p className="py-8 text-center text-sm text-muted-foreground">Заказы не найдены</p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="pb-3 text-left font-medium">№</th>
                                            <th className="pb-3 text-left font-medium">Клиент</th>
                                            <th className="pb-3 text-left font-medium">Сумма</th>
                                            <th className="pb-3 text-left font-medium">Кол-во</th>
                                            <th className="pb-3 text-left font-medium">Оплата</th>
                                            <th className="pb-3 text-left font-medium">Статус</th>
                                            <th className="pb-3 text-left font-medium">Дата</th>
                                            <th className="pb-3 text-right font-medium"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {orders.data.map((order) => (
                                            <tr key={order.id} className="border-b last:border-0">
                                                <td className="py-3 font-mono text-sm">#{order.invoice_id}</td>
                                                <td className="py-3">
                                                    <div className="font-medium">{order.user.name}</div>
                                                    <div className="text-xs text-muted-foreground">{order.user.email}</div>
                                                </td>
                                                <td className="py-3">{formatCurrency(order.amount)} сом.</td>
                                                <td className="py-3">{order.product_quantity}</td>
                                                <td className="py-3">
                                                    <Badge variant={order.payment_status ? 'default' : 'secondary'}>
                                                        {order.payment_status ? 'Оплачен' : 'Не оплачен'}
                                                    </Badge>
                                                </td>
                                                <td className="py-3">
                                                    <Badge variant={ORDER_STATUS_VARIANTS[order.order_status] || 'outline'}>
                                                        {ORDER_STATUS_LABELS[order.order_status] || order.order_status}
                                                    </Badge>
                                                </td>
                                                <td className="py-3 text-muted-foreground">{formatDate(order.created_at)}</td>
                                                <td className="py-3 text-right">
                                                    <Button size="icon" variant="ghost" asChild>
                                                        <Link href={`/vendor/orders/${order.id}`}>
                                                            <Eye className="h-4 w-4" />
                                                        </Link>
                                                    </Button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                        {orders.last_page > 1 && (
                            <div className="mt-4">
                                <Pagination links={orders.links} />
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </VendorLayout>
    );
}
