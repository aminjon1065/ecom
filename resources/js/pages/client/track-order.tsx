import { Head, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import GetStatusBadge from '@/helper/getStatusBadge';
import type { SharedData } from '@/types';
import { Check, Clock, Download, Loader2, Package, Search, Truck, X, XCircle } from 'lucide-react';

interface OrderProduct {
    id: number;
    quantity: number;
    unit_price: number;
    product: {
        id: number;
        name: string;
        slug: string;
        thumb_image: string;
    } | null;
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
    products: OrderProduct[];
}

interface Props {
    order: Order | null;
    error: string | null;
    invoiceId: string | null;
}

const statusSteps = [
    { key: 'pending', label: 'В обработке', icon: Clock },
    { key: 'processing', label: 'Обрабатывается', icon: Package },
    { key: 'shipped', label: 'Отправлен', icon: Truck },
    { key: 'delivered', label: 'Доставлен', icon: Check },
];

function getStepIndex(status: string): number {
    if (status === 'cancelled') return -1;
    const idx = statusSteps.findIndex((s) => s.key === status);
    return idx >= 0 ? idx : 0;
}

export default function TrackOrder({ order, error, invoiceId }: Props) {
    const { auth } = usePage<SharedData>().props;
    const [query, setQuery] = useState(invoiceId || '');
    const [loading, setLoading] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        if (!query.trim()) return;

        setLoading(true);
        router.get(
            '/track-order',
            { invoice_id: query.trim() },
            {
                preserveState: true,
                preserveScroll: false,
                onFinish: () => setLoading(false),
            },
        );
    };

    const currentStep = order ? getStepIndex(order.order_status) : -1;
    const isCancelled = order?.order_status === 'cancelled';

    return (
        <AppHeaderLayout>
            <Head title="Отслеживание заказа" />

            <div className="mx-auto max-w-3xl px-4 py-8">
                {/* Search Section */}
                <div className="mb-8 text-center">
                    <h1 className="mb-2 text-2xl font-bold">Отслеживание заказа</h1>
                    <p className="mb-6 text-sm text-muted-foreground">
                        Введите номер заказа, чтобы узнать его статус
                    </p>

                    <form onSubmit={handleSubmit} className="mx-auto flex max-w-md gap-2">
                        <div className="relative flex-1">
                            <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                type="text"
                                placeholder="Номер заказа (ID)"
                                value={query}
                                onChange={(e) => setQuery(e.target.value)}
                                className="pl-10"
                            />
                        </div>
                        <Button type="submit" disabled={loading || !query.trim()}>
                            {loading ? (
                                <Loader2 className="h-4 w-4 animate-spin" />
                            ) : (
                                'Найти'
                            )}
                        </Button>
                    </form>
                </div>

                {/* Error */}
                {error && (
                    <Card className="border-destructive/50 bg-destructive/5">
                        <CardContent className="flex items-center gap-3 py-6">
                            <XCircle className="h-5 w-5 text-destructive" />
                            <p className="text-sm text-destructive">{error}</p>
                        </CardContent>
                    </Card>
                )}

                {/* Order Found */}
                {order && (
                    <div className="space-y-6">
                        {/* Status Timeline */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center justify-between text-base">
                                    <span>Заказ #{order.invoice_id}</span>
                                    <GetStatusBadge order_status={order.order_status} />
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                {isCancelled ? (
                                    <div className="flex flex-col items-center gap-2 py-4 text-destructive">
                                        <X className="h-10 w-10 rounded-full bg-destructive/10 p-2" />
                                        <p className="text-sm font-medium">Заказ отменен</p>
                                    </div>
                                ) : (
                                    <div className="py-4">
                                        {/* Desktop timeline */}
                                        <div className="hidden sm:block">
                                            <div className="relative flex items-center justify-between">
                                                {/* Line */}
                                                <div className="absolute left-0 right-0 top-5 h-0.5 bg-muted" />
                                                <div
                                                    className="absolute left-0 top-5 h-0.5 bg-primary transition-all duration-500"
                                                    style={{
                                                        width: `${(currentStep / (statusSteps.length - 1)) * 100}%`,
                                                    }}
                                                />

                                                {statusSteps.map((step, i) => {
                                                    const done = i <= currentStep;
                                                    const active = i === currentStep;
                                                    const Icon = step.icon;

                                                    return (
                                                        <div key={step.key} className="relative z-10 flex flex-col items-center gap-2">
                                                            <div
                                                                className={`flex h-10 w-10 items-center justify-center rounded-full border-2 transition-colors ${
                                                                    done
                                                                        ? 'border-primary bg-primary text-primary-foreground'
                                                                        : 'border-muted bg-background text-muted-foreground'
                                                                } ${active ? 'ring-4 ring-primary/20' : ''}`}
                                                            >
                                                                <Icon className="h-5 w-5" />
                                                            </div>
                                                            <span
                                                                className={`text-xs ${
                                                                    done ? 'font-medium text-foreground' : 'text-muted-foreground'
                                                                }`}
                                                            >
                                                                {step.label}
                                                            </span>
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        </div>

                                        {/* Mobile timeline (vertical) */}
                                        <div className="sm:hidden">
                                            <div className="space-y-0">
                                                {statusSteps.map((step, i) => {
                                                    const done = i <= currentStep;
                                                    const active = i === currentStep;
                                                    const Icon = step.icon;
                                                    const isLast = i === statusSteps.length - 1;

                                                    return (
                                                        <div key={step.key} className="flex gap-3">
                                                            <div className="flex flex-col items-center">
                                                                <div
                                                                    className={`flex h-8 w-8 items-center justify-center rounded-full border-2 ${
                                                                        done
                                                                            ? 'border-primary bg-primary text-primary-foreground'
                                                                            : 'border-muted bg-background text-muted-foreground'
                                                                    } ${active ? 'ring-4 ring-primary/20' : ''}`}
                                                                >
                                                                    <Icon className="h-4 w-4" />
                                                                </div>
                                                                {!isLast && (
                                                                    <div
                                                                        className={`h-8 w-0.5 ${
                                                                            i < currentStep ? 'bg-primary' : 'bg-muted'
                                                                        }`}
                                                                    />
                                                                )}
                                                            </div>
                                                            <div className="pt-1">
                                                                <span
                                                                    className={`text-sm ${
                                                                        done ? 'font-medium text-foreground' : 'text-muted-foreground'
                                                                    }`}
                                                                >
                                                                    {step.label}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </CardContent>
                        </Card>

                        {/* Order Details */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">Информация о заказе</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <dl className="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                                    <div>
                                        <dt className="text-muted-foreground">Номер заказа</dt>
                                        <dd className="font-medium">#{order.invoice_id}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">Дата заказа</dt>
                                        <dd className="font-medium">
                                            {new Date(order.created_at).toLocaleDateString('ru-RU', {
                                                day: 'numeric',
                                                month: 'long',
                                                year: 'numeric',
                                            })}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">Способ оплаты</dt>
                                        <dd className="font-medium">
                                            {order.payment_method === 'cash' ? 'Наличными' : 'Картой'}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">Оплата</dt>
                                        <dd className="font-medium">
                                            {order.payment_status ? (
                                                <span className="text-green-600">Оплачен</span>
                                            ) : (
                                                <span className="text-amber-600">Не оплачен</span>
                                            )}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">Кол-во товаров</dt>
                                        <dd className="font-medium">{order.product_quantity}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-muted-foreground">Сумма</dt>
                                        <dd className="text-lg font-bold text-primary">
                                            {Number(order.amount).toLocaleString('ru-RU')} сом.
                                        </dd>
                                    </div>
                                </dl>
                            </CardContent>
                        </Card>

                        {/* Products */}
                        {order.products.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-base">Товары</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="divide-y">
                                        {order.products.map((item) => (
                                            <div key={item.id} className="flex items-center gap-3 py-3 first:pt-0 last:pb-0">
                                                {item.product?.thumb_image && (
                                                    <img
                                                        src={`/${item.product.thumb_image}`}
                                                        alt={item.product?.name || ''}
                                                        className="h-14 w-14 rounded-md border object-cover"
                                                    />
                                                )}
                                                <div className="flex-1 min-w-0">
                                                    <p className="truncate text-sm font-medium">
                                                        {item.product?.name || 'Товар удален'}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {item.quantity} x {Number(item.unit_price).toLocaleString('ru-RU')} сом.
                                                    </p>
                                                </div>
                                                <p className="text-sm font-medium">
                                                    {(item.quantity * item.unit_price).toLocaleString('ru-RU')} сом.
                                                </p>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Download Invoice (auth users only) */}
                        {auth.user && (
                            <div className="text-center">
                                <a href={`/account/orders/${order.id}/invoice`}>
                                    <Button variant="outline">
                                        <Download className="mr-2 h-4 w-4" />
                                        Скачать чек
                                    </Button>
                                </a>
                            </div>
                        )}
                    </div>
                )}

                {/* Empty state when no search yet */}
                {!order && !error && !invoiceId && (
                    <div className="mt-8 text-center text-muted-foreground">
                        <Package className="mx-auto mb-3 h-12 w-12 opacity-30" />
                        <p className="text-sm">Введите номер заказа для отслеживания</p>
                    </div>
                )}
            </div>
        </AppHeaderLayout>
    );
}
