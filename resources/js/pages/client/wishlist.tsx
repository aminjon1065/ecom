import { Head, Link, router } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { ProductCard } from '@/components/client/product-card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Heart, ShoppingCart, Sparkles } from 'lucide-react';
import { useMemo, useState } from 'react';

interface Product {
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
}

interface Wishlist {
  id: number;
  product_id: number;
  product: Product;
}

interface Props {
  wishlists: Wishlist[];
  wishlistSummary: {
    total: number;
    available: number;
    out_of_stock: number;
    potential_savings: number;
  };
}

export default function WishlistPage({ wishlists, wishlistSummary }: Props) {
  const [showAvailableOnly, setShowAvailableOnly] = useState(false);

  const visibleWishlists = useMemo(
    () =>
      wishlists.filter((wishlist) =>
        showAvailableOnly ? wishlist.product.status && wishlist.product.qty > 0 : true,
      ),
    [showAvailableOnly, wishlists],
  );

  const getCurrentPrice = (product: Product): number => {
    if (!product.offer_price || !product.offer_start_date || !product.offer_end_date) {
      return product.price;
    }

    const now = new Date();
    const offerStart = new Date(product.offer_start_date);
    const offerEnd = new Date(product.offer_end_date);

    if (now >= offerStart && now <= offerEnd) {
      return product.offer_price;
    }

    return product.price;
  };

  const handleRemove = (wishlistId: number) => {
    router.delete(`/wishlist/${wishlistId}`, {
      preserveScroll: true,
    });
  };

  const handleAddToCart = (productId: number) => {
    router.post(
      '/cart',
      { product_id: productId },
      {
        preserveScroll: true,
      }
    );
  };

  return (
    <AppHeaderLayout>
      <Head title="Избранное" />

      <div className="container mx-auto px-4 py-8">
        <div className="mb-8">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div>
              <h1 className="text-3xl font-bold flex items-center gap-2">
                <Heart className="h-8 w-8" />
                Избранное
              </h1>
              <p className="text-muted-foreground mt-2">
                {wishlists.length > 0
                  ? `У вас ${wishlists.length} ${wishlists.length === 1 ? 'товар' : 'товаров'} в избранном`
                  : 'Ваш список избранного пуст'}
              </p>
            </div>
            {wishlists.length > 0 && (
              <div className="flex flex-wrap gap-2">
                <Button
                  variant={showAvailableOnly ? 'default' : 'outline'}
                  onClick={() => setShowAvailableOnly((current) => !current)}
                >
                  {showAvailableOnly ? 'Показать все' : 'Только доступные'}
                </Button>
                <Button
                  variant="outline"
                  onClick={() => router.post('/wishlist/move-to-cart')}
                >
                  <ShoppingCart className="h-4 w-4 mr-2" />
                  Добавить всё в корзину
                </Button>
              </div>
            )}
          </div>
        </div>

        {wishlists.length > 0 ? (
          <div className="space-y-6">
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <Card>
                <CardHeader className="pb-2">
                  <CardTitle className="text-sm text-muted-foreground">Всего в избранном</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-2xl font-bold">{wishlistSummary.total}</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader className="pb-2">
                  <CardTitle className="text-sm text-muted-foreground">Доступно к заказу</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-2xl font-bold text-green-600">{wishlistSummary.available}</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader className="pb-2">
                  <CardTitle className="text-sm text-muted-foreground">Нет в наличии</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-2xl font-bold text-amber-600">{wishlistSummary.out_of_stock}</p>
                </CardContent>
              </Card>
              <Card>
                <CardHeader className="pb-2">
                  <CardTitle className="text-sm text-muted-foreground">Потенциальная выгода</CardTitle>
                </CardHeader>
                <CardContent>
                  <p className="text-2xl font-bold text-primary">
                    {wishlistSummary.potential_savings.toLocaleString()} сом.
                  </p>
                </CardContent>
              </Card>
            </div>

            {wishlistSummary.potential_savings > 0 && (
              <div className="rounded-lg border border-primary/20 bg-primary/5 p-3 text-sm">
                <div className="flex items-start gap-2">
                  <Sparkles className="mt-0.5 h-4 w-4 text-primary" />
                  <p>
                    Сейчас в избранном есть товары со скидкой. Можно сэкономить до{' '}
                    <span className="font-semibold">{wishlistSummary.potential_savings.toLocaleString()} сом.</span>
                  </p>
                </div>
              </div>
            )}

            {showAvailableOnly && (
              <p className="text-sm text-muted-foreground">
                Показаны только товары, которые можно добавить в корзину прямо сейчас.
              </p>
            )}

          {visibleWishlists.length > 0 ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
              {visibleWishlists.map((wishlist) => {
              const currentPrice = getCurrentPrice(wishlist.product);
              const hasDiscount = currentPrice < wishlist.product.price;
              const savings = Math.max(0, wishlist.product.price - currentPrice);

              return (
              <div key={wishlist.id} className="relative group">
                <ProductCard product={wishlist.product} />

                <div className="mt-2 flex flex-wrap items-center gap-2">
                  {wishlist.product.qty > 0 && wishlist.product.status ? (
                    <Badge variant="outline" className="border-green-200 text-green-700">
                      В наличии
                    </Badge>
                  ) : (
                    <Badge variant="outline" className="border-amber-200 text-amber-700">
                      Нет в наличии
                    </Badge>
                  )}
                  {hasDiscount && (
                    <Badge variant="secondary">
                      Выгода {savings.toLocaleString()} сом.
                    </Badge>
                  )}
                </div>

                <div className="mt-3 flex gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    className="flex-1"
                    onClick={() => handleAddToCart(wishlist.product.id)}
                    disabled={!wishlist.product.status || wishlist.product.qty === 0}
                  >
                    <ShoppingCart className="h-4 w-4 mr-2" />
                    В корзину
                  </Button>

                  <Button
                    variant="destructive"
                    size="sm"
                    onClick={() => handleRemove(wishlist.id)}
                  >
                    <Heart className="h-4 w-4 fill-current" />
                  </Button>
                </div>
              </div>
              );
              })}
            </div>
          ) : (
            <div className="rounded-lg border border-dashed p-8 text-center text-muted-foreground">
              Нет доступных товаров по выбранному фильтру.
            </div>
          )}
          </div>
        ) : (
          <div className="text-center py-16">
            <div className="flex justify-center mb-4">
              <Heart className="h-24 w-24 text-muted-foreground/50" />
            </div>
            <h2 className="text-2xl font-semibold mb-2">Ваш список избранного пуст</h2>
            <p className="text-muted-foreground mb-6">
              Добавьте товары в избранное, чтобы быстро найти их позже
            </p>
            <Link href="/products">
              <Button>
                Перейти к покупкам
              </Button>
            </Link>
          </div>
        )}
      </div>
    </AppHeaderLayout>
  );
}
