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
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

interface AuditLogItem {
    id: number;
    action: string;
    model_type: string | null;
    model_id: string | null;
    new_values: Record<string, unknown> | null;
    ip_address: string | null;
    created_at: string;
    user: {
        id: number;
        name: string;
        email: string;
    } | null;
}

interface ModelTypeOption {
    value: string;
    label: string;
}

interface Props {
    logs: PaginatedResponse<AuditLogItem>;
    modelTypes: ModelTypeOption[];
    filters: { search?: string; action?: string; model_type?: string };
}

const ACTION_LABELS: Record<string, string> = {
    POST: 'Создание',
    PATCH: 'Изменение',
    PUT: 'Обновление',
    DELETE: 'Удаление',
};

const ACTION_COLORS: Record<string, string> = {
    POST: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    PATCH: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    PUT: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    DELETE: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Журнал действий', href: '/admin/audit-log' },
];

export default function AuditLogIndex({ logs, modelTypes, filters: initialFilters }: Props) {
    const [filters, setFilters] = useState({
        search: initialFilters.search ?? '',
        action: initialFilters.action ?? 'all',
        model_type: initialFilters.model_type ?? 'all',
    });

    function applyFilters() {
        router.get('/admin/audit-log', {
            search: filters.search || undefined,
            action: filters.action === 'all' ? undefined : filters.action,
            model_type: filters.model_type === 'all' ? undefined : filters.model_type,
            page: 1,
        }, { preserveScroll: true, preserveState: true });
    }

    function modelBasename(fqn: string | null): string {
        if (!fqn) {
            return '—';
        }
        const parts = fqn.split('\\');
        return parts[parts.length - 1];
    }

    const columns: Column<AuditLogItem>[] = [
        {
            key: 'created_at',
            label: 'Дата',
            render: (row) => (
                <span className="whitespace-nowrap text-sm">
                    {new Date(row.created_at).toLocaleString('ru-RU', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                    })}
                </span>
            ),
        },
        {
            key: 'user',
            label: 'Пользователь',
            render: (row) => row.user ? (
                <div>
                    <div className="font-medium">{row.user.name}</div>
                    <div className="text-xs text-muted-foreground">{row.user.email}</div>
                </div>
            ) : <span className="text-muted-foreground">—</span>,
        },
        {
            key: 'action',
            label: 'Действие',
            render: (row) => (
                <Badge className={ACTION_COLORS[row.action] ?? 'bg-gray-100 text-gray-800'} variant="outline">
                    {ACTION_LABELS[row.action] ?? row.action}
                </Badge>
            ),
        },
        {
            key: 'model_type',
            label: 'Модель',
            render: (row) => (
                <span className="text-sm">{modelBasename(row.model_type)}</span>
            ),
        },
        {
            key: 'model_id',
            label: 'ID',
            render: (row) => (
                <span className="font-mono text-sm">{row.model_id ?? '—'}</span>
            ),
        },
        {
            key: 'ip_address',
            label: 'IP',
            render: (row) => (
                <span className="font-mono text-xs text-muted-foreground">{row.ip_address ?? '—'}</span>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Журнал действий" />
            <div className="space-y-4">
                <h1 className="text-xl font-semibold">Журнал действий</h1>
                <div className="rounded-lg border p-4">
                    <div className="grid gap-4 md:grid-cols-4">
                        <Input
                            placeholder="Поиск по имени или email"
                            value={filters.search}
                            onChange={(e) => setFilters((f) => ({ ...f, search: e.target.value }))}
                            onKeyDown={(e) => e.key === 'Enter' && applyFilters()}
                        />
                        <Select value={filters.action} onValueChange={(v) => setFilters((f) => ({ ...f, action: v }))}>
                            <SelectTrigger><SelectValue placeholder="Действие" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Все действия</SelectItem>
                                <SelectItem value="POST">Создание</SelectItem>
                                <SelectItem value="PATCH">Изменение</SelectItem>
                                <SelectItem value="PUT">Обновление</SelectItem>
                                <SelectItem value="DELETE">Удаление</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select value={filters.model_type} onValueChange={(v) => setFilters((f) => ({ ...f, model_type: v }))}>
                            <SelectTrigger><SelectValue placeholder="Модель" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Все модели</SelectItem>
                                {modelTypes.map((mt) => (
                                    <SelectItem key={mt.value} value={mt.value}>{mt.label}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <div className="flex gap-2">
                            <Button onClick={applyFilters}>Применить</Button>
                            <Button variant="outline" onClick={() => {
                                setFilters({ search: '', action: 'all', model_type: 'all' });
                                router.get('/admin/audit-log');
                            }}>Сбросить</Button>
                        </div>
                    </div>
                </div>
                <DataTable data={logs.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination currentPage={logs.current_page} lastPage={logs.last_page} path={logs.path} />
                </div>
            </div>
        </AppLayout>
    );
}
