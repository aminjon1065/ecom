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

interface FlashSaleItem {
    id: number;
    end_date: string;
    status: boolean;
    show_at_main: boolean;
    created_at: string;
    product: { id: number; name: string; thumb_image: string; price: number };
}

interface Product { id: number; name: string; }

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Распродажа', href: '/admin/flash-sale' },
];

interface Props {
    flashSales: PaginatedResponse<FlashSaleItem>;
    products: Product[];
}

export default function FlashSaleIndex({ flashSales, products }: Props) {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, reset } = useForm({
        product_id: '',
        end_date: '',
        status: true,
        show_at_main: true,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post('/admin/flash-sale', {
            onSuccess: () => { setOpen(false); reset(); toast.success('Распродажа добавлена'); },
        });
    }

    const columns: Column<FlashSaleItem>[] = [
        {
            key: 'product', label: 'Товар',
            render: (row) => (
                <div className="flex items-center gap-3">
                    <img src={row.product.thumb_image} alt={row.product.name} className="h-10 w-10 rounded object-cover" />
                    <span className="font-medium">{row.product.name}</span>
                </div>
            ),
        },
        { key: 'end_date', label: 'Окончание', render: (row) => new Date(row.end_date).toLocaleDateString('ru-RU') },
        {
            key: 'status', label: 'Активен',
            render: (row) => (
                <Switch checked={row.status} onCheckedChange={() =>
                    router.patch(`/admin/flash-sale/${row.id}/status`, {}, { preserveScroll: true, onSuccess: () => toast.success('Статус обновлён') })
                } />
            ),
        },
        {
            key: 'show_at_main', label: 'На главной',
            render: (row) => <Badge variant={row.show_at_main ? 'default' : 'secondary'}>{row.show_at_main ? 'Да' : 'Нет'}</Badge>,
        },
        {
            key: 'actions', label: '', className: 'text-right',
            render: (row) => (
                <Button size="sm" variant="destructive" onClick={() => router.delete(`/admin/flash-sale/${row.id}`, { preserveScroll: true })}>
                    <Trash2 className="h-4 w-4" />
                </Button>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Распродажа" />
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Распродажа</h1>
                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild><Button><Plus className="mr-2 h-4 w-4" />Добавить</Button></DialogTrigger>
                        <DialogContent>
                            <DialogHeader><DialogTitle>Новая распродажа</DialogTitle></DialogHeader>
                            <form onSubmit={submit} className="space-y-4">
                                <div className="space-y-2">
                                    <Label>Товар</Label>
                                    <Select value={data.product_id} onValueChange={(v) => setData('product_id', v)}>
                                        <SelectTrigger><SelectValue placeholder="Выберите товар" /></SelectTrigger>
                                        <SelectContent>{products.map((p) => (<SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>))}</SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label>Дата окончания</Label>
                                    <Input type="date" value={data.end_date} onChange={(e) => setData('end_date', e.target.value)} />
                                </div>
                                <div className="flex items-center justify-between rounded-md border p-3">
                                    <Label>Активен</Label>
                                    <Switch checked={data.status} onCheckedChange={(v) => setData('status', v)} />
                                </div>
                                <div className="flex items-center justify-between rounded-md border p-3">
                                    <Label>На главной</Label>
                                    <Switch checked={data.show_at_main} onCheckedChange={(v) => setData('show_at_main', v)} />
                                </div>
                                <Button type="submit" disabled={processing} className="w-full">Сохранить</Button>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
                <DataTable data={flashSales.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination currentPage={flashSales.current_page} lastPage={flashSales.last_page} path={flashSales.path} />
                </div>
            </div>
        </AppLayout>
    );
}
