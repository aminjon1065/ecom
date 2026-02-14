import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Link, router } from '@inertiajs/react';
import { Heart, ShoppingCart, Star } from 'lucide-react';

interface ProductCardProps {
    product: {
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
        category?: { id: number; name: string } | null;
    };
    showActions?: boolean;
}

function getEffectivePrice(product: ProductCardProps['product']) {
    if (
        product.offer_price &&
        product.offer_start_date &&
        product.offer_end_date &&
        new Date() >= new Date(product.offer_start_date) &&
        new Date() <= new Date(product.offer_end_date)
    ) {
        return { price: product.offer_price, original: product.price, hasDiscount: true };
    }
    return { price: product.price, original: product.price, hasDiscount: false };
}

export function ProductCard({ product, showActions = true }: ProductCardProps) {
    const { price, original, hasDiscount } = getEffectivePrice(product);
    const rating = product.reviews_avg_rating ? Number(product.reviews_avg_rating).toFixed(1) : null;

    const addToCart = (e: React.MouseEvent) => {
        e.preventDefault();
        e.stopPropagation();
        router.post('/cart', { product_id: product.id }, { preserveScroll: true });
    };

    const toggleWishlist = (e: React.MouseEvent) => {
        e.preventDefault();
        e.stopPropagation();
        router.post('/wishlist', { product_id: product.id }, { preserveScroll: true });
    };

    return (
        <Card className="group overflow-hidden transition-shadow hover:shadow-lg">
            <Link href={`/products/${product.slug}`}>
                <div className="relative aspect-square overflow-hidden bg-muted">
                    <img
                        src={`/storage/${product.thumb_image}`}
                        alt={product.name}
                        className="h-full w-full object-cover transition-transform group-hover:scale-105"
                        loading="lazy"
                    />
                    {/* Badges */}
                    {product.product_type && (
                        <Badge className="absolute top-1.5 left-1.5 text-[10px] sm:top-2 sm:left-2 sm:text-xs" variant={product.product_type === 'Новый' ? 'default' : 'secondary'}>
                            {product.product_type}
                        </Badge>
                    )}
                    {hasDiscount && (
                        <Badge className="absolute top-1.5 right-1.5 text-[10px] sm:top-2 sm:right-2 sm:text-xs" variant="destructive">
                            -{Math.round(((original - price) / original) * 100)}%
                        </Badge>
                    )}
                    {/* Action buttons: always visible on mobile, hover on desktop */}
                    {showActions && (
                        <div className="absolute right-1.5 bottom-1.5 flex gap-1 sm:right-2 sm:bottom-2 sm:opacity-0 sm:transition-opacity sm:group-hover:opacity-100">
                            <Button size="icon" variant="secondary" className="h-7 w-7 shadow-sm sm:h-8 sm:w-8" onClick={toggleWishlist}>
                                <Heart className="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                            </Button>
                            <Button size="icon" variant="secondary" className="h-7 w-7 shadow-sm sm:h-8 sm:w-8" onClick={addToCart}>
                                <ShoppingCart className="h-3 w-3 sm:h-3.5 sm:w-3.5" />
                            </Button>
                        </div>
                    )}
                </div>
                <CardContent className="p-2.5 sm:p-3">
                    {product.category && (
                        <p className="mb-0.5 truncate text-[10px] text-muted-foreground sm:mb-1 sm:text-xs">{product.category.name}</p>
                    )}
                    <h3 className="line-clamp-2 text-xs font-medium leading-tight sm:text-sm">{product.name}</h3>
                    <div className="mt-1.5 flex flex-wrap items-baseline gap-x-2 gap-y-0 sm:mt-2">
                        <span className="text-sm font-semibold sm:text-base">{price.toLocaleString()} сом.</span>
                        {hasDiscount && (
                            <span className="text-[10px] text-muted-foreground line-through sm:text-sm">{original.toLocaleString()}</span>
                        )}
                    </div>
                    {rating && (
                        <div className="mt-1 flex items-center gap-1 text-[10px] text-muted-foreground sm:mt-1.5 sm:text-xs">
                            <Star className="h-2.5 w-2.5 fill-yellow-400 text-yellow-400 sm:h-3 sm:w-3" />
                            <span>{rating}</span>
                            <span>({product.reviews_count})</span>
                        </div>
                    )}
                </CardContent>
            </Link>
        </Card>
    );
}
