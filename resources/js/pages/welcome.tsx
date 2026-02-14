import { ProductCard } from '@/components/client/product-card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowRight, ChevronLeft, ChevronRight, Flame, Sparkles, Star, TrendingUp } from 'lucide-react';
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

function HeroSlider({ sliders }: { sliders: Slider[] }) {
    const [current, setCurrent] = useState(0);

    const next = useCallback(() => {
        setCurrent((c) => (c + 1) % sliders.length);
    }, [sliders.length]);

    const prev = () => setCurrent((c) => (c - 1 + sliders.length) % sliders.length);

    useEffect(() => {
        if (sliders.length <= 1) return;
        const timer = setInterval(next, 5000);
        return () => clearInterval(timer);
    }, [next, sliders.length]);

    if (!sliders.length) return null;

    return (
        <div className="relative overflow-hidden rounded-xl">
            <div className="relative aspect-21/8 w-full">
                {sliders.map((slider, i) => (
                    <div
                        key={slider.id}
                        className={`absolute inset-0 transition-opacity duration-500 ${i === current ? 'opacity-100' : 'opacity-0 pointer-events-none'}`}
                    >
                        <img src={`/storage/${slider.banner}`} alt={slider.title} className="h-full w-full object-cover" />
                        <div className="absolute inset-0 bg-linear-to-r from-black/60 to-transparent" />
                        <div className="absolute inset-0 flex flex-col justify-center p-8 md:p-12 lg:p-16">
                            <Badge variant="secondary" className="mb-2 w-fit">{slider.type}</Badge>
                            <h2 className="mb-2 max-w-lg text-2xl font-bold text-white md:text-4xl">{slider.title}</h2>
                            <p className="mb-4 text-lg text-white/80">от {slider.starting_price} сом.</p>
                            <Link href={slider.btn_url}>
                                <Button size="lg">
                                    Смотреть <ArrowRight className="ml-2 h-4 w-4" />
                                </Button>
                            </Link>
                        </div>
                    </div>
                ))}
            </div>
            {sliders.length > 1 && (
                <>
                    <button onClick={prev} className="absolute top-1/2 left-3 -translate-y-1/2 rounded-full bg-white/80 p-2 backdrop-blur hover:bg-white">
                        <ChevronLeft className="h-5 w-5" />
                    </button>
                    <button onClick={next} className="absolute top-1/2 right-3 -translate-y-1/2 rounded-full bg-white/80 p-2 backdrop-blur hover:bg-white">
                        <ChevronRight className="h-5 w-5" />
                    </button>
                    <div className="absolute bottom-3 left-1/2 flex -translate-x-1/2 gap-1.5">
                        {sliders.map((_, i) => (
                            <button
                                key={i}
                                onClick={() => setCurrent(i)}
                                className={`h-2 rounded-full transition-all ${i === current ? 'w-6 bg-white' : 'w-2 bg-white/50'}`}
                            />
                        ))}
                    </div>
                </>
            )}
        </div>
    );
}

function ProductSection({ title, icon, products, viewAllHref }: { title: string; icon: React.ReactNode; products: Product[]; viewAllHref?: string }) {
    if (!products.length) return null;
    return (
        <section>
            <div className="mb-4 flex items-center justify-between">
                <h2 className="flex items-center gap-2 text-xl font-bold">
                    {icon} {title}
                </h2>
                {viewAllHref && (
                    <Link href={viewAllHref} className="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
                        Смотреть все <ArrowRight className="h-3.5 w-3.5" />
                    </Link>
                )}
            </div>
            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                {products.map((product) => (
                    <ProductCard key={product.id} product={product} />
                ))}
            </div>
        </section>
    );
}

function NewsletterSection() {
    const [email, setEmail] = useState('');
    const [submitted, setSubmitted] = useState(false);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        router.post('/newsletter', { email }, {
            preserveScroll: true,
            onSuccess: () => { setEmail(''); setSubmitted(true); },
        });
    };

    return (
        <section className="rounded-xl bg-muted/50 p-8 text-center">
            <h2 className="mb-2 text-xl font-bold">Подпишитесь на рассылку</h2>
            <p className="mb-4 text-sm text-muted-foreground">Получайте уведомления о скидках и новинках</p>
            {submitted ? (
                <p className="text-sm text-green-600">Вы успешно подписались!</p>
            ) : (
                <form onSubmit={handleSubmit} className="mx-auto flex max-w-md gap-2">
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

export default function Welcome({ sliders, flashSaleProducts, newProducts, topProducts, bestProducts, categories }: Props) {
    return (
        <AppHeaderLayout>
            <Head title="Главная" />
            <div className="mx-auto max-w-7xl space-y-10 px-4 py-6 sm:px-6 lg:px-8">
                <HeroSlider sliders={sliders} />

                {categories.length > 0 && (
                    <section>
                        <h2 className="mb-4 text-xl font-bold">Популярные категории</h2>
                        <div className="grid grid-cols-2 gap-3 sm:grid-cols-4 md:grid-cols-8">
                            {categories.map((cat) => (
                                <Link key={cat.id} href={`/products?category=${cat.id}`}>
                                    <Card className="text-center transition-shadow hover:shadow-md">
                                        <CardContent className="p-3">
                                            <div className="mb-1 text-2xl">{cat.icon || '📦'}</div>
                                            <p className="line-clamp-1 text-xs font-medium">{cat.name}</p>
                                            <p className="text-[10px] text-muted-foreground">{cat.products_count} тов.</p>
                                        </CardContent>
                                    </Card>
                                </Link>
                            ))}
                        </div>
                    </section>
                )}

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
