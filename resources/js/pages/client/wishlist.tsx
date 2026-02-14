import { Head, Link, router } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { ProductCard } from '@/components/client/product-card';
import { Button } from '@/components/ui/button';
import { Heart, ShoppingCart } from 'lucide-react';

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
}

export default function WishlistPage({ wishlists }: Props) {
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

        {wishlists.length > 0 ? (
          <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            {wishlists.map((wishlist) => (
              <div key={wishlist.id} className="relative group">
                <ProductCard product={wishlist.product} />

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
            ))}
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
