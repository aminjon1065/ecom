import { router } from '@inertiajs/react';
import { Flame, Loader2, Search, Tag } from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Input } from '@/components/ui/input';

interface ProductSuggestion {
    id: number;
    name: string;
    slug: string;
    thumb_image: string;
    price: number;
    offer_price: number | null;
    offer_start_date: string | null;
    offer_end_date: string | null;
    qty: number;
    category: {
        id: number;
        name: string;
    } | null;
    brand: {
        id: number;
        name: string;
    } | null;
}

interface Props {
    placeholder?: string;
    className?: string;
    inputClassName?: string;
}

type KeyboardOption =
    | { type: 'popular'; query: string }
    | { type: 'product'; product: ProductSuggestion }
    | { type: 'category'; id: number; name: string }
    | { type: 'brand'; id: number; name: string }
    | { type: 'search'; query: string };

const FALLBACK_POPULAR_QUERIES = ['Смартфон', 'Наушники', 'Ноутбук', 'Кроссовки', 'Кофемашина'];

function isOfferActive(product: ProductSuggestion): boolean {
    if (!product.offer_price || !product.offer_start_date || !product.offer_end_date) {
        return false;
    }

    const now = new Date();

    return now >= new Date(product.offer_start_date) && now <= new Date(product.offer_end_date);
}

function imagePath(path: string): string {
    if (path.startsWith('http')) {
        return path;
    }

    return `/storage/${path}`;
}

