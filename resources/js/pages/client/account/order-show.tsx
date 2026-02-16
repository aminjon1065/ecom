import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Calendar, CreditCard, Download, Hash, Package } from 'lucide-react';

interface OrderProduct {
    id: number;
    quantity: number;
    unit_price: number;
    product: {
        id: number;
        name: string;
        slug: string;
        thumb_image: string;
        price: number;
    };
}

interface Order {
    id: number;
    invoice_id: number;
    amount: number;
    order_status: string;
    payment_status: boolean;
    created_at: string;
    transaction_id: string;
    payment_method: string;
    coupon?: string;
    product_quantity: number;
    products: OrderProduct[];
}

interface Props {
    order: Order;
}

function getStatusBadge(status: string) {
    const statusMap: Record<
        string,
        {
            label: string;
            variant: 'default' | 'secondary' | 'destructive' | 'outline';
        }
    > = {
        pending: { label: 'В обработке', variant: 'secondary' },
        processing: { label: 'Обрабатывается', variant: 'default' },
        delivered: { label: 'Доставлен', variant: 'default' },
        completed: { label: 'Завершен', variant: 'default' },
        cancelled: { label: 'Отменен', variant: 'destructive' },
    };

    const config = statusMap[status] || {
        label: status,
        variant: 'outline' as const,
    };
    return <Badge variant={config.variant}>{config.label}</Badge>;
}

