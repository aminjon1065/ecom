import { CouponForm } from '@/components/admin/coupon/coupon-form';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Купоны', href: '/admin/coupon' },
    { title: 'Создать', href: '/admin/coupon/create' },
];

export default function CouponCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        code: '',
        quantity: 100,
        max_use: 1,
        start_date: '',
        end_date: '',
        discount_type: 'percent',
        discount: 0,
        status: true,
    });

    function submit(event: React.FormEvent<HTMLFormElement>) {
        event.preventDefault();
        post('/admin/coupon');
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Создать купон" />
            <div className="mx-auto w-full max-w-3xl space-y-6">
                <div className="flex items-center justify-between gap-3">
                    <h1 className="text-xl font-semibold">Новый купон</h1>
                    <Button asChild variant="outline">
                        <Link href="/admin/coupon">Назад</Link>
                    </Button>
                </div>

                <CouponForm
                    data={data}
                    errors={errors}
                    processing={processing}
                    submitLabel="Сохранить"
                    cancelHref="/admin/coupon"
                    onSubmit={submit}
                    onChange={(field, value) => setData(field, value as never)}
                />
            </div>
        </AppLayout>
    );
}
