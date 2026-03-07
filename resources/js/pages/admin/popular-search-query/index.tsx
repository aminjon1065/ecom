import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { PaginatedResponse } from '@/types/pagination';
import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

interface PopularSearchQuery {
    id: number;
    query: string;
    priority: number;
    is_active: boolean;
    created_at: string;
}

interface Props {
    queries: PaginatedResponse<PopularSearchQuery>;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Популярные запросы', href: '/admin/popular-searches' },
];

export default function PopularSearchQueryIndex({ queries }: Props) {
    const columns: Column<PopularSearchQuery>[] = [
        {
            key: 'query',
            label: 'Запрос',
            render: (row) => <span className="font-medium">{row.query}</span>,
        },
        {
            key: 'priority',
            label: 'Приоритет',
        },
        {
            key: 'is_active',
            label: 'Активен',
            render: (row) => (
                <Switch
                    checked={row.is_active}
                    onCheckedChange={() =>
                        router.patch(`/admin/popular-searches/${row.id}/status`, {}, {
                            preserveScroll: true,
                            onSuccess: () => toast.success('Статус обновлён'),
                        })
                    }
                />
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
                <div className="flex justify-end gap-2">
                    <Button asChild size="sm" variant="outline">
                        <Link href={`/admin/popular-searches/${row.id}/edit`}>
                            <Pencil className="h-4 w-4" />
                        </Link>
                    </Button>
                    <Button
                        size="sm"
                        variant="destructive"
                        onClick={() => router.delete(`/admin/popular-searches/${row.id}`, { preserveScroll: true })}
                    >
                        <Trash2 className="h-4 w-4" />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Популярные запросы" />
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Популярные запросы</h1>
                    <Button asChild>
                        <Link href="/admin/popular-searches/create">
                            <Plus className="mr-2 h-4 w-4" />
                            Добавить
                        </Link>
                    </Button>
                </div>
                <DataTable data={queries.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination currentPage={queries.current_page} lastPage={queries.last_page} path={queries.path} />
                </div>
            </div>
        </AppLayout>
    );
}
