import { CouponForm } from '@/components/admin/coupon/coupon-form';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';

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
}

interface Props {
    coupon: Coupon;
}

function normalizeDate(value: string): string {
    return value ? value.slice(0, 10) : '';
}

export default function CouponEdit({ coupon }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Дашборд', href: dashboard().url },
        { title: 'Купоны', href: '/admin/coupon' },
        { title: coupon.code, href: `/admin/coupon/${coupon.id}/edit` },
    ];

    const { data, setData, put, processing, errors } = useForm({
        name: coupon.name,
        code: coupon.code,
        quantity: coupon.quantity,
        max_use: coupon.max_use,
        start_date: normalizeDate(coupon.start_date),
        end_date: normalizeDate(coupon.end_date),
        discount_type: coupon.discount_type,
        discount: coupon.discount,
        status: coupon.status,
    });

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        put(`/admin/coupon/${coupon.id}`);
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Редактировать ${coupon.code}`} />
            <div className="mx-auto w-full max-w-3xl space-y-6">
                <div className="flex items-center justify-between gap-3">
                    <h1 className="text-xl font-semibold">Редактирование купона</h1>
                    <Button asChild variant="outline">
                        <Link href="/admin/coupon">Назад</Link>
                    </Button>
                </div>

                <CouponForm
                    data={data}
                    errors={errors}
                    processing={processing}
                    submitLabel="Обновить"
                    cancelHref="/admin/coupon"
                    onSubmit={submit}
                    onChange={(field, value) => setData(field, value as never)}
                />
            </div>
        </AppLayout>
    );
}
