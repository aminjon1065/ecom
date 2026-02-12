import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { PaginatedResponse } from '@/types/pagination';
import { Head, router } from '@inertiajs/react';
import { Star, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Review {
    id: number;
    review: string;
    rating: number;
    status: boolean;
    created_at: string;
    product: { id: number; name: string; thumb_image: string };
    user: { id: number; name: string; email: string };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Отзывы', href: '/admin/review' },
];

interface Props {
    reviews: PaginatedResponse<Review>;
    filters: { status?: string; rating?: string };
}

export default function ReviewIndex({ reviews, filters: initialFilters }: Props) {
    const [filters, setFilters] = useState({
        status: initialFilters.status ?? 'all',
        rating: initialFilters.rating ?? 'all',
    });

    function applyFilters() {
        router.get('/admin/review', {
            status: filters.status === 'all' ? undefined : filters.status,
            rating: filters.rating === 'all' ? undefined : filters.rating,
            page: 1,
        }, { preserveScroll: true, preserveState: true });
    }

    const columns: Column<Review>[] = [
        {
            key: 'product', label: 'Товар',
            render: (row) => (
                <div className="flex items-center gap-3">
                    <img src={row.product.thumb_image} alt={row.product.name} className="h-10 w-10 rounded object-cover" />
                    <span className="text-sm font-medium">{row.product.name}</span>
                </div>
            ),
        },
        {
            key: 'user', label: 'Пользователь',
            render: (row) => (
                <div>
                    <div className="font-medium">{row.user.name}</div>
                    <div className="text-xs text-muted-foreground">{row.user.email}</div>
                </div>
            ),
        },
        {
            key: 'rating', label: 'Оценка',
            render: (row) => (
                <div className="flex items-center gap-0.5">
                    {Array.from({ length: 5 }).map((_, i) => (
                        <Star key={i} className={`h-3.5 w-3.5 ${i < row.rating ? 'fill-yellow-400 text-yellow-400' : 'text-muted-foreground/30'}`} />
                    ))}
                </div>
            ),
        },
        {
            key: 'review', label: 'Отзыв',
            render: (row) => <p className="max-w-xs truncate text-sm text-muted-foreground">{row.review}</p>,
        },
        {
            key: 'status', label: 'Статус',
            render: (row) => (
                <Switch
                    checked={row.status}
                    onCheckedChange={() =>
                        router.patch(`/admin/review/${row.id}/status`, {}, {
                            preserveScroll: true,
                            onSuccess: () => toast.success('Статус обновлён'),
                        })
                    }
                />
            ),
        },
        {
            key: 'created_at', label: 'Дата',
            render: (row) => new Date(row.created_at).toLocaleDateString('ru-RU'),
        },
        {
            key: 'actions', label: '', className: 'text-right',
            render: (row) => (
                <Button size="sm" variant="destructive" onClick={() => router.delete(`/admin/review/${row.id}`, { preserveScroll: true })}>
                    <Trash2 className="h-4 w-4" />
                </Button>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Отзывы" />
            <div className="space-y-4">
                <h1 className="text-xl font-semibold">Отзывы</h1>
                <div className="rounded-lg border p-4">
                    <div className="grid gap-4 md:grid-cols-3">
                        <Select value={filters.status} onValueChange={(v) => setFilters((f) => ({ ...f, status: v }))}>
                            <SelectTrigger><SelectValue placeholder="Статус" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Все</SelectItem>
                                <SelectItem value="1">Одобренные</SelectItem>
                                <SelectItem value="0">На модерации</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select value={filters.rating} onValueChange={(v) => setFilters((f) => ({ ...f, rating: v }))}>
                            <SelectTrigger><SelectValue placeholder="Оценка" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Все оценки</SelectItem>
                                {[5, 4, 3, 2, 1].map((r) => (
                                    <SelectItem key={r} value={String(r)}>{r} {r === 1 ? 'звезда' : 'звёзд'}</SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <div className="flex gap-2">
                            <Button onClick={applyFilters}>Применить</Button>
                            <Button variant="outline" onClick={() => { setFilters({ status: 'all', rating: 'all' }); router.get('/admin/review'); }}>Сбросить</Button>
                        </div>
                    </div>
                </div>
                <DataTable data={reviews.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination currentPage={reviews.current_page} lastPage={reviews.last_page} path={reviews.path} />
                </div>
            </div>
        </AppLayout>
    );
}
