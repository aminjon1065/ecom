import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { PaginatedResponse } from '@/types/pagination';
import { Head, Link, router } from '@inertiajs/react';
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
    user: { id: number; name: string; email: string; phone: string | null; telegram_username: string | null };
}

const STATUS_LABELS: Record<string, string> = {
    pending: 'Ожидает',
    processing: 'В обработке',
    shipped: 'Отправлен',
    delivered: 'Доставлен',
    cancelled: 'Отменён',
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Заказы', href: '/admin/order' },
];

interface Props {
    orders: PaginatedResponse<Order>;
    filters: { search?: string; order_status?: string; payment_status?: string };
}

export default function OrderIndex({ orders, filters: initialFilters }: Props) {
    const [filters, setFilters] = useState({
        search: initialFilters.search ?? '',
        order_status: initialFilters.order_status ?? 'all',
        payment_status: initialFilters.payment_status ?? 'all',
    });

    function applyFilters() {
        router.get('/admin/order', {
            search: filters.search || undefined,
            order_status: filters.order_status === 'all' ? undefined : filters.order_status,
            payment_status: filters.payment_status === 'all' ? undefined : filters.payment_status,
            page: 1,
        }, { preserveScroll: true, preserveState: true });
    }

    const columns: Column<Order>[] = [
        {
            key: 'invoice_id',
            label: '№ заказа',
            render: (row) => <span className="font-mono text-sm">#{row.invoice_id}</span>,
        },
        {
            key: 'user',
            label: 'Клиент',
            render: (row) => (
                <div>
                    <div className="font-medium">{row.user.name}</div>
                    <div className="text-xs text-muted-foreground">
                        {row.user.telegram_username
                            ? `@${row.user.telegram_username}`
                            : row.user.phone
                              ? row.user.phone
                              : row.user.email}
                    </div>
                </div>
            ),
        },
        {
            key: 'amount',
            label: 'Сумма',
            render: (row) => `${row.amount.toLocaleString('ru-RU')} сом.`,
        },
        { key: 'product_quantity', label: 'Товаров' },
        {
            key: 'payment_status',
            label: 'Оплата',
            render: (row) => (
                <Badge variant={row.payment_status ? 'default' : 'secondary'}>
                    {row.payment_status ? 'Оплачен' : 'Не оплачен'}
                </Badge>
            ),
        },
        {
            key: 'order_status',
            label: 'Статус',
            render: (row) => (
                <Select
                    value={row.order_status}
                    onValueChange={(v) =>
                        router.patch(`/admin/order/${row.id}/status`, { order_status: v }, { preserveScroll: true })
                    }
                >
                    <SelectTrigger className="w-[140px]">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {Object.entries(STATUS_LABELS).map(([val, label]) => (
                            <SelectItem key={val} value={val}>{label}</SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            ),
        },
        {
            key: 'created_at',
            label: 'Дата',
            render: (row) => new Date(row.created_at).toLocaleDateString('ru-RU'),
        },
        {
            key: 'actions',
            label: '',
            className: 'text-right',
            render: (row) => (
                <Link href={`/admin/order/${row.id}`}>
                    <Button size="sm" variant="outline"><Eye className="h-4 w-4" /></Button>
                </Link>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Заказы" />
            <div className="space-y-4">
                <h1 className="text-xl font-semibold">Заказы</h1>
                <div className="rounded-lg border p-4">
                    <div className="grid gap-4 md:grid-cols-4">
                        <Input
                            placeholder="Поиск: № заказа"
                            value={filters.search}
                            onChange={(e) => setFilters((f) => ({ ...f, search: e.target.value }))}
                            onKeyDown={(e) => e.key === 'Enter' && applyFilters()}
                        />
                        <Select value={filters.order_status} onValueChange={(v) => setFilters((f) => ({ ...f, order_status: v }))}>
                            <SelectTrigger><SelectValue placeholder="Статус заказа" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Все статусы</SelectItem>
                                {Object.entries(STATUS_LABELS).map(([val, label]) => (
                                    <SelectItem key={val} value={val}>{label}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <Select value={filters.payment_status} onValueChange={(v) => setFilters((f) => ({ ...f, payment_status: v }))}>
                            <SelectTrigger><SelectValue placeholder="Оплата" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Все</SelectItem>
                                <SelectItem value="1">Оплачен</SelectItem>
                                <SelectItem value="0">Не оплачен</SelectItem>
                            </SelectContent>
                        </Select>
                        <div className="flex gap-2">
                            <Button onClick={applyFilters}>Применить</Button>
                            <Button variant="outline" onClick={() => { setFilters({ search: '', order_status: 'all', payment_status: 'all' }); router.get('/admin/order'); }}>Сбросить</Button>
                        </div>
                    </div>
                </div>
                <DataTable data={orders.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination currentPage={orders.current_page} lastPage={orders.last_page} path={orders.path} />
                </div>
            </div>
        </AppLayout>
    );
}
