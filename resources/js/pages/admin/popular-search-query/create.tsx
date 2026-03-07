import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Популярные запросы', href: '/admin/popular-searches' },
    { title: 'Создать', href: '/admin/popular-searches/create' },
];

export default function PopularSearchQueryCreate() {
    const { data, setData, post, processing, errors } = useForm({
        query: '',
        priority: 0,
        is_active: true,
    });

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/admin/popular-searches');
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Создать популярный запрос" />
            <div className="mx-auto w-full max-w-2xl space-y-6">
                <div className="flex items-center justify-between gap-3">
                    <h1 className="text-xl font-semibold">Новый популярный запрос</h1>
                    <Button asChild variant="outline">
                        <Link href="/admin/popular-searches">Назад</Link>
                    </Button>
                </div>

                <form onSubmit={submit} className="space-y-4 rounded-lg border p-5">
                    <div className="space-y-2">
                        <Label htmlFor="query">Запрос</Label>
                        <Input
                            id="query"
                            value={data.query}
                            onChange={(event) => setData('query', event.target.value)}
                            placeholder="Например: iPhone"
                        />
                        {errors.query && <p className="text-sm text-destructive">{errors.query}</p>}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="priority">Приоритет</Label>
                        <Input
                            id="priority"
                            type="number"
                            min={0}
                            value={data.priority}
                            onChange={(event) => setData('priority', Number(event.target.value || 0))}
                        />
                        {errors.priority && <p className="text-sm text-destructive">{errors.priority}</p>}
                    </div>

                    <div className="flex items-center justify-between rounded-md border p-3">
                        <p className="text-sm">Активный запрос</p>
                        <Switch
                            checked={data.is_active}
                            onCheckedChange={(checked) => setData('is_active', checked)}
                        />
                    </div>

                    <div className="flex justify-end gap-2">
                        <Button asChild variant="outline" type="button">
                            <Link href="/admin/popular-searches">Отмена</Link>
                        </Button>
                        <Button type="submit" disabled={processing}>
                            Сохранить
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
