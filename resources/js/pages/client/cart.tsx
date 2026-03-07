import { Head, Link, router } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { ProductCard } from '@/components/client/product-card';
import { Badge } from '@/components/ui/badge';
import { Minus, Plus, Sparkles, Trash2, ShoppingBag } from 'lucide-react';

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

interface CartProps {
    cartItems: CartItem[];
    recommendedProducts: {
        id: number;
        name: string;
        slug: string;
        thumb_image: string;
        price: number;
        offer_price?: number | null;
        offer_start_date?: string | null;
        offer_end_date?: string | null;
        product_type?: string | null;
        reviews_avg_rating?: number | null;
        reviews_count?: number;
        category?: {
            id: number;
            name: string;
        } | null;
    }[];
    cartSummary: {
        subtotal: number;
        savings: number;
        free_shipping_threshold: number | null;
        remaining_to_free_shipping: number | null;
    };
}

export default function Cart({ cartItems, recommendedProducts, cartSummary }: CartProps) {
    const getEffectivePrice = (item: CartItem): number => {
        const { product } = item;

        if (!product.offer_price || !product.offer_start_date || !product.offer_end_date) {
            return product.price;
        }

        const now = new Date();
        const startDate = new Date(product.offer_start_date);
        const endDate = new Date(product.offer_end_date);

        if (now >= startDate && now <= endDate) {
            return product.offer_price;
        }

        return product.price;
    };

    const calculateSubtotal = (): number => {
        return cartItems.reduce((total, item) => {
            return total + getEffectivePrice(item) * item.quantity;
        }, 0);
    };

    const handleQuantityChange = (cartItemId: number, newQuantity: number) => {
        if (newQuantity < 1) return;

        router.patch(`/cart/${cartItemId}`, {
            quantity: newQuantity,
        });
    };

    const handleRemoveItem = (cartItemId: number) => {
        router.delete(`/cart/${cartItemId}`);
    };

    const handleClearCart = () => {
        router.delete('/cart');
    };

    if (cartItems.length === 0) {
        return (
            <AppHeaderLayout>
                <Head title="Корзина" />
                <div className="container mx-auto px-4 py-12">
                    <div className="flex flex-col items-center justify-center min-h-[400px] text-center">
                        <ShoppingBag className="h-24 w-24 text-muted-foreground mb-6" />
                        <h1 className="text-3xl font-bold mb-4">Корзина пуста</h1>
                        <p className="text-muted-foreground mb-6">
                            Добавьте товары в корзину, чтобы продолжить покупки
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
    const remainingToFreeShipping = cartSummary.remaining_to_free_shipping;
    const freeShippingProgress = cartSummary.free_shipping_threshold && cartSummary.free_shipping_threshold > 0
        ? Math.min(100, Math.round((subtotal / cartSummary.free_shipping_threshold) * 100))
        : null;

    return (
        <AppHeaderLayout>
            <Head title="Корзина" />
            <div className="container mx-auto px-4 py-8 pb-24 lg:pb-8">
                <div className="flex items-center justify-between mb-8">
                    <h1 className="text-3xl font-bold">Корзина</h1>
                    <Button
                        variant="outline"
                        onClick={handleClearCart}
                        className="text-destructive hover:text-destructive"
                    >
                        <Trash2 className="h-4 w-4 mr-2" />
                        Очистить корзину
                    </Button>
                </div>

                <div className="grid gap-8 lg:grid-cols-3">
                    <div className="lg:col-span-2">
                        <Card className="overflow-hidden">
                            <div className="divide-y">
                                {cartItems.map((item) => {
                                    const effectivePrice = getEffectivePrice(item);
                                    const hasOffer = effectivePrice !== item.product.price;

                                    return (
                                        <div key={item.id} className="p-6">
                                            <div className="flex gap-4">
                                                <div className="flex-shrink-0">
                                                    <img
                                                        src={`/storage/${item.product.thumb_image}`}
                                                        alt={item.product.name}
                                                        className="h-24 w-24 object-cover rounded-lg"
                                                    />
                                                </div>

                                                <div className="flex-1 min-w-0">
                                                    <Link
                                                        href={`/products/${item.product.slug}`}
                                                        className="text-lg font-semibold hover:text-primary transition-colors line-clamp-2"
                                                    >
                                                        {item.product.name}
                                                    </Link>

                                                    <div className="mt-2 flex items-center gap-2">
                                                        <span className="text-lg font-bold">
                                                            {effectivePrice.toFixed(2)} сом.
                                                        </span>
                                                        {hasOffer && (
                                                            <span className="text-sm text-muted-foreground line-through">
                                                                {item.product.price.toFixed(2)} сом.
                                                            </span>
                                                        )}
                                                    </div>

                                                    <div className="mt-4 flex items-center gap-4">
                                                        <div className="flex items-center border rounded-lg">
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                className="h-8 w-8 rounded-r-none"
                                                                onClick={() => handleQuantityChange(item.id, item.quantity - 1)}
                                                                disabled={item.quantity <= 1}
                                                            >
                                                                <Minus className="h-4 w-4" />
                                                            </Button>
                                                            <span className="px-4 font-medium min-w-[3rem] text-center">
                                                                {item.quantity}
                                                            </span>
                                                            <Button
                                                                variant="ghost"
                                                                size="icon"
                                                                className="h-8 w-8 rounded-l-none"
                                                                onClick={() => handleQuantityChange(item.id, item.quantity + 1)}
                                                                disabled={item.quantity >= item.product.qty}
                                                            >
                                                                <Plus className="h-4 w-4" />
                                                            </Button>
                                                        </div>

                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            onClick={() => handleRemoveItem(item.id)}
                                                            className="text-destructive hover:text-destructive"
                                                        >
                                                            <Trash2 className="h-4 w-4 mr-2" />
                                                            Удалить
                                                        </Button>
                                                    </div>
                                                </div>

                                                <div className="text-right">
                                                    <p className="text-lg font-bold">
                                                        {(effectivePrice * item.quantity).toFixed(2)} сом.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                        </Card>
                    </div>

                    <div className="lg:col-span-1">
                        <Card className="p-6 sticky top-24">
                            <h2 className="text-xl font-bold mb-4">Итого</h2>
                            <Separator className="mb-4" />

                            {cartSummary.savings > 0 && (
                                <div className="mb-4 rounded-lg border border-primary/20 bg-primary/5 p-3 text-sm">
                                    <div className="flex items-start gap-2">
                                        <Sparkles className="h-4 w-4 mt-0.5 text-primary" />
                                        <p>
                                            Вы уже экономите{' '}
                                            <span className="font-semibold">{cartSummary.savings.toFixed(2)} сом.</span>{' '}
                                            за счёт текущих скидок.
                                        </p>
                                    </div>
                                </div>
                            )}

                            {cartSummary.free_shipping_threshold !== null && (
                                <div className="mb-4 rounded-lg border p-3">
                                    {remainingToFreeShipping !== null && remainingToFreeShipping > 0 ? (
                                        <>
                                            <p className="text-sm">
                                                Добавьте товаров ещё на{' '}
                                                <span className="font-semibold">{remainingToFreeShipping.toFixed(2)} сом.</span>{' '}
                                                для бесплатной доставки.
                                            </p>
                                            {freeShippingProgress !== null && (
                                                <div className="mt-3">
                                                    <div className="h-2 rounded-full bg-muted">
                                                        <div
                                                            className="h-full rounded-full bg-primary transition-all"
                                                            style={{ width: `${freeShippingProgress}%` }}
                                                        />
                                                    </div>
                                                    <p className="mt-1 text-xs text-muted-foreground">
                                                        Прогресс: {freeShippingProgress}%
                                                    </p>
                                                </div>
                                            )}
                                        </>
                                    ) : (
                                        <div className="flex items-center gap-2">
                                            <Badge variant="secondary">Бесплатная доставка активна</Badge>
                                        </div>
                                    )}
                                </div>
                            )}

                            <div className="space-y-3 mb-6">
                                <div className="flex justify-between text-muted-foreground">
                                    <span>Подытог</span>
                                    <span>{subtotal.toFixed(2)} сом.</span>
                                </div>
                                <Separator />
                                <div className="flex justify-between text-lg font-bold">
                                    <span>Всего</span>
                                    <span>{subtotal.toFixed(2)} сом.</span>
                                </div>
                            </div>

                            <Button asChild className="w-full" size="lg">
                                <Link href="/checkout">Перейти к оформлению</Link>
                            </Button>

                            <Button
                                asChild
                                variant="outline"
                                className="w-full mt-3"
                                size="lg"
                            >
                                <Link href="/products">Продолжить покупки</Link>
                            </Button>
                        </Card>
                    </div>
                </div>

                <div className="fixed inset-x-0 bottom-0 z-40 border-t bg-background/95 p-3 backdrop-blur lg:hidden">
                    <div className="container mx-auto flex items-center justify-between gap-3 px-0">
                        <div>
                            <p className="text-sm text-muted-foreground">Итого</p>
                            <p className="text-lg font-bold">{subtotal.toFixed(2)} сом.</p>
                        </div>
                        <Button asChild>
                            <Link href="/checkout">Оформить</Link>
                        </Button>
                    </div>
                </div>

                {recommendedProducts.length > 0 && (
                    <div className="mt-10">
                        <h2 className="mb-4 text-2xl font-bold">С этим покупают</h2>
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                            {recommendedProducts.map((product) => (
                                <ProductCard key={product.id} product={product} />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppHeaderLayout>
    );
}
