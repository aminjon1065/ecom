import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Link } from '@inertiajs/react';

export interface CouponFormData {
    name: string;
    code: string;
    quantity: number;
    max_use: number;
    start_date: string;
    end_date: string;
    discount_type: string;
    discount: number;
    status: boolean;
}

type CouponField = keyof CouponFormData;

interface Props {
    data: CouponFormData;
    errors: Record<string, string>;
    processing: boolean;
    submitLabel: string;
    cancelHref: string;
    onSubmit: (event: React.FormEvent<HTMLFormElement>) => void;
    onChange: (field: CouponField, value: string | number | boolean) => void;
}

export function CouponForm({ data, errors, processing, submitLabel, cancelHref, onSubmit, onChange }: Props) {
    return (
        <form onSubmit={onSubmit} className="space-y-6 rounded-xl border p-4 md:p-6">
            <div className="grid gap-4 md:grid-cols-2">
                <div className="space-y-2">
                    <Label htmlFor="name">Название</Label>
                    <Input id="name" value={data.name} onChange={(event) => onChange('name', event.target.value)} />
                    {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="code">Код</Label>
                    <Input id="code" value={data.code} onChange={(event) => onChange('code', event.target.value.toUpperCase())} />
                    {errors.code && <p className="text-sm text-destructive">{errors.code}</p>}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="quantity">Количество</Label>
                    <Input
                        id="quantity"
                        type="number"
                        value={data.quantity}
                        onChange={(event) => onChange('quantity', Number(event.target.value))}
                    />
                    {errors.quantity && <p className="text-sm text-destructive">{errors.quantity}</p>}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="max_use">Макс. использований</Label>
                    <Input
                        id="max_use"
                        type="number"
                        value={data.max_use}
                        onChange={(event) => onChange('max_use', Number(event.target.value))}
                    />
                    {errors.max_use && <p className="text-sm text-destructive">{errors.max_use}</p>}
                </div>

                <div className="space-y-2">
                    <Label>Тип скидки</Label>
                    <Select value={data.discount_type} onValueChange={(value) => onChange('discount_type', value)}>
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="percent">Процент (%)</SelectItem>
                            <SelectItem value="fixed">Фиксированный (сом.)</SelectItem>
                        </SelectContent>
                    </Select>
                    {errors.discount_type && <p className="text-sm text-destructive">{errors.discount_type}</p>}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="discount">Скидка</Label>
                    <Input id="discount" type="number" value={data.discount} onChange={(event) => onChange('discount', Number(event.target.value))} />
                    {errors.discount && <p className="text-sm text-destructive">{errors.discount}</p>}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="start_date">Дата начала</Label>
                    <Input id="start_date" type="date" value={data.start_date} onChange={(event) => onChange('start_date', event.target.value)} />
                    {errors.start_date && <p className="text-sm text-destructive">{errors.start_date}</p>}
                </div>

                <div className="space-y-2">
                    <Label htmlFor="end_date">Дата окончания</Label>
                    <Input id="end_date" type="date" value={data.end_date} onChange={(event) => onChange('end_date', event.target.value)} />
                    {errors.end_date && <p className="text-sm text-destructive">{errors.end_date}</p>}
                </div>
            </div>

            <div className="flex items-center justify-between rounded-md border p-3">
                <Label htmlFor="status">Активен</Label>
                <Switch id="status" checked={data.status} onCheckedChange={(value) => onChange('status', value)} />
            </div>

            <div className="flex justify-end gap-3">
                <Button asChild variant="outline">
                    <Link href={cancelHref}>Отмена</Link>
                </Button>
                <Button type="submit" disabled={processing}>
                    {submitLabel}
                </Button>
            </div>
        </form>
    );
}
