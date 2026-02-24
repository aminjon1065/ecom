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
import { Pencil, Plus, Trash2 } from 'lucide-react';
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
    flat: 'Фиксированная',
    free_shipping: 'Бесплатная',
    min_cost: 'Мин. сумма',
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Правила доставки', href: '/admin/shipping-rule' },
];

interface Props { shippingRules: PaginatedResponse<ShippingRule>; }

export default function ShippingRuleIndex({ shippingRules }: Props) {
    // ── Create ──────────────────────────────────────────────────────────────
    const [createOpen, setCreateOpen] = useState(false);
    const createForm = useForm({ name: '', type: 'flat', min_cost: 0 as number | null, cost: 0, status: true });

    function handleCreate(e: React.FormEvent) {
        e.preventDefault();
        createForm.post('/admin/shipping-rule', {
            onSuccess: () => { setCreateOpen(false); createForm.reset(); toast.success('Правило добавлено'); },
        });
    }

    // ── Edit ────────────────────────────────────────────────────────────────
    const [editOpen, setEditOpen] = useState(false);
    const [editingId, setEditingId] = useState<number | null>(null);
    const editForm = useForm({ name: '', type: 'flat', min_cost: 0 as number | null, cost: 0, status: true });

    function openEdit(rule: ShippingRule) {
        setEditingId(rule.id);
        editForm.setData({
            name: rule.name,
            type: rule.type,
            min_cost: rule.min_cost,
            cost: rule.cost,
            status: rule.status,
        });
        setEditOpen(true);
    }

    function handleEdit(e: React.FormEvent) {
        e.preventDefault();
        if (!editingId) return;
        editForm.put(`/admin/shipping-rule/${editingId}`, {
            onSuccess: () => { setEditOpen(false); toast.success('Правило обновлено'); },
        });
    }

    // ── Columns ─────────────────────────────────────────────────────────────
    const columns: Column<ShippingRule>[] = [
        { key: 'name', label: 'Название' },
        {
            key: 'type', label: 'Тип',
            render: (row) => <Badge variant="outline">{TYPE_LABELS[row.type] ?? row.type}</Badge>,
        },
        {
            key: 'min_cost', label: 'Мин. сумма',
            render: (row) => (row.min_cost != null ? `${row.min_cost} сом.` : '—'),
        },
        { key: 'cost', label: 'Стоимость', render: (row) => `${row.cost} сом.` },
        {
            key: 'status', label: 'Активен',
            render: (row) => (
                <Switch
                    checked={row.status}
                    onCheckedChange={() =>
                        router.patch(`/admin/shipping-rule/${row.id}/status`, {}, {
                            preserveScroll: true,
                            onSuccess: () => toast.success('Статус обновлён'),
                        })
                    }
                />
            ),
        },
        {
            key: 'actions', label: '', className: 'text-right',
            render: (row) => (
                <div className="flex items-center justify-end gap-2">
                    <Button size="sm" variant="outline" onClick={() => openEdit(row)}>
                        <Pencil className="h-4 w-4" />
                    </Button>
                    <Button
                        size="sm"
                        variant="destructive"
                        onClick={() => router.delete(`/admin/shipping-rule/${row.id}`, { preserveScroll: true })}
                    >
                        <Trash2 className="h-4 w-4" />
                    </Button>
                </div>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Правила доставки" />
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Правила доставки</h1>

                    {/* Create dialog */}
                    <Dialog open={createOpen} onOpenChange={setCreateOpen}>
                        <DialogTrigger asChild>
                            <Button><Plus className="mr-2 h-4 w-4" />Добавить</Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader><DialogTitle>Новое правило</DialogTitle></DialogHeader>
                            <ShippingRuleForm
                                data={createForm.data}
                                setData={createForm.setData}
                                processing={createForm.processing}
                                onSubmit={handleCreate}
                                submitLabel="Сохранить"
                            />
                        </DialogContent>
                    </Dialog>
                </div>

                <DataTable data={shippingRules.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination
                        currentPage={shippingRules.current_page}
                        lastPage={shippingRules.last_page}
                        path={shippingRules.path}
                    />
                </div>
            </div>

            {/* Edit dialog — outside the table to avoid stacking-context issues */}
            <Dialog open={editOpen} onOpenChange={setEditOpen}>
                <DialogContent>
                    <DialogHeader><DialogTitle>Редактировать правило</DialogTitle></DialogHeader>
                    <ShippingRuleForm
                        data={editForm.data}
                        setData={editForm.setData}
                        processing={editForm.processing}
                        onSubmit={handleEdit}
                        submitLabel="Обновить"
                    />
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}

// ── Shared form component ─────────────────────────────────────────────────────
interface FormData {
    name: string;
    type: string;
    min_cost: number | null;
    cost: number;
    status: boolean;
}

function ShippingRuleForm({
    data,
    setData,
    processing,
    onSubmit,
    submitLabel,
}: {
    data: FormData;
    setData: (key: keyof FormData, value: any) => void;
    processing: boolean;
    onSubmit: (e: React.FormEvent) => void;
    submitLabel: string;
}) {
    return (
        <form onSubmit={onSubmit} className="space-y-4">
            <div className="space-y-2">
                <Label>Название</Label>
                <Input value={data.name} onChange={(e) => setData('name', e.target.value)} required />
            </div>

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

            {/* min_cost threshold — only for min_cost type */}
            {data.type === 'min_cost' && (
                <div className="space-y-2">
                    <Label>Мин. сумма заказа для бесплатной доставки (сом.)</Label>
                    <Input
                        type="number"
                        min={0}
                        step="0.01"
                        value={data.min_cost ?? 0}
                        onChange={(e) => setData('min_cost', Number(e.target.value))}
                    />
                </div>
            )}

            {/* Shipping cost — hidden for free_shipping (always 0) */}
            {data.type !== 'free_shipping' && (
                <div className="space-y-2">
                    <Label>
                        {data.type === 'min_cost'
                            ? 'Стоимость доставки если заказ ниже минимума (сом.)'
                            : 'Стоимость доставки (сом.)'}
                    </Label>
                    <Input
                        type="number"
                        min={0}
                        step="0.01"
                        value={data.cost}
                        onChange={(e) => setData('cost', Number(e.target.value))}
                    />
                </div>
            )}

            <div className="flex items-center justify-between rounded-md border p-3">
                <Label>Активен</Label>
                <Switch checked={data.status} onCheckedChange={(v) => setData('status', v)} />
            </div>

            <Button type="submit" disabled={processing} className="w-full">{submitLabel}</Button>
        </form>
    );
}
