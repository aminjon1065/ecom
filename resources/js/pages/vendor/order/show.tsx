import VendorLayout from '@/layouts/app/vendor/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

interface OrderProduct {
    id: number;
    quantity: number;
    unit_price: number;
    product: {
        id: number;
        name: string;
        thumb_image: string;
        price: number;
    };
}

interface Order {
    id: number;
    invoice_id: number;
    amount: number;
    product_quantity: number;
    payment_method: string;
    payment_status: boolean;
    order_status: string;
    created_at: string;
    user: { id: number; name: string; email: string };
    products: OrderProduct[];
}

interface Props {
    order: Order;
}

const ORDER_STATUS_LABELS: Record<string, string> = {
    pending: 'Ожидает',
    processing: 'В обработке',
    shipped: 'Отправлен',
    delivered: 'Доставлен',
    cancelled: 'Отменён',
};

function formatCurrency(value: number): string {
    return value.toLocaleString('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('ru-RU');
}

export default function VendorOrderShow({ order }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Дашборд', href: '/vendor' },
        { title: 'Заказы', href: '/vendor/orders' },
        { title: `#${order.invoice_id}`, href: `/vendor/orders/${order.id}` },
    ];

    const vendorTotal = order.products.reduce((sum, p) => sum + p.quantity * p.unit_price, 0);

    return (
        <VendorLayout breadcrumbs={breadcrumbs}>
            <Head title={`Заказ #${order.invoice_id}`} />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="grid gap-6 md:grid-cols-2">
                    {/* Order Info */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Информация о заказе</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Номер заказа</span>
                                <span className="font-mono font-medium">#{order.invoice_id}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Дата</span>
                                <span>{formatDate(order.created_at)}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Способ оплаты</span>
                                <span className="capitalize">{order.payment_method}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Оплата</span>
                                <Badge variant={order.payment_status ? 'default' : 'secondary'}>
                                    {order.payment_status ? 'Оплачен' : 'Не оплачен'}
                                </Badge>
                            </div>
                            <div className="flex items-center justify-between">
                                <span className="text-muted-foreground">Статус заказа</span>
                                <Select
                                    value={order.order_status}
                                    onValueChange={(v) => {
                                        router.patch(`/vendor/orders/${order.id}/status`, { order_status: v }, { preserveScroll: true });
                                    }}
                                >
                                    <SelectTrigger className="w-44">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {Object.entries(ORDER_STATUS_LABELS).map(([key, label]) => (
                                            <SelectItem key={key} value={key}>{label}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Customer Info */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Клиент</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Имя</span>
                                <span className="font-medium">{order.user.name}</span>
                            </div>
                            <div className="flex justify-between">
                                <span className="text-muted-foreground">Email</span>
                                <span>{order.user.email}</span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Products (only this vendor's products) */}
                <Card>
                    <CardHeader>
                        <CardTitle>Ваши товары в заказе</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-3">
                            {order.products.map((item) => (
                                <div key={item.id} className="flex items-center gap-4 rounded-lg border p-3">
                                    <img
                                        src={item.product.thumb_image}
                                        alt={item.product.name}
                                        className="h-14 w-14 rounded object-cover"
                                    />
                                    <div className="min-w-0 flex-1">
                                        <p className="truncate font-medium">{item.product.name}</p>
                                        <p className="text-sm text-muted-foreground">
                                            {formatCurrency(item.unit_price)} сом. &times; {item.quantity}
                                        </p>
                                    </div>
                                    <div className="text-right font-medium">
                                        {formatCurrency(item.unit_price * item.quantity)} сом.
                                    </div>
                                </div>
                            ))}
                        </div>
                        <div className="mt-4 flex justify-end border-t pt-4">
                            <div className="text-right">
                                <p className="text-sm text-muted-foreground">Ваша часть заказа</p>
                                <p className="text-xl font-bold">{formatCurrency(vendorTotal)} сом.</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </VendorLayout>
    );
}
