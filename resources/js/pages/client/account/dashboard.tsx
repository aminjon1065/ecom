import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import GetStatusBadge from '@/helper/getStatusBadge';
import AppAccountLayout from '@/layouts/app/client/account/app-account-layout';
import { Head, Link } from '@inertiajs/react';
import { CheckCircle, Clock, Package, Wallet } from 'lucide-react';

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

interface Props {
    stats: {
        totalOrders: number;
        pendingOrders: number;
        completedOrders: number;
        totalSpent: number;
    };
    recentOrders: Order[];
}
export default function AccountDashboard({ stats, recentOrders }: Props) {
    return (
        <AppAccountLayout activePath={'/account'} title={'Личный кабинет'}>
            <Head title="Личный кабинет" />
            {/* Stats Cards */}
            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Всего заказов
                        </CardTitle>
                        <Package className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {stats.totalOrders}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            В обработке
                        </CardTitle>
                        <Clock className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {stats.pendingOrders}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Завершено
                        </CardTitle>
                        <CheckCircle className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {stats.completedOrders}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle className="text-sm font-medium">
                            Потрачено
                        </CardTitle>
                        <Wallet className="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div className="text-2xl font-bold">
                            {stats.totalSpent.toLocaleString()} сом
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Recent Orders */}
            <Card>
                <CardHeader>
                    <CardTitle>Последние заказы</CardTitle>
                </CardHeader>
                <CardContent>
                    {recentOrders.length > 0 ? (
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
                                                Статус
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
                                        {recentOrders.map((order) => (
                                            <tr
                                                key={order.id}
                                                className="border-b hover:bg-muted/50"
                                            >
                                                <td className="px-2 py-3 font-medium">
                                                    #{order.invoice_id}
                                                </td>
                                                <td className="px-2 py-3">
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

                            <div className="flex justify-end">
                                <Link href="/account/orders">
                                    <Button variant="outline">
                                        Все заказы
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    ) : (
                        <div className="py-8 text-center text-muted-foreground">
                            У вас пока нет заказов
                        </div>
                    )}
                </CardContent>
            </Card>
        </AppAccountLayout>
    );
}
