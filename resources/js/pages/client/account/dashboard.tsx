import { Head, Link } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { LayoutDashboard, ShoppingBag, MapPin, User, Package, Clock, CheckCircle, Wallet } from 'lucide-react';

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

export default function AccountDashboard({ stats, recentOrders }: Props) {
  return (
    <AppHeaderLayout>
      <Head title="Личный кабинет" />

      <div className="container mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold mb-8">Личный кабинет</h1>

        <div className="flex flex-col lg:flex-row gap-6">
          <AccountSidebar activePath="/account" />

          <div className="flex-1 space-y-6">
            {/* Stats Cards */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Всего заказов</CardTitle>
                  <Package className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats.totalOrders}</div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">В обработке</CardTitle>
                  <Clock className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats.pendingOrders}</div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Завершено</CardTitle>
                  <CheckCircle className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats.completedOrders}</div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                  <CardTitle className="text-sm font-medium">Потрачено</CardTitle>
                  <Wallet className="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats.totalSpent.toLocaleString()} сом</div>
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
                            <th className="text-left py-3 px-2">Номер</th>
                            <th className="text-left py-3 px-2">Сумма</th>
                            <th className="text-left py-3 px-2">Статус</th>
                            <th className="text-left py-3 px-2">Оплата</th>
                            <th className="text-left py-3 px-2">Дата</th>
                            <th className="text-right py-3 px-2">Действия</th>
                          </tr>
                        </thead>
                        <tbody>
                          {recentOrders.map((order) => (
                            <tr key={order.id} className="border-b hover:bg-muted/50">
                              <td className="py-3 px-2 font-medium">#{order.invoice_id}</td>
                              <td className="py-3 px-2">{order.amount.toLocaleString()} сом</td>
                              <td className="py-3 px-2">{getStatusBadge(order.order_status)}</td>
                              <td className="py-3 px-2">
                                <Badge variant={order.payment_status ? 'default' : 'secondary'}>
                                  {order.payment_status ? 'Оплачен' : 'Не оплачен'}
                                </Badge>
                              </td>
                              <td className="py-3 px-2 text-sm text-muted-foreground">
                                {new Date(order.created_at).toLocaleDateString('ru-RU')}
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

                    <div className="flex justify-end">
                      <Link href="/account/orders">
                        <Button variant="outline">Все заказы</Button>
                      </Link>
                    </div>
                  </div>
                ) : (
                  <div className="text-center py-8 text-muted-foreground">
                    У вас пока нет заказов
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
