import { ProductCard } from '@/components/client/product-card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowRight,
    ChevronLeft,
    ChevronRight,
    Flame,
    LayoutGrid,
    Sparkles,
    Star,
    TrendingUp,
} from 'lucide-react';
import { useCallback, useEffect, useState } from 'react';

interface Slider {
    id: number;
    banner: string;
    type: string;
    title: string;
    starting_price: string;
    btn_url: string;
    status: boolean;
}

interface Product {
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
}

interface Category {
    id: number;
    name: string;
    icon?: string | null;
    products_count: number;
}

interface Props {
    sliders: Slider[];
    flashSaleProducts: Product[];
    newProducts: Product[];
    topProducts: Product[];
    bestProducts: Product[];
    categories: Category[];
}

/* ─────────── Hero Slider ─────────── */
function HeroSlider({ sliders }: { sliders: Slider[] }) {
    const [current, setCurrent] = useState(0);

    const next = useCallback(() => {
        setCurrent((c) => (c + 1) % sliders.length);
    }, [sliders.length]);

    const prev = () =>
        setCurrent((c) => (c - 1 + sliders.length) % sliders.length);

    useEffect(() => {
        if (sliders.length <= 1) return;
        const timer = setInterval(next, 5000);
        return () => clearInterval(timer);
    }, [next, sliders.length]);

    if (!sliders.length) return null;

    return (
        <div className="relative overflow-hidden rounded-xl">
            {/* Mobile: taller aspect ratio for better visibility */}
            <div className="relative aspect-video w-full sm:aspect-21/8">
                {sliders.map((slider, i) => (
                    <div
                        key={slider.id}
                        className={`absolute inset-0 transition-opacity duration-500 ${i === current ? 'opacity-100' : 'pointer-events-none opacity-0'}`}
                    >
                        <img
                            src={`/storage/${slider.banner}`}
                            alt={slider.title}
                            className="h-full w-full object-cover"
                        />
                        <div className="absolute inset-0 bg-linear-to-r from-black/60 to-transparent" />
                        <div className="absolute inset-0 flex flex-col justify-center p-5 sm:p-8 md:p-12 lg:p-16">
                            <Badge variant="secondary" className="mb-2 w-fit text-xs">
                                {slider.type}
                            </Badge>
                            <h2 className="mb-1 max-w-lg text-lg font-bold text-white sm:mb-2 sm:text-2xl md:text-4xl">
                                {slider.title}
                            </h2>
                            <p className="mb-3 text-sm text-white/80 sm:mb-4 sm:text-lg">
                                от {slider.starting_price} сом.
                            </p>
                            <Link href={slider.btn_url}>
                                <Button size="sm" className="sm:size-default">
                                    Смотреть{' '}
                                    <ArrowRight className="ml-1.5 h-3.5 w-3.5 sm:h-4 sm:w-4" />
                                </Button>
                            </Link>
                        </div>
                    </div>
                ))}
            </div>
            {sliders.length > 1 && (
                <>
                    <button
                        onClick={prev}
                        className="absolute top-1/2 left-2 -translate-y-1/2 rounded-full bg-white/80 p-1.5 backdrop-blur hover:bg-white sm:left-3 sm:p-2"
                    >
                        <ChevronLeft className="h-4 w-4 sm:h-5 sm:w-5" />
                    </button>
                    <button
                        onClick={next}
                        className="absolute top-1/2 right-2 -translate-y-1/2 rounded-full bg-white/80 p-1.5 backdrop-blur hover:bg-white sm:right-3 sm:p-2"
                    >
                        <ChevronRight className="h-4 w-4 sm:h-5 sm:w-5" />
                    </button>
                    <div className="absolute bottom-2 left-1/2 flex -translate-x-1/2 gap-1.5 sm:bottom-3">
                        {sliders.map((_, i) => (
                            <button
                                key={i}
                                onClick={() => setCurrent(i)}
                                className={`h-1.5 rounded-full transition-all sm:h-2 ${i === current ? 'w-5 bg-white sm:w-6' : 'w-1.5 bg-white/50 sm:w-2'}`}
                            />
                        ))}
                    </div>
                </>
            )}
        </div>
    );
}