export function SearchSuggestions({
    placeholder = 'Поиск товаров...',
    className = '',
    inputClassName = '',
}: Props) {
    const [query, setQuery] = useState('');
    const [popularQueries, setPopularQueries] = useState<string[]>(FALLBACK_POPULAR_QUERIES);
    const [hasLoadedPopularQueries, setHasLoadedPopularQueries] = useState(false);
    const [suggestions, setSuggestions] = useState<ProductSuggestion[]>([]);
    const [loading, setLoading] = useState(false);
    const [open, setOpen] = useState(false);
    const [activeIndex, setActiveIndex] = useState(-1);
    const containerRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLInputElement>(null);
    const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const abortRef = useRef<AbortController | null>(null);
    const normalizedQuery = query.trim();
    const showPopular = normalizedQuery.length < 2;
    const showQueryResults = normalizedQuery.length >= 2;

    const categories = useMemo(() => {
        const unique = new Map<number, string>();
        suggestions.forEach((product) => {
            if (product.category) {
                unique.set(product.category.id, product.category.name);
            }
        });

        return Array.from(unique.entries())
            .map(([id, name]) => ({ id, name }))
            .slice(0, 3);
    }, [suggestions]);

    const brands = useMemo(() => {
        const unique = new Map<number, string>();
        suggestions.forEach((product) => {
            if (product.brand) {
                unique.set(product.brand.id, product.brand.name);
            }
        });

        return Array.from(unique.entries())
            .map(([id, name]) => ({ id, name }))
            .slice(0, 3);
    }, [suggestions]);

    const keyboardOptions = useMemo<KeyboardOption[]>(() => {
        if (!open) {
            return [];
        }

        if (showPopular) {
            return popularQueries.map((popularQuery) => ({
                type: 'popular',
                query: popularQuery,
            }));
        }

        if (!showQueryResults) {
            return [];
        }

        return [
            ...suggestions.map((product) => ({ type: 'product' as const, product })),
            ...categories.map((category) => ({ type: 'category' as const, id: category.id, name: category.name })),
            ...brands.map((brand) => ({ type: 'brand' as const, id: brand.id, name: brand.name })),
            { type: 'search', query: normalizedQuery },
        ];
    }, [brands, categories, normalizedQuery, open, popularQueries, showPopular, showQueryResults, suggestions]);

    const fetchSuggestions = useCallback(async (value: string): Promise<void> => {
        if (abortRef.current) {
            abortRef.current.abort();
        }

        if (value.length < 2) {
            setSuggestions([]);
            setLoading(false);
            return;
        }

        const controller = new AbortController();
        abortRef.current = controller;
        setLoading(true);

        try {
            const response = await fetch(`/api/search?q=${encodeURIComponent(value)}`, {
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error('Search request failed');
            }

            const data: ProductSuggestion[] = await response.json();
            setSuggestions(data);
        } catch (error: unknown) {
            if (error instanceof DOMException && error.name === 'AbortError') {
                return;
            }

            setSuggestions([]);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        if (timerRef.current) {
            clearTimeout(timerRef.current);
        }

        if (normalizedQuery.length < 2) {
            setSuggestions([]);
            setLoading(false);
            setActiveIndex(-1);
            return;
        }

        setLoading(true);
        timerRef.current = setTimeout(() => {
            void fetchSuggestions(normalizedQuery);
        }, 300);

        return () => {
            if (timerRef.current) {
                clearTimeout(timerRef.current);
            }
        };
    }, [fetchSuggestions, normalizedQuery]);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent): void => {
            if (containerRef.current && !containerRef.current.contains(event.target as Node)) {
                setOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);

        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    useEffect(() => {
        if (!open) {
            setActiveIndex(-1);
        }
    }, [open]);

    useEffect(() => {
        if (!showPopular || hasLoadedPopularQueries) {
            return;
        }

        const controller = new AbortController();

        fetch('/api/search/popular', {
            signal: controller.signal,
        })
            .then(async (response) => {
                if (!response.ok) {
                    throw new Error('Popular search request failed');
                }

                const data = (await response.json()) as string[];
                const clean = data
                    .map((item) => item.trim())
                    .filter((item) => item !== '')
                    .slice(0, 8);

                setPopularQueries(clean.length > 0 ? clean : FALLBACK_POPULAR_QUERIES);
                setHasLoadedPopularQueries(true);
            })
            .catch((error: unknown) => {
                if (error instanceof DOMException && error.name === 'AbortError') {
                    return;
                }

                setPopularQueries(FALLBACK_POPULAR_QUERIES);
                setHasLoadedPopularQueries(true);
            });

        return () => controller.abort();
    }, [hasLoadedPopularQueries, showPopular]);

    const navigateToSearch = (searchQuery: string, extra: Record<string, string | number> = {}): void => {
        const cleanQuery = searchQuery.trim();
        if (!cleanQuery) {
            return;
        }

        setOpen(false);
        router.get('/products', {
            search: cleanQuery,
            ...extra,
        });
    };

    const navigateToProduct = (slug: string): void => {
        setOpen(false);
        router.get(`/products/${slug}`);
    };

    const executeKeyboardOption = (option: KeyboardOption): void => {
        if (option.type === 'popular') {
            setQuery(option.query);
            navigateToSearch(option.query);
            return;
        }

        if (option.type === 'product') {
            navigateToProduct(option.product.slug);
            return;
        }

        if (option.type === 'category') {
            navigateToSearch(normalizedQuery, { category: option.id });
            return;
        }

        if (option.type === 'brand') {
            navigateToSearch(normalizedQuery, { brand: option.id });
            return;
        }

        navigateToSearch(option.query);
    };

    const handleKeyDown = (event: React.KeyboardEvent): void => {
        if (!open && event.key === 'ArrowDown') {
            setOpen(true);
            return;
        }

        if (event.key === 'Escape') {
            setOpen(false);
            inputRef.current?.blur();
            return;
        }

        if (keyboardOptions.length === 0) {
            if (event.key === 'Enter') {
                event.preventDefault();
                navigateToSearch(normalizedQuery);
            }
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActiveIndex((current) => (current < keyboardOptions.length - 1 ? current + 1 : 0));
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActiveIndex((current) => (current > 0 ? current - 1 : keyboardOptions.length - 1));
            return;
        }

        if (event.key === 'Enter') {
            event.preventDefault();
            const option = activeIndex >= 0 ? keyboardOptions[activeIndex] : keyboardOptions.at(-1);
            if (option) {
                executeKeyboardOption(option);
            }
        }
    };

    const shouldShowPanel = open && (showPopular || showQueryResults);

    return (
        <div ref={containerRef} className={`relative ${className}`}>
            <div className="relative">
                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    ref={inputRef}
                    type="text"
                    placeholder={placeholder}
                    value={query}
                    onChange={(event) => setQuery(event.target.value)}
                    onKeyDown={handleKeyDown}
                    onFocus={() => setOpen(true)}
                    className={`pl-10 pr-9 ${inputClassName}`}
                    autoComplete="off"
                />
                {loading && (
                    <Loader2 className="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-muted-foreground" />
                )}
            </div>

            {shouldShowPanel && (
                <div className="absolute left-0 right-0 top-full z-[60] mt-1 rounded-lg border bg-popover shadow-lg">
                    {showPopular && (
                        <div className="p-3">
                            <p className="mb-2 flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                <Flame className="h-3.5 w-3.5" />
                                Популярные запросы
                            </p>
                            <div className="flex flex-wrap gap-2">
                                {popularQueries.map((popularQuery, index) => (
                                    <button
                                        key={popularQuery}
                                        type="button"
                                        className={`rounded-full border px-3 py-1 text-xs transition-colors ${
                                            activeIndex === index ? 'bg-accent' : 'hover:bg-accent'
                                        }`}
                                        onMouseEnter={() => setActiveIndex(index)}
                                        onClick={() => {
                                            setQuery(popularQuery);
                                            navigateToSearch(popularQuery);
                                        }}
                                    >
                                        {popularQuery}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {showQueryResults && (
                        <>
                            {suggestions.length > 0 ? (
                                <div className="max-h-[360px] overflow-y-auto">
                                    {suggestions.map((product, index) => {
                                        const hasOffer = isOfferActive(product);
                                        const displayPrice = hasOffer ? product.offer_price ?? product.price : product.price;

                                        return (
                                            <button
                                                key={product.id}
                                                type="button"
                                                className={`flex w-full items-center gap-3 px-3 py-2.5 text-left transition-colors hover:bg-accent ${
                                                    index === activeIndex ? 'bg-accent' : ''
                                                }`}
                                                onMouseEnter={() => setActiveIndex(index)}
                                                onClick={() => navigateToProduct(product.slug)}
                                            >
                                                <img
                                                    src={imagePath(product.thumb_image)}
                                                    alt={product.name}
                                                    className="h-10 w-10 shrink-0 rounded-md border object-cover"
                                                    loading="lazy"
                                                />
                                                <div className="min-w-0 flex-1">
                                                    <p className="truncate text-sm font-medium">{product.name}</p>
                                                    <p className="text-xs text-muted-foreground">
                                                        {[product.category?.name, product.brand?.name]
                                                            .filter(Boolean)
                                                            .join(' • ')}
                                                    </p>
                                                </div>
                                                <div className="shrink-0 text-right">
                                                    <p className="text-sm font-semibold text-primary">
                                                        {displayPrice.toLocaleString('ru-RU')} сом.
                                                    </p>
                                                    {hasOffer && (
                                                        <p className="text-xs text-muted-foreground line-through">
                                                            {product.price.toLocaleString('ru-RU')}
                                                        </p>
                                                    )}
                                                </div>
                                            </button>
                                        );
                                    })}
                                </div>
                            ) : (
                                !loading && (
                                    <div className="border-t p-3 text-sm text-muted-foreground">
                                        Ничего не найдено по запросу «{normalizedQuery}»
                                    </div>
                                )
                            )}

                            {categories.length > 0 && (
                                <div className="border-t p-3">
                                    <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                        Категории
                                    </p>
                                    <div className="flex flex-wrap gap-2">
                                        {categories.map((category, categoryIndex) => {
                                            const optionIndex = suggestions.length + categoryIndex;

                                            return (
                                                <button
                                                    key={category.id}
                                                    type="button"
                                                    className={`rounded-full border px-3 py-1 text-xs transition-colors ${
                                                        activeIndex === optionIndex ? 'bg-accent' : 'hover:bg-accent'
                                                    }`}
                                                    onMouseEnter={() => setActiveIndex(optionIndex)}
                                                    onClick={() =>
                                                        navigateToSearch(normalizedQuery, { category: category.id })
                                                    }
                                                >
                                                    <Tag className="mr-1 inline h-3 w-3" />
                                                    {category.name}
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}

                            {brands.length > 0 && (
                                <div className="border-t p-3">
                                    <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                        Бренды
                                    </p>
                                    <div className="flex flex-wrap gap-2">
                                        {brands.map((brand, brandIndex) => {
                                            const optionIndex = suggestions.length + categories.length + brandIndex;

                                            return (
                                                <button
                                                    key={brand.id}
                                                    type="button"
                                                    className={`rounded-full border px-3 py-1 text-xs transition-colors ${
                                                        activeIndex === optionIndex ? 'bg-accent' : 'hover:bg-accent'
                                                    }`}
                                                    onMouseEnter={() => setActiveIndex(optionIndex)}
                                                    onClick={() => navigateToSearch(normalizedQuery, { brand: brand.id })}
                                                >
                                                    {brand.name}
                                                </button>
                                            );
                                        })}
                                    </div>
                                </div>
                            )}

                            <button
                                type="button"
                                className={`flex w-full items-center justify-center gap-2 border-t px-3 py-2.5 text-sm text-primary transition-colors hover:bg-accent ${
                                    activeIndex === keyboardOptions.length - 1 ? 'bg-accent' : ''
                                }`}
                                onMouseEnter={() => setActiveIndex(keyboardOptions.length - 1)}
                                onClick={() => navigateToSearch(normalizedQuery)}
                            >
                                <Search className="h-3.5 w-3.5" />
                                Все результаты по «{normalizedQuery}»
                            </button>
                        </>
                    )}
                </div>
            )}
        </div>
    );
}