export default function OrderShow({ order }: Props) {
    const subtotal = order.products.reduce(
        (sum, item) => sum + item.unit_price * item.quantity,
        0,
    );

    return (
        <AppHeaderLayout>
            <Head title={`Заказ #${order.invoice_id}`} />

            <div className="container mx-auto px-4 py-8">
                <div className="mb-6 flex items-center justify-between">
                    <Link href="/account/orders">
                        <Button variant="ghost" size="sm">
                            <ArrowLeft className="mr-2 h-4 w-4" />
                            Назад к заказам
                        </Button>
                    </Link>
                    <a href={`/account/orders/${order.id}/invoice`}>
                        <Button variant="outline" size="sm">
                            <Download className="mr-2 h-4 w-4" />
                            Скачать чек
                        </Button>
                    </a>
                </div>

                <h1 className="mb-8 text-3xl font-bold">
                    Заказ #{order.invoice_id}
                </h1>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Order Details */}
                    <div className="space-y-6 lg:col-span-2">
                        {/* Order Info Card */}
                        <Card>
                            <CardHeader>
                                <CardTitle>Информация о заказе</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div className="flex items-start gap-3">
                                        <Hash className="mt-0.5 h-5 w-5 text-muted-foreground" />
                                        <div>
                                            <p className="text-sm text-muted-foreground">
                                                Номер заказа
                                            </p>
                                            <p className="font-semibold">
                                                #{order.invoice_id}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-3">
                                        <Package className="mt-0.5 h-5 w-5 text-muted-foreground" />
                                        <div>
                                            <p className="text-sm text-muted-foreground">
                                                Статус заказа
                                            </p>
                                            <div className="mt-1">
                                                {getStatusBadge(
                                                    order.order_status,
                                                )}
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-3">
                                        <CreditCard className="mt-0.5 h-5 w-5 text-muted-foreground" />
                                        <div>
                                            <p className="text-sm text-muted-foreground">
                                                Статус оплаты
                                            </p>
                                            <Badge
                                                variant={
                                                    order.payment_status
                                                        ? 'default'
                                                        : 'secondary'
                                                }
                                                className="mt-1"
                                            >
                                                {order.payment_status
                                                    ? 'Оплачен'
                                                    : 'Не оплачен'}
                                            </Badge>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-3">
                                        <Calendar className="mt-0.5 h-5 w-5 text-muted-foreground" />
                                        <div>
                                            <p className="text-sm text-muted-foreground">
                                                Дата заказа
                                            </p>
                                            <p className="font-semibold">
                                                {new Date(
                                                    order.created_at,
                                                ).toLocaleDateString('ru-RU', {
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric',
                                                })}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <Separator />

                                <div className="grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div>
                                        <p className="text-sm text-muted-foreground">
                                            ID транзакции
                                        </p>
                                        <p className="font-medium">
                                            {order.transaction_id}
                                        </p>
                                    </div>

                                    <div>
                                        <p className="text-sm text-muted-foreground">
                                            Способ оплаты
                                        </p>
                                        <p className="font-medium">
                                            {order.payment_method}
                                        </p>
                                    </div>

                                    {order.coupon && (
                                        <div>
                                            <p className="text-sm text-muted-foreground">
                                                Промокод
                                            </p>
                                            <p className="font-medium">
                                                {order.coupon}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Products Card */}
                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Товары ({order.product_quantity})
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="overflow-x-auto">
                                    <table className="w-full">
                                        <thead>
                                            <tr className="border-b">
                                                <th className="px-2 py-3 text-left">
                                                    Товар
                                                </th>
                                                <th className="px-2 py-3 text-center">
                                                    Количество
                                                </th>
                                                <th className="px-2 py-3 text-right">
                                                    Цена
                                                </th>
                                                <th className="px-2 py-3 text-right">
                                                    Итого
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {order.products.map((item) => (
                                                <tr
                                                    key={item.id}
                                                    className="border-b"
                                                >
                                                    <td className="px-2 py-4">
                                                        <div className="flex items-center gap-3">
                                                            <img
                                                                src={
                                                                    item.product
                                                                        .thumb_image
                                                                }
                                                                alt={
                                                                    item.product
                                                                        .name
                                                                }
                                                                className="h-16 w-16 rounded object-cover"
                                                            />
                                                            <div>
                                                                <Link
                                                                    href={`/products/${item.product.slug}`}
                                                                    className="font-medium hover:text-primary"
                                                                >
                                                                    {
                                                                        item
                                                                            .product
                                                                            .name
                                                                    }
                                                                </Link>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td className="px-2 py-4 text-center">
                                                        <span className="font-medium">
                                                            {item.quantity}
                                                        </span>
                                                    </td>
                                                    <td className="px-2 py-4 text-right">
                                                        {item.unit_price.toLocaleString()}{' '}
                                                        сом
                                                    </td>
                                                    <td className="px-2 py-4 text-right font-semibold">
                                                        {(
                                                            item.unit_price *
                                                            item.quantity
                                                        ).toLocaleString()}{' '}
                                                        сом
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Order Summary */}
                    <div className="lg:col-span-1">
                        <Card className="sticky top-4">
                            <CardHeader>
                                <CardTitle>Итого</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <div className="flex justify-between">
                                        <span className="text-muted-foreground">
                                            Подытог:
                                        </span>
                                        <span className="font-medium">
                                            {subtotal.toLocaleString()} сом
                                        </span>
                                    </div>

                                    {order.coupon && (
                                        <div className="flex justify-between text-green-600">
                                            <span>Скидка (промокод):</span>
                                            <span className="font-medium">
                                                -
                                                {(
                                                    subtotal - order.amount
                                                ).toLocaleString()}{' '}
                                                сом
                                            </span>
                                        </div>
                                    )}
                                </div>

                                <Separator />

                                <div className="flex justify-between text-lg font-bold">
                                    <span>Общая сумма:</span>
                                    <span>
                                        {order.amount.toLocaleString()} сом
                                    </span>
                                </div>

                                <div className="pt-2">
                                    <div className="rounded-md bg-muted p-3 text-sm">
                                        <p className="mb-1 font-medium">
                                            Статус оплаты:
                                        </p>
                                        <Badge
                                            variant={
                                                order.payment_status
                                                    ? 'default'
                                                    : 'secondary'
                                            }
                                        >
                                            {order.payment_status
                                                ? 'Оплачен'
                                                : 'Не оплачен'}
                                        </Badge>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppHeaderLayout>
    );
}