/* ─────────── Category Strip (horizontal scroll on mobile) ─────────── */
function CategoryStrip({ categories }: { categories: Category[] }) {
    if (!categories.length) return null;

    return (
        <section>
            <div className="mb-3 flex items-center justify-between">
                <h2 className="flex items-center gap-2 text-lg font-bold sm:text-xl">
                    <LayoutGrid className="h-5 w-5 text-primary" />
                    Категории
                </h2>
                <Link
                    href="/products"
                    className="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                >
                    Все <ArrowRight className="h-3.5 w-3.5" />
                </Link>
            </div>

            {/* Mobile: Horizontal scroll. Desktop: Grid */}
            <div className="scrollbar-hide -mx-4 flex gap-3 overflow-x-auto px-4 pb-2 sm:mx-0 sm:grid sm:grid-cols-4 sm:gap-3 sm:overflow-visible sm:px-0 md:grid-cols-6 lg:grid-cols-8">
                {categories.map((cat) => (
                    <Link
                        key={cat.id}
                        href={`/products?category=${cat.id}`}
                        className="flex min-w-18 flex-col items-center gap-1.5 sm:min-w-0"
                    >
                        <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-muted transition-colors hover:bg-accent sm:h-16 sm:w-16">
                            {cat.icon ? (
                                <img src={`${cat.icon}`} alt={cat.name} className="h-7 w-7 sm:h-8 sm:w-8" />
                            ) : (
                                <LayoutGrid className="h-6 w-6 text-muted-foreground" />
                            )}
                        </div>
                        <span className="w-full text-center text-[11px] leading-tight text-foreground sm:text-xs">
                            {cat.name}
                        </span>
                    </Link>
                ))}
            </div>
        </section>
    );
}

/* ─────────── Product Section ─────────── */
function ProductSection({
    title,
    icon,
    products,
    viewAllHref,
}: {
    title: string;
    icon: React.ReactNode;
    products: Product[];
    viewAllHref?: string;
}) {
    if (!products.length) return null;

    return (
        <section>
            <div className="mb-3 flex items-center justify-between sm:mb-4">
                <h2 className="flex items-center gap-2 text-lg font-bold sm:text-xl">
                    {icon} {title}
                </h2>
                {viewAllHref && (
                    <Link
                        href={viewAllHref}
                        className="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        Все <ArrowRight className="h-3.5 w-3.5" />
                    </Link>
                )}
            </div>

            {/* Mobile: horizontal scroll. Desktop: grid */}
            <div className="scrollbar-hide -mx-4 flex gap-3 overflow-x-auto px-4 pb-2 sm:mx-0 sm:grid sm:grid-cols-3 sm:gap-4 sm:overflow-visible sm:px-0 md:grid-cols-4">
                {products.map((product) => (
                    <div key={product.id} className="w-[44vw] shrink-0 sm:w-auto">
                        <ProductCard product={product} />
                    </div>
                ))}
            </div>
        </section>
    );
}

/* ─────────── Newsletter ─────────── */
function NewsletterSection() {
    const [email, setEmail] = useState('');
    const [submitted, setSubmitted] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.post(
            '/newsletter',
            { email },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setEmail('');
                    setSubmitted(true);
                },
            },
        );
    };

    return (
        <section className="rounded-xl bg-muted/50 p-6 text-center sm:p-8">
            <h2 className="mb-2 text-lg font-bold sm:text-xl">Подпишитесь на рассылку</h2>
            <p className="mb-4 text-sm text-muted-foreground">
                Получайте уведомления о скидках и новинках
            </p>
            {submitted ? (
                <p className="text-sm text-green-600">
                    Вы успешно подписались!
                </p>
            ) : (
                <form
                    onSubmit={handleSubmit}
                    className="mx-auto flex max-w-md gap-2"
                >
                    <Input
                        type="email"
                        placeholder="Ваш email..."
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        required
                    />
                    <Button type="submit">Подписаться</Button>
                </form>
            )}
        </section>
    );
}

/* ─────────── Main Page ─────────── */
export default function Welcome({
    sliders,
    flashSaleProducts,
    newProducts,
    topProducts,
    bestProducts,
    categories,
}: Props) {
    return (
        <AppHeaderLayout>
            <Head title="Главная" />
            <div className="mx-auto max-w-7xl space-y-6 px-4 py-4 sm:space-y-10 sm:px-6 sm:py-6 lg:px-8">
                <HeroSlider sliders={sliders} />

                <CategoryStrip categories={categories} />

                <ProductSection
                    title="Акции"
                    icon={<Flame className="h-5 w-5 text-orange-500" />}
                    products={flashSaleProducts}
                />

                <ProductSection
                    title="Новинки"
                    icon={<Sparkles className="h-5 w-5 text-blue-500" />}
                    products={newProducts}
                    viewAllHref="/products?sort=latest"
                />

                <ProductSection
                    title="Топ товары"
                    icon={<TrendingUp className="h-5 w-5 text-green-500" />}
                    products={topProducts}
                />

                <ProductSection
                    title="Лучшие товары"
                    icon={<Star className="h-5 w-5 text-yellow-500" />}
                    products={bestProducts}
                    viewAllHref="/products?sort=popular"
                />

                <NewsletterSection />
            </div>
        </AppHeaderLayout>
    );
}
