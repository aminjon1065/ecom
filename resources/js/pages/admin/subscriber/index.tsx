import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { PaginatedResponse } from '@/types/pagination';
import { Head, router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';

interface Subscriber {
    id: number;
    email: string;
    is_verified: boolean;
    created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Рассылки', href: '/admin/subscriber' },
];

interface Props { subscribers: PaginatedResponse<Subscriber>; }

export default function SubscriberIndex({ subscribers }: Props) {
    const columns: Column<Subscriber>[] = [
        { key: 'email', label: 'Email' },
        {
            key: 'is_verified', label: 'Подтверждён',
            render: (row) => <Badge variant={row.is_verified ? 'default' : 'secondary'}>{row.is_verified ? 'Да' : 'Нет'}</Badge>,
        },
        {
            key: 'created_at', label: 'Дата подписки',
            render: (row) => new Date(row.created_at).toLocaleDateString('ru-RU'),
        },
        {
            key: 'actions', label: '', className: 'text-right',
            render: (row) => (
                <Button size="sm" variant="destructive" onClick={() => router.delete(`/admin/subscriber/${row.id}`, { preserveScroll: true })}>
                    <Trash2 className="h-4 w-4" />
                </Button>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Рассылки" />
            <div className="space-y-4">
                <h1 className="text-xl font-semibold">Подписчики</h1>
                <DataTable data={subscribers.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination currentPage={subscribers.current_page} lastPage={subscribers.last_page} path={subscribers.path} />
                </div>
            </div>
        </AppLayout>
    );
}
