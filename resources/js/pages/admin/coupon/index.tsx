import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { PaginatedResponse } from '@/types/pagination';
import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

interface Coupon {
    id: number;
    name: string;
    code: string;
    quantity: number;
    max_use: number;
    start_date: string;
    end_date: string;
    discount_type: string;
    discount: number;
    status: boolean;
    total_used: number;
    created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Купоны', href: '/admin/coupon' },
];

interface Props {
    coupons: PaginatedResponse<Coupon>;
}

export default function CouponIndex({ coupons }: Props) {
    const columns: Column<Coupon>[] = [
        { key: 'name', label: 'Название' },
        { key: 'code', label: 'Код', render: (row) => <span className="font-mono text-sm">{row.code}</span> },
        {
            key: 'discount',
            label: 'Скидка',
            render: (row) => (row.discount_type === 'percent' ? `${row.discount}%` : `${row.discount} сом.`),
        },
        { key: 'quantity', label: 'Кол-во' },
        {
            key: 'total_used',
            label: 'Использовано',
            render: (row) => <Badge variant="outline">{row.total_used}/{row.quantity}</Badge>,
        },
        { key: 'start_date', label: 'Начало', render: (row) => new Date(row.start_date).toLocaleDateString('ru-RU') },
        { key: 'end_date', label: 'Конец', render: (row) => new Date(row.end_date).toLocaleDateString('ru-RU') },
        {
            key: 'status',
            label: 'Активен',
            render: (row) => (
                <Switch
                    checked={row.status}
                    onCheckedChange={() =>
                        router.patch(`/admin/coupon/${row.id}/status`, {}, {
                            preserveScroll: true,
                            onSuccess: () => toast.success('Статус обновлён'),
                        })
                    }
                />
            ),
        },
        {
            key: 'actions',
            label: '',
            className: 'text-right',
            render: (row) => (
                <div className="flex justify-end gap-2">
                    <Button asChild size="sm" variant="outline">
                        <Link href={`/admin/coupon/${row.id}/edit`}>
                            <Pencil className="h-4 w-4" />
                        </Link>
                    </Button>
                    <Button
                        size="sm"
                        variant="destructive"
                        onClick={() => router.delete(`/admin/coupon/${row.id}`, { preserveScroll: true })}
                    >
                        <Trash2 className="h-4 w-4" />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Купоны" />
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Купоны</h1>
                    <Button asChild>
                        <Link href="/admin/coupon/create">
                            <Plus className="mr-2 h-4 w-4" />
                            Добавить
                        </Link>
                    </Button>
                </div>
                <DataTable data={coupons.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination currentPage={coupons.current_page} lastPage={coupons.last_page} path={coupons.path} />
                </div>
            </div>
        </AppLayout>
    );
}
