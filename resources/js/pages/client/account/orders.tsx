import { Head, Link } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Pagination } from '@/components/pagination';
import { LayoutDashboard, ShoppingBag, MapPin, User } from 'lucide-react';

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

function AccountSidebar({ activePath }: { activePath: string }) {
  const navItems = [
    { href: '/account', label: 'Дашборд', icon: LayoutDashboard },
    { href: '/account/orders', label: 'Заказы', icon: ShoppingBag },
    { href: '/account/addresses', label: 'Адреса', icon: MapPin },
    { href: '/account/profile', label: 'Профиль', icon: User },
  ];

  return (
    <aside className="w-full lg:w-64 space-y-2">
      <Card>
        <CardContent className="p-4">
          <nav className="space-y-1">
            {navItems.map((item) => {
              const Icon = item.icon;
              const isActive = activePath === item.href;

              return (
                <Link
                  key={item.href}
                  href={item.href}
                  className={`flex items-center gap-3 px-3 py-2 rounded-md transition-colors ${
                    isActive
                      ? 'bg-primary text-primary-foreground'
                      : 'hover:bg-muted'
                  }`}
                >
                  <Icon className="h-5 w-5" />
                  <span className="font-medium">{item.label}</span>
                </Link>
              );
            })}
          </nav>
        </CardContent>
      </Card>
    </aside>
  );
}

function getStatusBadge(status: string) {
  const statusMap: Record<string, { label: string; variant: 'default' | 'secondary' | 'destructive' | 'outline' }> = {
    pending: { label: 'В обработке', variant: 'secondary' },
    processing: { label: 'Обрабатывается', variant: 'default' },
    delivered: { label: 'Доставлен', variant: 'default' },
    completed: { label: 'Завершен', variant: 'default' },
    cancelled: { label: 'Отменен', variant: 'destructive' },
  };

  const config = statusMap[status] || { label: status, variant: 'outline' as const };
  return <Badge variant={config.variant}>{config.label}</Badge>;
}

export default function AccountOrders({ orders }: Props) {
  return (
    <AppHeaderLayout>
      <Head title="Мои заказы" />

      <div className="container mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold mb-8">Мои заказы</h1>

        <div className="flex flex-col lg:flex-row gap-6">
          <AccountSidebar activePath="/account/orders" />

          <div className="flex-1">
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
                            <th className="text-left py-3 px-2">Номер</th>
                            <th className="text-left py-3 px-2">Сумма</th>
                            <th className="text-left py-3 px-2">Статус заказа</th>
                            <th className="text-left py-3 px-2">Оплата</th>
                            <th className="text-left py-3 px-2">Дата</th>
                            <th className="text-right py-3 px-2">Действия</th>
                          </tr>
                        </thead>
                        <tbody>
                          {orders.data.map((order) => (
                            <tr key={order.id} className="border-b hover:bg-muted/50">
                              <td className="py-3 px-2 font-medium">#{order.invoice_id}</td>
                              <td className="py-3 px-2 font-semibold">
                                {order.amount.toLocaleString()} сом
                              </td>
                              <td className="py-3 px-2">{getStatusBadge(order.order_status)}</td>
                              <td className="py-3 px-2">
                                <Badge variant={order.payment_status ? 'default' : 'secondary'}>
                                  {order.payment_status ? 'Оплачен' : 'Не оплачен'}
                                </Badge>
                              </td>
                              <td className="py-3 px-2 text-sm text-muted-foreground">
                                {new Date(order.created_at).toLocaleDateString('ru-RU', {
                                  year: 'numeric',
                                  month: 'long',
                                  day: 'numeric',
                                })}
                              </td>
                              <td className="py-3 px-2 text-right">
                                <Link href={`/account/orders/${order.id}`}>
                                  <Button variant="ghost" size="sm">
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
                      <div className="flex justify-center mt-6">
                        <Pagination currentPage={orders.current_page} lastPage={orders.last_page} path="/account/orders" />
                      </div>
                    )}
                  </div>
                ) : (
                  <div className="text-center py-12 text-muted-foreground">
                    <p>У вас пока нет заказов</p>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppHeaderLayout>
  );
}
