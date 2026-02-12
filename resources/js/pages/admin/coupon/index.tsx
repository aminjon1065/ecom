import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
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

interface Props { coupons: PaginatedResponse<Coupon>; }

export default function CouponIndex({ coupons }: Props) {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, reset } = useForm({
        name: '', code: '', quantity: 100, max_use: 1,
        start_date: '', end_date: '', discount_type: 'percent', discount: 0, status: true,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post('/admin/coupon', { onSuccess: () => { setOpen(false); reset(); toast.success('Купон добавлен'); } });
    }

    const columns: Column<Coupon>[] = [
        { key: 'name', label: 'Название' },
        { key: 'code', label: 'Код', render: (row) => <span className="font-mono text-sm">{row.code}</span> },
        {
            key: 'discount', label: 'Скидка',
            render: (row) => row.discount_type === 'percent' ? `${row.discount}%` : `${row.discount} сом.`,
        },
        { key: 'quantity', label: 'Кол-во' },
        {
            key: 'total_used', label: 'Использовано',
            render: (row) => <Badge variant="outline">{row.total_used}/{row.quantity}</Badge>,
        },
        { key: 'start_date', label: 'Начало', render: (row) => new Date(row.start_date).toLocaleDateString('ru-RU') },
        { key: 'end_date', label: 'Конец', render: (row) => new Date(row.end_date).toLocaleDateString('ru-RU') },
        {
            key: 'status', label: 'Активен',
            render: (row) => (
                <Switch checked={row.status} onCheckedChange={() =>
                    router.patch(`/admin/coupon/${row.id}/status`, {}, { preserveScroll: true, onSuccess: () => toast.success('Статус обновлён') })
                } />
            ),
        },
        {
            key: 'actions', label: '', className: 'text-right',
            render: (row) => (
                <Button size="sm" variant="destructive" onClick={() => router.delete(`/admin/coupon/${row.id}`, { preserveScroll: true })}>
                    <Trash2 className="h-4 w-4" />
                </Button>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Купоны" />
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Купоны</h1>
                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild><Button><Plus className="mr-2 h-4 w-4" />Добавить</Button></DialogTrigger>
                        <DialogContent className="max-w-lg">
                            <DialogHeader><DialogTitle>Новый купон</DialogTitle></DialogHeader>
                            <form onSubmit={submit} className="space-y-4">
                                <div className="grid gap-4 md:grid-cols-2">
                                    <div className="space-y-2"><Label>Название</Label><Input value={data.name} onChange={(e) => setData('name', e.target.value)} /></div>
                                    <div className="space-y-2"><Label>Код</Label><Input value={data.code} onChange={(e) => setData('code', e.target.value.toUpperCase())} /></div>
                                    <div className="space-y-2"><Label>Количество</Label><Input type="number" value={data.quantity} onChange={(e) => setData('quantity', Number(e.target.value))} /></div>
                                    <div className="space-y-2"><Label>Макс. использований</Label><Input type="number" value={data.max_use} onChange={(e) => setData('max_use', Number(e.target.value))} /></div>
                                    <div className="space-y-2">
                                        <Label>Тип скидки</Label>
                                        <Select value={data.discount_type} onValueChange={(v) => setData('discount_type', v)}>
                                            <SelectTrigger><SelectValue /></SelectTrigger>
                                            <SelectContent><SelectItem value="percent">Процент (%)</SelectItem><SelectItem value="fixed">Фиксированный (сом.)</SelectItem></SelectContent>
                                        </Select>
                                    </div>
                                    <div className="space-y-2"><Label>Скидка</Label><Input type="number" value={data.discount} onChange={(e) => setData('discount', Number(e.target.value))} /></div>
                                    <div className="space-y-2"><Label>Дата начала</Label><Input type="date" value={data.start_date} onChange={(e) => setData('start_date', e.target.value)} /></div>
                                    <div className="space-y-2"><Label>Дата окончания</Label><Input type="date" value={data.end_date} onChange={(e) => setData('end_date', e.target.value)} /></div>
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
                <DataTable data={coupons.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination currentPage={coupons.current_page} lastPage={coupons.last_page} path={coupons.path} />
                </div>
            </div>
        </AppLayout>
    );
}
