import { Head, Link, router, usePage } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { useState, FormEvent, useEffect } from 'react';
import { ShoppingBag } from 'lucide-react';

interface CartItem {
    id: number;
    product_id: number;
    quantity: number;
    product: {
        id: number;
        name: string;
        slug: string;
        thumb_image: string;
        price: number;
        offer_price?: number | null;
        offer_start_date?: string | null;
        offer_end_date?: string | null;
        qty: number;
        status: boolean;
    };
}

interface Address {
    id: number;
    address: string;
    description?: string | null;
}

interface ShippingRule {
    id: number;
    name: string;
    type: 'flat' | 'free_shipping' | 'min_cost';
    min_cost: number | null;
    cost: number;
    status: boolean;
}

interface AppliedCoupon {
    code: string;
    discount_type: 'percent' | 'fixed';
    discount: number;
}

interface CheckoutProps {
    cartItems: CartItem[];
    addresses: Address[];
    shippingRules: ShippingRule[];
    flash?: { appliedCoupon?: AppliedCoupon | null };
}

const TYPE_LABELS: Record<string, string> = {
    flat: 'Фиксированная',
    free_shipping: 'Бесплатная',
    min_cost: 'По мин. сумме',
};

export default function Checkout({ cartItems, addresses, shippingRules, flash }: CheckoutProps) {
    const [selectedAddressId, setSelectedAddressId] = useState<number | null>(
        addresses.length > 0 ? addresses[0].id : null
    );
    const [selectedShippingId, setSelectedShippingId] = useState<number | null>(
        shippingRules.length > 0 ? shippingRules[0].id : null
    );
    const [selectedPaymentMethod, setSelectedPaymentMethod] = useState<'cash' | 'card'>('cash');
    const [couponCode, setCouponCode] = useState('');
    const [appliedCoupon, setAppliedCoupon] = useState<AppliedCoupon | null>(null);
    const [couponError, setCouponError] = useState<string | null>(null);
    const [showAddAddressForm, setShowAddAddressForm] = useState(false);
    const [newAddress, setNewAddress] = useState({ address: '', description: '' });

    // Pick up coupon from flash (set after applyCoupon redirect)
    useEffect(() => {
        if (flash?.appliedCoupon) {
            setAppliedCoupon(flash.appliedCoupon);
            setCouponError(null);
        }
    }, [flash?.appliedCoupon]);

    const getEffectivePrice = (item: CartItem): number => {
        const { product } = item;
        if (!product.offer_price || !product.offer_start_date || !product.offer_end_date) {
            return product.price;
        }
        const now = new Date();
        if (now >= new Date(product.offer_start_date) && now <= new Date(product.offer_end_date)) {
            return product.offer_price;
        }
        return product.price;
    };

    const calculateSubtotal = (): number =>
        cartItems.reduce((total, item) => total + getEffectivePrice(item) * item.quantity, 0);

    /** Mirror of the backend match() logic */
    const getShippingCost = (): number => {
        if (!selectedShippingId) return 0;
        const rule = shippingRules.find(r => r.id === selectedShippingId);
        if (!rule) return 0;
        switch (rule.type) {
            case 'flat':
                return rule.cost;
            case 'free_shipping':
                return 0;
            case 'min_cost':
                return calculateSubtotal() >= (rule.min_cost ?? 0) ? 0 : rule.cost;
            default:
                return 0;
        }
    };

    const getDiscount = (): number => {
        if (!appliedCoupon) return 0;
        return appliedCoupon.discount_type === 'percent'
            ? calculateSubtotal() * appliedCoupon.discount / 100
            : appliedCoupon.discount;
    };

    const calculateTotal = (): number =>
        Math.max(0, calculateSubtotal() + getShippingCost() - getDiscount());

    const handleApplyCoupon = () => {
        if (!couponCode.trim()) return;
        setCouponError(null);
        router.post('/checkout/coupon', { code: couponCode }, {
            preserveScroll: true,
            onError: (errors) => setCouponError(errors.code ?? 'Ошибка применения купона'),
        });
    };

    const handleAddAddress = (e: FormEvent) => {
        e.preventDefault();
        router.post('/account/addresses', newAddress, {
            onSuccess: () => {
                setNewAddress({ address: '', description: '' });
                setShowAddAddressForm(false);
            },
        });
    };

    const handlePlaceOrder = () => {
        if (!selectedAddressId) {
            alert('Пожалуйста, выберите адрес доставки');
            return;
        }
        if (!selectedShippingId) {
            alert('Пожалуйста, выберите способ доставки');
            return;
        }
        router.post('/checkout', {
            address_id: selectedAddressId,
            payment_method: selectedPaymentMethod,
            shipping_rule_id: selectedShippingId,
            coupon_code: appliedCoupon?.code || null,
        });
    };

    /** Label shown next to shipping rule name in the radio list */
    const shippingRuleCostLabel = (rule: ShippingRule): string => {
        switch (rule.type) {
            case 'free_shipping':
                return 'Бесплатно';
            case 'min_cost':
                return `${rule.cost.toFixed(2)} сом. (при заказе < ${(rule.min_cost ?? 0).toFixed(2)} сом.)`;
            default:
                return `${rule.cost.toFixed(2)} сом.`;
        }
    };

    if (cartItems.length === 0) {
        return (
            <AppHeaderLayout>
                <Head title="Оформление заказа" />
                <div className="container mx-auto px-4 py-12">
                    <div className="flex flex-col items-center justify-center min-h-[400px] text-center">
                        <ShoppingBag className="h-24 w-24 text-muted-foreground mb-6" />
                        <h1 className="text-3xl font-bold mb-4">Корзина пуста</h1>
                        <p className="text-muted-foreground mb-6">
                            Добавьте товары в корзину для оформления заказа
                        </p>
                        <Button asChild>
                            <Link href="/products">Перейти к товарам</Link>
                        </Button>
                    </div>
                </div>
            </AppHeaderLayout>
        );
    }

    const subtotal = calculateSubtotal();
    const shippingCost = getShippingCost();
    const discount = getDiscount();
    const total = calculateTotal();

    return (
        <AppHeaderLayout>
            <Head title="Оформление заказа" />
            <div className="container mx-auto px-4 py-8">
                <h1 className="text-3xl font-bold mb-8">Оформление заказа</h1>

                <div className="grid gap-8 lg:grid-cols-3">
                    <div className="lg:col-span-2 space-y-6">
                        {/* Order Summary */}
                        <Card className="p-6">
                            <h2 className="text-xl font-bold mb-4">Ваш заказ</h2>
                            <Separator className="mb-4" />
                            <div className="space-y-4">
                                {cartItems.map((item) => {
                                    const effectivePrice = getEffectivePrice(item);
                                    return (
                                        <div key={item.id} className="flex gap-4">
                                            <img
                                                src={`/storage/${item.product.thumb_image}`}
                                                alt={item.product.name}
                                                className="h-16 w-16 object-cover rounded"
                                            />
                                            <div className="flex-1">
                                                <Link
                                                    href={`/products/${item.product.slug}`}
                                                    className="font-medium hover:text-primary line-clamp-1"
                                                >
                                                    {item.product.name}
                                                </Link>
                                                <p className="text-sm text-muted-foreground">
                                                    {effectivePrice.toFixed(2)} сом. × {item.quantity}
                                                </p>
                                            </div>
                                            <div className="font-semibold">
                                                {(effectivePrice * item.quantity).toFixed(2)} сом.
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </Card>

                        {/* Address Selection */}
                        <Card className="p-6">
                            <h2 className="text-xl font-bold mb-4">Адрес доставки</h2>
                            <Separator className="mb-4" />

                            {addresses.length > 0 ? (
                                <div className="space-y-3 mb-4">
                                    {addresses.map((address) => (
                                        <label
                                            key={address.id}
                                            className="flex items-start gap-3 p-4 border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors"
                                        >
                                            <input
                                                type="radio"
                                                name="address"
                                                value={address.id}
                                                checked={selectedAddressId === address.id}
                                                onChange={() => setSelectedAddressId(address.id)}
                                                className="mt-1"
                                            />
                                            <div className="flex-1">
                                                <p className="font-medium">{address.address}</p>
                                                {address.description && (
                                                    <p className="text-sm text-muted-foreground mt-1">
                                                        {address.description}
                                                    </p>
                                                )}
                                            </div>
                                        </label>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-muted-foreground mb-4">У вас нет сохраненных адресов</p>
                            )}

                            {!showAddAddressForm ? (
                                <Button
                                    variant="outline"
                                    onClick={() => setShowAddAddressForm(true)}
                                    className="w-full"
                                >
                                    Добавить адрес
                                </Button>
                            ) : (
                                <form onSubmit={handleAddAddress} className="space-y-4 border-t pt-4">
                                    <div>
                                        <Label htmlFor="address">Адрес</Label>
                                        <Input
                                            id="address"
                                            value={newAddress.address}
                                            onChange={(e) => setNewAddress({ ...newAddress, address: e.target.value })}
                                            placeholder="Введите адрес"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <Label htmlFor="description">Описание (необязательно)</Label>
                                        <Input
                                            id="description"
                                            value={newAddress.description}
                                            onChange={(e) => setNewAddress({ ...newAddress, description: e.target.value })}
                                            placeholder="Например: Дом, Офис"
                                        />
                                    </div>
                                    <div className="flex gap-2">
                                        <Button type="submit" className="flex-1">Сохранить</Button>
                                        <Button
                                            type="button"
                                            variant="outline"
                                            onClick={() => setShowAddAddressForm(false)}
                                        >
                                            Отмена
                                        </Button>
                                    </div>
                                </form>
                            )}
                        </Card>

                        {/* Shipping Method */}
                        <Card className="p-6">
                            <h2 className="text-xl font-bold mb-4">Способ доставки</h2>
                            <Separator className="mb-4" />
                            <div className="space-y-3">
                                {shippingRules
                                    .filter(rule => rule.status)
                                    .map((rule) => (
                                        <label
                                            key={rule.id}
                                            className="flex items-center justify-between p-4 border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors"
                                        >
                                            <div className="flex items-center gap-3">
                                                <input
                                                    type="radio"
                                                    name="shipping"
                                                    value={rule.id}
                                                    checked={selectedShippingId === rule.id}
                                                    onChange={() => setSelectedShippingId(rule.id)}
                                                />
                                                <div>
                                                    <p className="font-medium">{rule.name}</p>
                                                    <p className="text-sm text-muted-foreground">
                                                        {TYPE_LABELS[rule.type] ?? rule.type}
                                                    </p>
                                                </div>
                                            </div>
                                            <span className="font-semibold text-right">
                                                {shippingRuleCostLabel(rule)}
                                            </span>
                                        </label>
                                    ))}
                            </div>
                        </Card>

                        {/* Payment Method */}
                        <Card className="p-6">
                            <h2 className="text-xl font-bold mb-4">Способ оплаты</h2>
                            <Separator className="mb-4" />
                            <div className="space-y-3">
                                <label className="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors">
                                    <input
                                        type="radio"
                                        name="payment"
                                        value="cash"
                                        checked={selectedPaymentMethod === 'cash'}
                                        onChange={() => setSelectedPaymentMethod('cash')}
                                    />
                                    <span className="font-medium">Наличные</span>
                                </label>
                                <label className="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-muted/50 transition-colors">
                                    <input
                                        type="radio"
                                        name="payment"
                                        value="card"
                                        checked={selectedPaymentMethod === 'card'}
                                        onChange={() => setSelectedPaymentMethod('card')}
                                    />
                                    <span className="font-medium">Карта</span>
                                </label>
                            </div>
                        </Card>
                    </div>

                    {/* Order Summary Sidebar */}
                    <div className="lg:col-span-1">
                        <Card className="p-6 sticky top-4">
                            <h2 className="text-xl font-bold mb-4">Итого</h2>
                            <Separator className="mb-4" />

                            {/* Coupon Code */}
                            <div className="mb-4">
                                <Label htmlFor="coupon">Промокод</Label>
                                <div className="flex gap-2 mt-2">
                                    <Input
                                        id="coupon"
                                        value={couponCode}
                                        onChange={(e) => setCouponCode(e.target.value)}
                                        placeholder="Введите код"
                                        disabled={!!appliedCoupon}
                                    />
                                    <Button
                                        onClick={handleApplyCoupon}
                                        disabled={!couponCode.trim() || !!appliedCoupon}
                                    >
                                        Применить
                                    </Button>
                                </div>
                                {couponError && (
                                    <p className="text-sm text-destructive mt-1">{couponError}</p>
                                )}
                                {appliedCoupon && (
                                    <div className="mt-2 flex items-center justify-between">
                                        <Badge variant="secondary">
                                            Промокод «{appliedCoupon.code}» применён
                                        </Badge>
                                        <button
                                            type="button"
                                            className="text-xs text-muted-foreground underline"
                                            onClick={() => { setAppliedCoupon(null); setCouponCode(''); }}
                                        >
                                            Убрать
                                        </button>
                                    </div>
                                )}
                            </div>

                            <Separator className="mb-4" />

                            <div className="space-y-3 mb-6">
                                <div className="flex justify-between text-muted-foreground">
                                    <span>Подытог</span>
                                    <span>{subtotal.toFixed(2)} сом.</span>
                                </div>
                                <div className="flex justify-between text-muted-foreground">
                                    <span>Доставка</span>
                                    <span>
                                        {shippingCost === 0
                                            ? <span className="text-green-600">Бесплатно</span>
                                            : `${shippingCost.toFixed(2)} сом.`}
                                    </span>
                                </div>
                                {discount > 0 && (
                                    <div className="flex justify-between text-green-600">
                                        <span>
                                            Скидка{appliedCoupon?.discount_type === 'percent'
                                                ? ` (${appliedCoupon.discount}%)`
                                                : ''}
                                        </span>
                                        <span>-{discount.toFixed(2)} сом.</span>
                                    </div>
                                )}
                                <Separator />
                                <div className="flex justify-between text-lg font-bold">
                                    <span>Всего</span>
                                    <span>{total.toFixed(2)} сом.</span>
                                </div>
                            </div>

                            <Button
                                onClick={handlePlaceOrder}
                                className="w-full"
                                size="lg"
                            >
                                Оформить заказ
                            </Button>

                            <Button
                                asChild
                                variant="outline"
                                className="w-full mt-3"
                            >
                                <Link href="/cart">Вернуться в корзину</Link>
                            </Button>
                        </Card>
                    </div>
                </div>
            </div>
        </AppHeaderLayout>
    );
}
