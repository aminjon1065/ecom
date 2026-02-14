import { Head, Link, router } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Minus, Plus, Trash2, ShoppingBag } from 'lucide-react';

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
}

export default function Cart({ cartItems }: CartProps) {
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

    return (
        <AppHeaderLayout>
            <Head title="Корзина" />
            <div className="container mx-auto px-4 py-8">
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
                        <Card className="p-6 sticky top-4">
                            <h2 className="text-xl font-bold mb-4">Итого</h2>
                            <Separator className="mb-4" />

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
            </div>
        </AppHeaderLayout>
    );
}
