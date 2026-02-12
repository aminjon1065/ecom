import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { PaginatedResponse } from '@/types/pagination';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';

interface SellerProduct {
    id: number;
    name: string;
    slug: string;
    thumb_image: string;
    price: number;
    offer_price: number | null;
    qty: number;
    code: number;
    status: boolean;
    is_approved: boolean;
    created_at: string;
    vendor: { user: { id: number; name: string } } | null;
    category: { id: number; name: string } | null;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Продукты продавцов', href: '/admin/seller-products' },
];

interface Props {
    products: PaginatedResponse<SellerProduct>;
    filters: { search?: string; approval?: string };
}

export default function SellerProductIndex({ products, filters: initialFilters }: Props) {
    const [filters, setFilters] = useState({
        search: initialFilters.search ?? '',
        approval: initialFilters.approval ?? 'all',
    });

    function applyFilters() {
        router.get('/admin/seller-products', {
            search: filters.search || undefined,
            approval: filters.approval === 'all' ? undefined : filters.approval,
            page: 1,
        }, { preserveScroll: true, preserveState: true });
    }

    const columns: Column<SellerProduct>[] = [
        { key: 'code', label: 'Код', className: 'text-center' },
        {
            key: 'thumb_image', label: '', className: 'w-[70px]',
            render: (row) => <img src={row.thumb_image} alt={row.name} className="h-12 w-12 rounded border object-cover" />,
        },
        {
            key: 'name', label: 'Название',
            render: (row) => <div className="font-medium">{row.name}</div>,
        },
        {
            key: 'vendor', label: 'Продавец',
            render: (row) => row.vendor?.user?.name ?? '—',
        },
        {
            key: 'category', label: 'Категория',
            render: (row) => row.category?.name ?? '—',
        },
        {
            key: 'price', label: 'Цена',
            render: (row) => `${row.price.toLocaleString('ru-RU')} сом.`,
        },
        {
            key: 'is_approved', label: 'Одобрен',
            render: (row) => (
                <Switch
                    checked={row.is_approved}
                    onCheckedChange={() =>
                        router.patch(`/admin/seller-products/${row.id}/approval`, {}, {
                            preserveScroll: true,
                            onSuccess: () => toast.success(row.is_approved ? 'Одобрение снято' : 'Товар одобрен'),
                        })
                    }
                />
            ),
        },
        {
            key: 'status', label: 'Активен',
            render: (row) => (
                <Switch
                    checked={row.status}
                    onCheckedChange={() =>
                        router.patch(`/admin/seller-products/${row.id}/status`, {}, {
                            preserveScroll: true,
                            onSuccess: () => toast.success('Статус обновлен'),
                        })
                    }
                />
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Продукты продавцов" />
            <div className="space-y-4">
                <h1 className="text-xl font-semibold">Продукты продавцов</h1>
                <div className="rounded-lg border p-4">
                    <div className="grid gap-4 md:grid-cols-3">
                        <Input placeholder="Поиск: название, код" value={filters.search}
                            onChange={(e) => setFilters((f) => ({ ...f, search: e.target.value }))}
                            onKeyDown={(e) => e.key === 'Enter' && applyFilters()} />
                        <Select value={filters.approval} onValueChange={(v) => setFilters((f) => ({ ...f, approval: v }))}>
                            <SelectTrigger><SelectValue placeholder="Одобрение" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Все</SelectItem>
                                <SelectItem value="1">Одобренные</SelectItem>
                                <SelectItem value="0">Не одобренные</SelectItem>
                            </SelectContent>
                        </Select>
                        <div className="flex gap-2">
                            <Button onClick={applyFilters}>Применить</Button>
                            <Button variant="outline" onClick={() => { setFilters({ search: '', approval: 'all' }); router.get('/admin/seller-products'); }}>Сбросить</Button>
                        </div>
                    </div>
                </div>
                <DataTable data={products.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination currentPage={products.current_page} lastPage={products.last_page} path={products.path} />
                </div>
            </div>
        </AppLayout>
    );
}
