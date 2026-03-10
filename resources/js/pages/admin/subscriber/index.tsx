import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { PaginatedResponse } from '@/types/pagination';
import { Head, router, useForm, usePage } from '@inertiajs/react';
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

interface Props {
    subscribers: PaginatedResponse<Subscriber>;
    stats: {
        total: number;
        verified: number;
        unverified: number;
    };
}

export default function SubscriberIndex({ subscribers, stats }: Props) {
    const { flash } = usePage<{ flash?: { success?: string | null } }>().props;
    const { data, setData, post, processing, errors, reset } = useForm({
        subject: '',
        body: '',
    });

    const columns: Column<Subscriber>[] = [
        { key: 'email', label: 'Эл. почта' },
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

    const submitNewsletter = (event: React.FormEvent) => {
        event.preventDefault();

        post('/admin/subscriber/send', {
            preserveScroll: true,
            onSuccess: () => reset(),
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Рассылки" />
            <div className="space-y-4">
                <h1 className="text-xl font-semibold">Подписчики</h1>

                <div className="grid gap-3 md:grid-cols-3">
                    <div className="rounded-lg border p-4">
                        <div className="text-sm text-muted-foreground">Всего</div>
                        <div className="text-2xl font-semibold">{stats.total}</div>
                    </div>
                    <div className="rounded-lg border p-4">
                        <div className="text-sm text-muted-foreground">Подтверждённые</div>
                        <div className="text-2xl font-semibold">{stats.verified}</div>
                    </div>
                    <div className="rounded-lg border p-4">
                        <div className="text-sm text-muted-foreground">Не подтверждённые</div>
                        <div className="text-2xl font-semibold">{stats.unverified}</div>
                    </div>
                </div>

                <form onSubmit={submitNewsletter} className="space-y-3 rounded-lg border p-4">
                    <h2 className="text-lg font-medium">Отправить рассылку</h2>
                    <p className="text-sm text-muted-foreground">
                        Письмо уйдёт только подписчикам с подтверждённым email.
                    </p>
                    {flash?.success && (
                        <p className="text-sm text-green-600">{flash.success}</p>
                    )}
                    <div className="space-y-2">
                        <Input
                            placeholder="Тема письма"
                            value={data.subject}
                            onChange={(event) => setData('subject', event.target.value)}
                        />
                        {errors.subject && (
                            <p className="text-sm text-destructive">{errors.subject}</p>
                        )}
                    </div>
                    <div className="space-y-2">
                        <Textarea
                            placeholder="Текст рассылки"
                            value={data.body}
                            onChange={(event) => setData('body', event.target.value)}
                            rows={8}
                        />
                        {errors.body && (
                            <p className="text-sm text-destructive">{errors.body}</p>
                        )}
                    </div>
                    <Button type="submit" disabled={processing}>
                        {processing ? 'Отправка...' : 'Отправить рассылку'}
                    </Button>
                </form>

                <DataTable data={subscribers.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination currentPage={subscribers.current_page} lastPage={subscribers.last_page} path={subscribers.path} />
                </div>
            </div>
        </AppLayout>
    );
}
