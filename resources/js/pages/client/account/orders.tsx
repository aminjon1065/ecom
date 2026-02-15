import { Pagination } from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import GetStatusBadge from '@/helper/getStatusBadge';
import AppAccountLayout from '@/layouts/app/client/account/app-account-layout';
import { Link } from '@inertiajs/react';

interface Order {
    id: number;
    invoice_id: number;
    amount: number;
    order_status: string;
    payment_status: boolean;
    created_at: string;
    products: {
        id: number;
        product: {
            id: number;
            name: string;
            thumb_image: string;
        };
    }[];
}

interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

interface Props {
    orders: PaginatedData<Order>;
}

export default function AccountOrders({ orders }: Props) {
    return (
        <AppAccountLayout
            activePath={'/account/orders'}
            title={'Мои заказы'}
        >
            <Card>
                <CardHeader>
                    <CardTitle>Все заказы</CardTitle>
                </CardHeader>
                <CardContent>
                    {orders.data.length > 0 ? (
                        <div className="space-y-4">
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="px-2 py-3 text-left">
                                                Номер
                                            </th>
                                            <th className="px-2 py-3 text-left">
                                                Сумма
                                            </th>
                                            <th className="px-2 py-3 text-left">
                                                Статус заказа
                                            </th>
                                            <th className="px-2 py-3 text-left">
                                                Оплата
                                            </th>
                                            <th className="px-2 py-3 text-left">
                                                Дата
                                            </th>
                                            <th className="px-2 py-3 text-right">
                                                Действия
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {orders.data.map((order) => (
                                            <tr
                                                key={order.id}
                                                className="border-b hover:bg-muted/50"
                                            >
                                                <td className="px-2 py-3 font-medium">
                                                    #{order.invoice_id}
                                                </td>
                                                <td className="px-2 py-3 font-semibold">
                                                    {order.amount.toLocaleString()}{' '}
                                                    сом
                                                </td>
                                                <td className="px-2 py-3">
                                                    <GetStatusBadge
                                                        order_status={
                                                            order.order_status
                                                        }
                                                    />
                                                </td>
                                                <td className="px-2 py-3">
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
                                                </td>
                                                <td className="px-2 py-3 text-sm text-muted-foreground">
                                                    {new Date(
                                                        order.created_at,
                                                    ).toLocaleDateString(
                                                        'ru-RU',
                                                        {
                                                            year: 'numeric',
                                                            month: 'long',
                                                            day: 'numeric',
                                                        },
                                                    )}
                                                </td>
                                                <td className="px-2 py-3 text-right">
                                                    <Link
                                                        href={`/account/orders/${order.id}`}
                                                    >
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                        >
                                                            Подробнее
                                                        </Button>
                                                    </Link>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {orders.last_page > 1 && (
                                <div className="mt-6 flex justify-center">
                                    <Pagination
                                        currentPage={orders.current_page}
                                        lastPage={orders.last_page}
                                        path="/account/orders"
                                    />
                                </div>
                            )}
                        </div>
                    ) : (
                        <div className="py-12 text-center text-muted-foreground">
                            <p>У вас пока нет заказов</p>
                        </div>
                    )}
                </CardContent>
            </Card>
        </AppAccountLayout>
    );
}
