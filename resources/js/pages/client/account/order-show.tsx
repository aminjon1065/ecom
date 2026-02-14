import { Head, Link } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Package, CreditCard, Calendar, Hash } from 'lucide-react';

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

export default function OrderShow({ order }: Props) {
  const subtotal = order.products.reduce((sum, item) => sum + item.unit_price * item.quantity, 0);

  return (
    <AppHeaderLayout>
      <Head title={`Заказ #${order.invoice_id}`} />

      <div className="container mx-auto px-4 py-8">
        <div className="mb-6">
          <Link href="/account/orders">
            <Button variant="ghost" size="sm">
              <ArrowLeft className="h-4 w-4 mr-2" />
              Назад к заказам
            </Button>
          </Link>
        </div>

        <h1 className="text-3xl font-bold mb-8">Заказ #{order.invoice_id}</h1>

        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Order Details */}
          <div className="lg:col-span-2 space-y-6">
            {/* Order Info Card */}
            <Card>
              <CardHeader>
                <CardTitle>Информация о заказе</CardTitle>
              </CardHeader>
              <CardContent className="space-y-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="flex items-start gap-3">
                    <Hash className="h-5 w-5 text-muted-foreground mt-0.5" />
                    <div>
                      <p className="text-sm text-muted-foreground">Номер заказа</p>
                      <p className="font-semibold">#{order.invoice_id}</p>
                    </div>
                  </div>

                  <div className="flex items-start gap-3">
                    <Package className="h-5 w-5 text-muted-foreground mt-0.5" />
                    <div>
                      <p className="text-sm text-muted-foreground">Статус заказа</p>
                      <div className="mt-1">{getStatusBadge(order.order_status)}</div>
                    </div>
                  </div>

                  <div className="flex items-start gap-3">
                    <CreditCard className="h-5 w-5 text-muted-foreground mt-0.5" />
                    <div>
                      <p className="text-sm text-muted-foreground">Статус оплаты</p>
                      <Badge variant={order.payment_status ? 'default' : 'secondary'} className="mt-1">
                        {order.payment_status ? 'Оплачен' : 'Не оплачен'}
                      </Badge>
                    </div>
                  </div>

                  <div className="flex items-start gap-3">
                    <Calendar className="h-5 w-5 text-muted-foreground mt-0.5" />
                    <div>
                      <p className="text-sm text-muted-foreground">Дата заказа</p>
                      <p className="font-semibold">
                        {new Date(order.created_at).toLocaleDateString('ru-RU', {
                          year: 'numeric',
                          month: 'long',
                          day: 'numeric',
                        })}
                      </p>
                    </div>
                  </div>
                </div>

                <Separator />

                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <p className="text-sm text-muted-foreground">ID транзакции</p>
                    <p className="font-medium">{order.transaction_id}</p>
                  </div>

                  <div>
                    <p className="text-sm text-muted-foreground">Способ оплаты</p>
                    <p className="font-medium">{order.payment_method}</p>
                  </div>

                  {order.coupon && (
                    <div>
                      <p className="text-sm text-muted-foreground">Промокод</p>
                      <p className="font-medium">{order.coupon}</p>
                    </div>
                  )}
                </div>
              </CardContent>
            </Card>

            {/* Products Card */}
            <Card>
              <CardHeader>
                <CardTitle>Товары ({order.product_quantity})</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="overflow-x-auto">
                  <table className="w-full">
                    <thead>
                      <tr className="border-b">
                        <th className="text-left py-3 px-2">Товар</th>
                        <th className="text-center py-3 px-2">Количество</th>
                        <th className="text-right py-3 px-2">Цена</th>
                        <th className="text-right py-3 px-2">Итого</th>
                      </tr>
                    </thead>
                    <tbody>
                      {order.products.map((item) => (
                        <tr key={item.id} className="border-b">
                          <td className="py-4 px-2">
                            <div className="flex items-center gap-3">
                              <img
                                src={item.product.thumb_image}
                                alt={item.product.name}
                                className="h-16 w-16 object-cover rounded"
                              />
                              <div>
                                <Link
                                  href={`/products/${item.product.slug}`}
                                  className="font-medium hover:text-primary"
                                >
                                  {item.product.name}
                                </Link>
                              </div>
                            </div>
                          </td>
                          <td className="py-4 px-2 text-center">
                            <span className="font-medium">{item.quantity}</span>
                          </td>
                          <td className="py-4 px-2 text-right">
                            {item.unit_price.toLocaleString()} сом
                          </td>
                          <td className="py-4 px-2 text-right font-semibold">
                            {(item.unit_price * item.quantity).toLocaleString()} сом
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
                    <span className="text-muted-foreground">Подытог:</span>
                    <span className="font-medium">{subtotal.toLocaleString()} сом</span>
                  </div>

                  {order.coupon && (
                    <div className="flex justify-between text-green-600">
                      <span>Скидка (промокод):</span>
                      <span className="font-medium">-{(subtotal - order.amount).toLocaleString()} сом</span>
                    </div>
                  )}
                </div>

                <Separator />

                <div className="flex justify-between text-lg font-bold">
                  <span>Общая сумма:</span>
                  <span>{order.amount.toLocaleString()} сом</span>
                </div>

                <div className="pt-2">
                  <div className="p-3 bg-muted rounded-md text-sm">
                    <p className="font-medium mb-1">Статус оплаты:</p>
                    <Badge variant={order.payment_status ? 'default' : 'secondary'}>
                      {order.payment_status ? 'Оплачен' : 'Не оплачен'}
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
