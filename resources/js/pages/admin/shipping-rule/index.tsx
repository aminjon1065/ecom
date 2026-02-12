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

interface ShippingRule {
    id: number;
    name: string;
    type: string;
    min_cost: number | null;
    cost: number;
    status: boolean;
    created_at: string;
}

const TYPE_LABELS: Record<string, string> = {
    flat: 'Фиксированная', free_shipping: 'Бесплатная', min_cost: 'Мин. сумма',
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Правила доставки', href: '/admin/shipping-rule' },
];

interface Props { shippingRules: PaginatedResponse<ShippingRule>; }

export default function ShippingRuleIndex({ shippingRules }: Props) {
    const [open, setOpen] = useState(false);
    const { data, setData, post, processing, reset } = useForm({
        name: '', type: 'flat', min_cost: 0, cost: 0, status: true,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post('/admin/shipping-rule', { onSuccess: () => { setOpen(false); reset(); toast.success('Правило добавлено'); } });
    }

    const columns: Column<ShippingRule>[] = [
        { key: 'name', label: 'Название' },
        { key: 'type', label: 'Тип', render: (row) => <Badge variant="outline">{TYPE_LABELS[row.type] || row.type}</Badge> },
        { key: 'min_cost', label: 'Мин. сумма', render: (row) => row.min_cost ? `${row.min_cost} сом.` : '—' },
        { key: 'cost', label: 'Стоимость', render: (row) => `${row.cost} сом.` },
        {
            key: 'status', label: 'Активен',
            render: (row) => (
                <Switch checked={row.status} onCheckedChange={() =>
                    router.patch(`/admin/shipping-rule/${row.id}/status`, {}, { preserveScroll: true, onSuccess: () => toast.success('Статус обновлён') })
                } />
            ),
        },
        {
            key: 'actions', label: '', className: 'text-right',
            render: (row) => (
                <Button size="sm" variant="destructive" onClick={() => router.delete(`/admin/shipping-rule/${row.id}`, { preserveScroll: true })}>
                    <Trash2 className="h-4 w-4" />
                </Button>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Правила доставки" />
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Правила доставки</h1>
                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild><Button><Plus className="mr-2 h-4 w-4" />Добавить</Button></DialogTrigger>
                        <DialogContent>
                            <DialogHeader><DialogTitle>Новое правило</DialogTitle></DialogHeader>
                            <form onSubmit={submit} className="space-y-4">
                                <div className="space-y-2"><Label>Название</Label><Input value={data.name} onChange={(e) => setData('name', e.target.value)} /></div>
                                <div className="space-y-2">
                                    <Label>Тип</Label>
                                    <Select value={data.type} onValueChange={(v) => setData('type', v)}>
                                        <SelectTrigger><SelectValue /></SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="flat">Фиксированная</SelectItem>
                                            <SelectItem value="free_shipping">Бесплатная</SelectItem>
                                            <SelectItem value="min_cost">По мин. сумме</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                {data.type === 'min_cost' && (
                                    <div className="space-y-2"><Label>Мин. сумма заказа</Label><Input type="number" value={data.min_cost} onChange={(e) => setData('min_cost', Number(e.target.value))} /></div>
                                )}
                                <div className="space-y-2"><Label>Стоимость доставки</Label><Input type="number" value={data.cost} onChange={(e) => setData('cost', Number(e.target.value))} /></div>
                                <div className="flex items-center justify-between rounded-md border p-3">
                                    <Label>Активен</Label>
                                    <Switch checked={data.status} onCheckedChange={(v) => setData('status', v)} />
                                </div>
                                <Button type="submit" disabled={processing} className="w-full">Сохранить</Button>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>
                <DataTable data={shippingRules.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination currentPage={shippingRules.current_page} lastPage={shippingRules.last_page} path={shippingRules.path} />
                </div>
            </div>
        </AppLayout>
    );
}
