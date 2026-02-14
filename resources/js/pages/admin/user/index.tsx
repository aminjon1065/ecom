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

interface UserItem {
    id: number;
    name: string;
    email: string;
    avatar: string | null;
    phone: string | null;
    telegram_username: string | null;
    is_active: boolean;
    created_at: string;
    roles: { id: number; name: string }[];
}

const ROLE_LABELS: Record<string, string> = { admin: 'Админ', vendor: 'Продавец', user: 'Покупатель' };

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Пользователи', href: '/admin/user' },
];

interface Props {
    users: PaginatedResponse<UserItem>;
    filters: { search?: string; role?: string };
}

export default function UserIndex({ users, filters: initialFilters }: Props) {
    const [filters, setFilters] = useState({
        search: initialFilters.search ?? '',
        role: initialFilters.role ?? 'all',
    });

    function applyFilters() {
        router.get('/admin/user', {
            search: filters.search || undefined,
            role: filters.role === 'all' ? undefined : filters.role,
            page: 1,
        }, { preserveScroll: true, preserveState: true });
    }

    const columns: Column<UserItem>[] = [
        {
            key: 'name', label: 'Пользователь',
            render: (row) => (
                <div className="flex items-center gap-3">
                    {row.avatar ? (
                        <img src={row.avatar} alt={row.name} className="h-8 w-8 rounded-full object-cover" />
                    ) : (
                        <div className="flex h-8 w-8 items-center justify-center rounded-full bg-muted text-xs font-medium">
                            {row.name.charAt(0).toUpperCase()}
                        </div>
                    )}
                    <div>
                        <div className="font-medium">{row.name}</div>
                        <div className="text-xs text-muted-foreground">
                            {row.telegram_username
                                ? `@${row.telegram_username}`
                                : row.email}
                        </div>
                    </div>
                </div>
            ),
        },
        {
            key: 'phone', label: 'Контакт',
            render: (row) => row.phone
                ? row.phone
                : row.telegram_username
                    ? <a href={`https://t.me/${row.telegram_username}`} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">@{row.telegram_username}</a>
                    : '—',
        },
        {
            key: 'roles', label: 'Роль',
            render: (row) => (
                <div className="flex gap-1">
                    {row.roles.map((r) => (
                        <Badge key={r.id} variant="outline">{ROLE_LABELS[r.name] || r.name}</Badge>
                    ))}
                </div>
            ),
        },
        {
            key: 'is_active', label: 'Активен',
            render: (row) => (
                <Switch checked={row.is_active} onCheckedChange={() =>
                    router.patch(`/admin/user/${row.id}/active`, {}, { preserveScroll: true, onSuccess: () => toast.success('Статус обновлён') })
                } />
            ),
        },
        {
            key: 'created_at', label: 'Дата регистрации',
            render: (row) => new Date(row.created_at).toLocaleDateString('ru-RU'),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Пользователи" />
            <div className="space-y-4">
                <h1 className="text-xl font-semibold">Пользователи</h1>
                <div className="rounded-lg border p-4">
                    <div className="grid gap-4 md:grid-cols-3">
                        <Input placeholder="Поиск: имя, email, телефон" value={filters.search}
                            onChange={(e) => setFilters((f) => ({ ...f, search: e.target.value }))}
                            onKeyDown={(e) => e.key === 'Enter' && applyFilters()} />
                        <Select value={filters.role} onValueChange={(v) => setFilters((f) => ({ ...f, role: v }))}>
                            <SelectTrigger><SelectValue placeholder="Роль" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Все роли</SelectItem>
                                <SelectItem value="admin">Админ</SelectItem>
                                <SelectItem value="vendor">Продавец</SelectItem>
                                <SelectItem value="user">Покупатель</SelectItem>
                            </SelectContent>
                        </Select>
                        <div className="flex gap-2">
                            <Button onClick={applyFilters}>Применить</Button>
                            <Button variant="outline" onClick={() => { setFilters({ search: '', role: 'all' }); router.get('/admin/user'); }}>Сбросить</Button>
                        </div>
                    </div>
                </div>
                <DataTable data={users.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination currentPage={users.current_page} lastPage={users.last_page} path={users.path} />
                </div>
            </div>
        </AppLayout>
    );
}
