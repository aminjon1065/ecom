import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';

interface OrderProduct {
    id: number;
    quantity: number;
    unit_price: number;
    product: { id: number; name: string; thumb_image: string; price: number };
}

interface Order {
    id: number;
    invoice_id: number;
    transaction_id: string;
    amount: number;
    product_quantity: number;
    payment_method: string;
    payment_status: boolean;
    coupon: string | null;
    order_status: string;
    created_at: string;
    user: { id: number; name: string; email: string; phone: string | null; telegram_username: string | null };
    products: OrderProduct[];
}

const STATUS_LABELS: Record<string, string> = {
    pending: 'Ожидает',
    processing: 'В обработке',
    shipped: 'Отправлен',
    delivered: 'Доставлен',
    cancelled: 'Отменён',
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Заказы', href: '/admin/order' },
    { title: 'Детали заказа', href: '#' },
];

export default function OrderShow({ order }: { order: Order }) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Заказ #${order.invoice_id}`} />
            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">
                        Заказ #{order.invoice_id}
                    </h1>
                    <Badge
                        variant={order.payment_status ? 'default' : 'secondary'}
                    >
                        {order.payment_status ? 'Оплачен' : 'Не оплачен'}
                    </Badge>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>Информация о заказе</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">
                                    Номер транзакции
                                </span>
                                <span className="font-mono">
                                    {order.transaction_id}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">
                                    Способ оплаты
                                </span>
                                <span className="capitalize">
                                    {order.payment_method}
                                </span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">
                                    Дата заказа
                                </span>
                                <span>
                                    {new Date(
                                        order.created_at,
                                    ).toLocaleDateString('ru-RU')}
                                </span>
                            </div>
                            {order.coupon && (
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Купон
                                    </span>
                                    <span>{order.coupon}</span>
                                </div>
                            )}
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Статус
                                </span>
                                <Select
                                    value={order.order_status}
                                    onValueChange={(v) =>
                                        router.patch(
                                            `/admin/order/${order.id}/status`,
                                            { order_status: v },
                                            { preserveScroll: true },
                                        )
                                    }
                                >
                                    <SelectTrigger className="w-[160px]">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.entries(STATUS_LABELS).map(
                                            ([val, label]) => (
                                                <SelectItem
                                                    key={val}
                                                    value={val}
                                                >
                                                    {label}
                                                </SelectItem>
                                            ),
                                        )}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">
                                    Оплата
                                </span>
                                <Button
                                    size="sm"
                                    variant={
                                        order.payment_status
                                            ? 'default'
                                            : 'outline'
                                    }
                                    onClick={() =>
                                        router.patch(
                                            `/admin/order/${order.id}/payment`,
                                            {},
                                            { preserveScroll: true },
                                        )
                                    }
                                >
                                    {order.payment_status
                                        ? 'Оплачен'
                                        : 'Отметить оплату'}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Информация о клиенте</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-3 text-sm">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">
                                    Имя
                                </span>
                                <span>{order.user.name}</span>
                            </div>
                            {order.user.telegram_username && (
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Telegram
                                    </span>
                                    <a
                                        href={`https://t.me/${order.user.telegram_username}`}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="text-blue-600 hover:underline"
                                    >
                                        @{order.user.telegram_username}
                                    </a>
                                </div>
                            )}
                            {!order.user.email.endsWith('@telegram.local') && (
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Email
                                    </span>
                                    <span>{order.user.email}</span>
                                </div>
                            )}
                            {order.user.phone && (
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        Телефон
                                    </span>
                                    <a
                                        href={`tel:${order.user.phone}`}
                                        className="text-blue-600 hover:underline"
                                    >
                                        {order.user.phone}
                                    </a>
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Товары ({order.product_quantity})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="divide-y">
                            {order.products.map((item) => (
                                <div
                                    key={item.id}
                                    className="flex items-center gap-4 py-3"
                                >
                                    <img
                                        src={item.product.thumb_image}
                                        alt={item.product.name}
                                        className="h-14 w-14 rounded object-cover"
                                    />
                                    <div className="min-w-0 flex-1">
                                        <div className="font-medium">
                                            {item.product.name}
                                        </div>
                                        <div className="text-sm text-muted-foreground">
                                            {item.unit_price.toLocaleString(
                                                'ru-RU',
                                            )}{' '}
                                            сом. x {item.quantity}
                                        </div>
                                    </div>
                                    <div className="font-semibold">
                                        {(
                                            item.unit_price * item.quantity
                                        ).toLocaleString('ru-RU')}{' '}
                                        сом.
                                    </div>
                                </div>
                            ))}
                        </div>
                        <div className="mt-4 border-t pt-4 text-right">
                            <span className="text-lg font-bold">
                                Итого: {order.amount.toLocaleString('ru-RU')}{' '}
                                сом.
                            </span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
