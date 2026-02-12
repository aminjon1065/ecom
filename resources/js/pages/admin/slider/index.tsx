import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { PaginatedResponse } from '@/types/pagination';
import { Head, router, useForm } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Slider {
    id: number;
    banner: string;
    type: string;
    title: string;
    starting_price: string;
    btn_url: string;
    serial: number;
    status: boolean;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Слайдеры', href: '/admin/slider' },
];

interface Props { sliders: PaginatedResponse<Slider>; }

export default function SliderIndex({ sliders }: Props) {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, reset } = useForm({
        banner: null as File | null, type: '', title: '', starting_price: '', btn_url: '', serial: 1, status: true,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post('/admin/slider', { forceFormData: true, onSuccess: () => { setOpen(false); reset(); toast.success('Слайдер добавлен'); } });
    }

    const columns: Column<Slider>[] = [
        { key: 'serial', label: '№' },
        {
            key: 'banner', label: 'Баннер',
            render: (row) => <img src={`/storage/${row.banner}`} alt={row.title} className="h-14 w-28 rounded object-cover" />,
        },
        { key: 'title', label: 'Заголовок' },
        { key: 'type', label: 'Тип' },
        { key: 'starting_price', label: 'Цена от' },
        {
            key: 'status', label: 'Активен',
            render: (row) => (
                <Switch checked={row.status} onCheckedChange={() =>
                    router.patch(`/admin/slider/${row.id}/status`, {}, { preserveScroll: true, onSuccess: () => toast.success('Статус обновлён') })
                } />
            ),
        },
        {
            key: 'actions', label: '', className: 'text-right',
            render: (row) => (
                <Button size="sm" variant="destructive" onClick={() => router.delete(`/admin/slider/${row.id}`, { preserveScroll: true })}>
                    <Trash2 className="h-4 w-4" />
                </Button>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Слайдеры" />
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Слайдеры</h1>
                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild><Button><Plus className="mr-2 h-4 w-4" />Добавить</Button></DialogTrigger>
                        <DialogContent>
                            <DialogHeader><DialogTitle>Новый слайдер</DialogTitle></DialogHeader>
                            <form onSubmit={submit} className="space-y-4">
                                <div className="space-y-2"><Label>Баннер</Label><Input type="file" accept="image/*" onChange={(e) => setData('banner', e.target.files?.[0] ?? null)} /></div>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2"><Label>Заголовок</Label><Input value={data.title} onChange={(e) => setData('title', e.target.value)} /></div>
                                    <div className="space-y-2"><Label>Тип</Label><Input value={data.type} onChange={(e) => setData('type', e.target.value)} /></div>
                                    <div className="space-y-2"><Label>Цена от</Label><Input value={data.starting_price} onChange={(e) => setData('starting_price', e.target.value)} /></div>
                                    <div className="space-y-2"><Label>Ссылка кнопки</Label><Input value={data.btn_url} onChange={(e) => setData('btn_url', e.target.value)} /></div>
                                    <div className="space-y-2"><Label>Порядок</Label><Input type="number" value={data.serial} onChange={(e) => setData('serial', Number(e.target.value))} /></div>
                                </div>
                                <div className="flex items-center justify-between rounded-md border p-3">
                                    <Label>Активен</Label>
                                    <Switch checked={data.status} onCheckedChange={(v) => setData('status', v)} />
                                </div>
                                <Button type="submit" disabled={processing} className="w-full">Сохранить</Button>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
                <DataTable data={sliders.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination currentPage={sliders.current_page} lastPage={sliders.last_page} path={sliders.path} />
                </div>
            </div>
        </AppLayout>
    );
}
