import { ProductCard } from '@/components/client/product-card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { Textarea } from '@/components/ui/textarea';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { lexicalDescriptionToHtml } from '@/lib/lexical-description';
import { Head, Link, router } from '@inertiajs/react';
import { ChevronRight, Heart, ShoppingCart, Star } from 'lucide-react';
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
    short_description: string;
    long_description: string;
    video_link?: string | null;
    sku?: string | null;
    qty: number;
    product_type?: string | null;
    category?: { id: number; name: string } | null;
    sub_category?: { id: number; name: string } | null;
    brand?: { id: number; name: string } | null;
    vendor?: {
        id: number;
        shop_name: string;
        user: { id: number; name: string };
    } | null;
    images: { id: number; image: string }[];
    reviews_avg_rating?: number | null;
    reviews_count?: number;
}

interface Review {
    id: number;
    rating: number;
    review: string;
    created_at: string;
    user: { id: number; name: string; avatar?: string | null };
}

interface Props {
    product: Product;
    reviews: Review[];
    relatedProducts: Product[];
    isInWishlist: boolean;
    isInCart: boolean;
}

export default function ProductShow({
    product,
    reviews,
    relatedProducts,
    isInWishlist,
    isInCart,
}: Props) {
    const imgPrefix = (path: string) =>
        path.startsWith('http') ? path : `/storage/${path}`;
    const [selectedImage, setSelectedImage] = useState(
        imgPrefix(product.thumb_image),
    );
    const [quantity, setQuantity] = useState(1);
    const [activeTab, setActiveTab] = useState<'description' | 'reviews'>(
        'description',
    );
    const [reviewRating, setReviewRating] = useState(0);
    const [reviewText, setReviewText] = useState('');
    const [hoveredStar, setHoveredStar] = useState(0);
    const productDescriptionHtml = useMemo(
        () => lexicalDescriptionToHtml(product.long_description),
        [product.long_description],
    );

    // All images: thumb_image + product.images
    const allImages = [
        imgPrefix(product.thumb_image),
        ...product.images.map((img) => imgPrefix(img.image)),
    ];

    // Check if offer is active
    const isOfferActive = () => {
        if (
            !product.offer_price ||
            !product.offer_start_date ||
            !product.offer_end_date
        ) {
            return false;
        }
        const now = new Date();
        const start = new Date(product.offer_start_date);
        const end = new Date(product.offer_end_date);
        return now >= start && now <= end;
    };

    const currentPrice =
        isOfferActive() && product.offer_price
            ? product.offer_price
            : product.price;
    const hasDiscount =
        isOfferActive() &&
        product.offer_price &&
        product.offer_price < product.price;

    const handleAddToCart = () => {
        router.post('/cart', {
            product_id: product.id,
            quantity: quantity,
        });
    };

    const handleToggleWishlist = () => {
        router.post('/wishlist', {
            product_id: product.id,
        });
    };

    const handleSubmitReview = (e: React.FormEvent) => {
        e.preventDefault();
        if (reviewRating === 0) {
            alert('Пожалуйста, выберите рейтинг');
            return;
        }
        router.post(
            `/products/${product.id}/review`,
            {
                rating: reviewRating,
                review: reviewText,
            },
            {
                onSuccess: () => {
                    setReviewRating(0);
                    setReviewText('');
                },
            },
        );
    };

    const renderStars = (
        rating: number,
        interactive: boolean = false,
        onRate?: (rating: number) => void,
    ) => {
        return (
            <div className="flex items-center gap-1">
                {[1, 2, 3, 4, 5].map((star) => (
                    <Star
                        key={star}
                        className={`h-5 w-5 ${
                            interactive
                                ? 'cursor-pointer transition-colors'
                                : ''
                        } ${
                            star <=
                            (interactive ? hoveredStar || reviewRating : rating)
                                ? 'fill-yellow-400 text-yellow-400'
                                : 'fill-none text-gray-300'
                        }`}
                        onClick={() => interactive && onRate && onRate(star)}
                        onMouseEnter={() => interactive && setHoveredStar(star)}
                        onMouseLeave={() => interactive && setHoveredStar(0)}
                    />
                ))}
            </div>
        );
    };

    return (
        <AppHeaderLayout>
            <Head title={product.name} />

            <div className="container mx-auto px-4 py-6">
                {/* Breadcrumb */}
                <nav className="mb-6 flex items-center gap-2 text-sm text-gray-600">
                    <Link href="/" className="hover:text-gray-900">
                        Главная
                    </Link>
                    <ChevronRight className="h-4 w-4" />
                    <Link href="/products" className="hover:text-gray-900">
                        Каталог
                    </Link>
                    {product.category && (
                        <>
                            <ChevronRight className="h-4 w-4" />
                            <Link
                                href={`/products?category=${product.category.id}`}
                                className="hover:text-gray-900"
                            >
                                {product.category.name}
                            </Link>
                        </>
                    )}
                    <ChevronRight className="h-4 w-4" />
                    <span className="font-medium text-gray-900">
                        {product.name}
                    </span>
                </nav>

                {/* Product Main Section */}
                <div className="mb-12 grid grid-cols-1 gap-8 lg:grid-cols-2">
                    {/* Image Gallery */}
                    <div className="space-y-4">
                        <div className="aspect-square overflow-hidden rounded-lg border bg-gray-100">
                            <img
                                src={selectedImage}
                                alt={product.name}
                                className="h-full w-full object-cover"
                            />
                        </div>
                        <div className="grid grid-cols-5 gap-2">
                            {allImages.map((image, index) => (
                                <button
                                    key={index}
                                    onClick={() => setSelectedImage(image)}
                                    className={`aspect-square overflow-hidden rounded-lg border-2 transition-all ${
                                        selectedImage === image
                                            ? 'border-primary'
                                            : 'border-gray-200 hover:border-gray-300'
                                    }`}
                                >
                                    <img
                                        src={image}
                                        alt={`${product.name} - ${index + 1}`}
                                        className="h-full w-full object-cover"
                                    />
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Product Info */}
                    <div className="space-y-6">
                        <div>
                            <h1 className="mb-2 text-3xl font-bold">
                                {product.name}
                            </h1>
                            {product.reviews_count &&
                                product.reviews_count > 0 && (
                                    <div className="mb-4 flex items-center gap-2">
                                        {renderStars(
                                            product.reviews_avg_rating || 0,
                                        )}
                                        <span className="text-sm text-gray-600">
                                            ({product.reviews_count}{' '}
                                            {product.reviews_count === 1
                                                ? 'отзыв'
                                                : 'отзыва'}
                                            )
                                        </span>
                                    </div>
                                )}
                            <p className="text-gray-600">
                                {product.short_description}
                            </p>
                        </div>

                        {/* Price */}
                        <div className="flex items-baseline gap-3">
                            <span className="text-4xl font-bold text-primary">
                                {currentPrice.toLocaleString()} сом.
                            </span>
                            {hasDiscount && (
                                <span className="text-2xl text-gray-400 line-through">
                                    {product.price.toLocaleString()} сом.
                                </span>
                            )}
                            {hasDiscount && (
                                <Badge
                                    variant="destructive"
                                    className="text-sm"
                                >
                                    -
                                    {Math.round(
                                        ((product.price - currentPrice) /
                                            product.price) *
                                            100,
                                    )}
                                    %
                                </Badge>
                            )}
                        </div>

                        <Separator />

                        {/* Product Details */}
                        <div className="space-y-2 text-sm">
                            {product.sku && (
                                <div className="flex gap-2">
                                    <span className="text-gray-600">
                                        Артикул:
                                    </span>
                                    <span className="font-medium">
                                        {product.sku}
                                    </span>
                                </div>
                            )}
                            {product.brand && (
                                <div className="flex gap-2">
                                    <span className="text-gray-600">
                                        Бренд:
                                    </span>
                                    <span className="font-medium">
                                        {product.brand.name}
                                    </span>
                                </div>
                            )}
                            <div className="flex gap-2">
                                <span className="text-gray-600">Наличие:</span>
                                <span
                                    className={`font-medium ${product.qty > 0 ? 'text-green-600' : 'text-red-600'}`}
                                >
                                    {product.qty > 0
                                        ? `В наличии (${product.qty} шт.)`
                                        : 'Нет в наличии'}
                                </span>
                            </div>
                            {product.vendor && (
                                <div className="flex gap-2">
                                    <span className="text-gray-600">
                                        Продавец:
                                    </span>
                                    <span className="font-medium">
                                        {product.vendor.shop_name}
                                    </span>
                                </div>
                            )}
                        </div>

                        <Separator />

                        {/* Quantity & Actions */}
                        <div className="space-y-4">
                            <div className="flex items-center gap-3">
                                <span className="text-sm font-medium">
                                    Количество:
                                </span>
                                <div className="flex items-center rounded-lg border">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() =>
                                            setQuantity(
                                                Math.max(1, quantity - 1),
                                            )
                                        }
                                        disabled={quantity <= 1}
                                    >
                                        -
                                    </Button>
                                    <Input
                                        type="number"
                                        value={quantity}
                                        onChange={(e) =>
                                            setQuantity(
                                                Math.max(
                                                    1,
                                                    Math.min(
                                                        product.qty,
                                                        parseInt(
                                                            e.target.value,
                                                        ) || 1,
                                                    ),
                                                ),
                                            )
                                        }
                                        className="w-16 border-0 text-center focus-visible:ring-0"
                                        min={1}
                                        max={product.qty}
                                    />
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={() =>
                                            setQuantity(
                                                Math.min(
                                                    product.qty,
                                                    quantity + 1,
                                                ),
                                            )
                                        }
                                        disabled={quantity >= product.qty}
                                    >
                                        +
                                    </Button>
                                </div>
                            </div>

                            <div className="flex gap-3">
                                <Button
                                    onClick={handleAddToCart}
                                    disabled={product.qty === 0 || isInCart}
                                    className="flex-1"
                                    size="lg"
                                >
                                    <ShoppingCart className="mr-2 h-5 w-5" />
                                    {isInCart
                                        ? 'В корзине'
                                        : 'Добавить в корзину'}
                                </Button>
                                <Button
                                    variant={
                                        isInWishlist ? 'default' : 'outline'
                                    }
                                    size="lg"
                                    onClick={handleToggleWishlist}
                                >
                                    <Heart
                                        className={`h-5 w-5 ${isInWishlist ? 'fill-current' : ''}`}
                                    />
                                </Button>
                            </div>
                        </div>

                        {/* Video Link */}
                        {product.video_link && (
                            <div>
                                <a
                                    href={product.video_link}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="text-sm font-medium text-primary hover:underline"
                                >
                                    Посмотреть видео о товаре
                                </a>
                            </div>
                        )}
                    </div>
                </div>

                {/* Tabs Section */}
                <Card className="mb-12">
                    <div className="border-b">
                        <div className="flex gap-8 px-6">
                            <button
                                onClick={() => setActiveTab('description')}
                                className={`border-b-2 py-4 font-medium transition-colors ${
                                    activeTab === 'description'
                                        ? 'border-primary text-primary'
                                        : 'border-transparent text-gray-600 hover:text-gray-900'
                                }`}
                            >
                                Описание
                            </button>
                            <button
                                onClick={() => setActiveTab('reviews')}
                                className={`border-b-2 py-4 font-medium transition-colors ${
                                    activeTab === 'reviews'
                                        ? 'border-primary text-primary'
                                        : 'border-transparent text-gray-600 hover:text-gray-900'
                                }`}
                            >
                                Отзывы ({reviews.length})
                            </button>
                        </div>
                    </div>

                    <div className="p-6">
                        {activeTab === 'description' &&
                            (productDescriptionHtml ? (
                                <div
                                    className="prose max-w-none"
                                    dangerouslySetInnerHTML={{
                                        __html: productDescriptionHtml,
                                    }}
                                />
                            ) : (
                                <p className="text-gray-600">
                                    Описание отсутствует.
                                </p>
                            ))}

                        {activeTab === 'reviews' && (
                            <div className="space-y-8">
                                {/* Review Form */}
                                <div>
                                    <h3 className="mb-4 text-xl font-semibold">
                                        Оставить отзыв
                                    </h3>
                                    <form
                                        onSubmit={handleSubmitReview}
                                        className="space-y-4"
                                    >
                                        <div>
                                            <label className="mb-2 block text-sm font-medium">
                                                Ваша оценка
                                            </label>
                                            {renderStars(
                                                reviewRating,
                                                true,
                                                setReviewRating,
                                            )}
                                        </div>
                                        <div>
                                            <label
                                                htmlFor="review"
                                                className="mb-2 block text-sm font-medium"
                                            >
                                                Ваш отзыв
                                            </label>
                                            <Textarea
                                                id="review"
                                                value={reviewText}
                                                onChange={(e) =>
                                                    setReviewText(
                                                        e.target.value,
                                                    )
                                                }
                                                placeholder="Поделитесь своим мнением о товаре..."
                                                rows={4}
                                                required
                                            />
                                        </div>
                                        <Button type="submit">
                                            Отправить отзыв
                                        </Button>
                                    </form>
                                </div>

                                <Separator />

                                {/* Reviews List */}
                                <div className="space-y-6">
                                    <h3 className="text-xl font-semibold">
                                        Отзывы покупателей ({reviews.length})
                                    </h3>
                                    {reviews.length === 0 ? (
                                        <p className="text-gray-600">
                                            Пока нет отзывов. Будьте первым!
                                        </p>
                                    ) : (
                                        <div className="space-y-6">
                                            {reviews.map((review) => (
                                                <div
                                                    key={review.id}
                                                    className="border-b pb-6 last:border-0"
                                                >
                                                    <div className="flex items-start gap-4">
                                                        <div className="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-gray-200">
                                                            {review.user
                                                                .avatar ? (
                                                                <img
                                                                    src={
                                                                        review
                                                                            .user
                                                                            .avatar
                                                                    }
                                                                    alt={
                                                                        review
                                                                            .user
                                                                            .name
                                                                    }
                                                                    className="h-full w-full rounded-full object-cover"
                                                                />
                                                            ) : (
                                                                <span className="text-lg font-semibold text-gray-600">
                                                                    {review.user.name
                                                                        .charAt(
                                                                            0,
                                                                        )
                                                                        .toUpperCase()}
                                                                </span>
                                                            )}
                                                        </div>
                                                        <div className="flex-1">
                                                            <div className="mb-2 flex items-center justify-between">
                                                                <h4 className="font-semibold">
                                                                    {
                                                                        review
                                                                            .user
                                                                            .name
                                                                    }
                                                                </h4>
                                                                <span className="text-sm text-gray-600">
                                                                    {new Date(
                                                                        review.created_at,
                                                                    ).toLocaleDateString(
                                                                        'ru-RU',
                                                                    )}
                                                                </span>
                                                            </div>
                                                            {renderStars(
                                                                review.rating,
                                                            )}
                                                            <p className="mt-2 text-gray-700">
                                                                {review.review}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}
                    </div>
                </Card>

                {/* Related Products */}
                {relatedProducts.length > 0 && (
                    <div>
                        <h2 className="mb-6 text-2xl font-bold">
                            Похожие товары
                        </h2>
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                            {relatedProducts.map((relatedProduct) => (
                                <ProductCard
                                    key={relatedProduct.id}
                                    product={relatedProduct}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppHeaderLayout>
    );
}
