import { router } from '@inertiajs/react';
import { Loader2, Search } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';
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
}

interface Props {
    placeholder?: string;
    className?: string;
    inputClassName?: string;
}

function isOfferActive(product: ProductSuggestion): boolean {
    if (!product.offer_price || !product.offer_start_date || !product.offer_end_date) return false;
    const now = new Date();
    return now >= new Date(product.offer_start_date) && now <= new Date(product.offer_end_date);
}

export function SearchSuggestions({ placeholder = 'Поиск товаров...', className = '', inputClassName = '' }: Props) {
    const [query, setQuery] = useState('');
    const [suggestions, setSuggestions] = useState<ProductSuggestion[]>([]);
    const [loading, setLoading] = useState(false);
    const [open, setOpen] = useState(false);
    const [activeIndex, setActiveIndex] = useState(-1);
    const containerRef = useRef<HTMLDivElement>(null);
    const inputRef = useRef<HTMLInputElement>(null);
    const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
    const abortRef = useRef<AbortController | null>(null);

    const fetchSuggestions = useCallback(async (q: string) => {
        // Cancel previous request
        if (abortRef.current) {
            abortRef.current.abort();
        }

        if (q.length < 2) {
            setSuggestions([]);
            setOpen(false);
            setLoading(false);
            return;
        }

        const controller = new AbortController();
        abortRef.current = controller;
        setLoading(true);

        try {
            const res = await fetch(`/api/search?q=${encodeURIComponent(q)}`, {
                signal: controller.signal,
            });
            if (!res.ok) throw new Error();
            const data: ProductSuggestion[] = await res.json();
            setSuggestions(data);
            setOpen(data.length > 0);
            setActiveIndex(-1);
        } catch (e: unknown) {
            if (e instanceof DOMException && e.name === 'AbortError') return;
            setSuggestions([]);
            setOpen(false);
        } finally {
            setLoading(false);
        }
    }, []);

    // Debounced search
    useEffect(() => {
        if (timerRef.current) clearTimeout(timerRef.current);

        if (query.trim().length < 2) {
            setSuggestions([]);
            setOpen(false);
            setLoading(false);
            return;
        }

        setLoading(true);
        timerRef.current = setTimeout(() => {
            fetchSuggestions(query.trim());
        }, 300);

        return () => {
            if (timerRef.current) clearTimeout(timerRef.current);
        };
    }, [query, fetchSuggestions]);

    // Close on click outside
    useEffect(() => {
        const handleClickOutside = (e: MouseEvent) => {
            if (containerRef.current && !containerRef.current.contains(e.target as Node)) {
                setOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const navigateToProduct = (slug: string) => {
        setOpen(false);
        setQuery('');
        router.get(`/products/${slug}`);
    };

    const navigateToSearch = () => {
        if (!query.trim()) return;
        setOpen(false);
        router.get('/products', { search: query.trim() });
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (!open || suggestions.length === 0) {
            if (e.key === 'Enter') {
                e.preventDefault();
                navigateToSearch();
            }
            return;
        }

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                setActiveIndex((prev) => (prev < suggestions.length - 1 ? prev + 1 : 0));
                break;
            case 'ArrowUp':
                e.preventDefault();
                setActiveIndex((prev) => (prev > 0 ? prev - 1 : suggestions.length - 1));
                break;
            case 'Enter':
                e.preventDefault();
                if (activeIndex >= 0 && activeIndex < suggestions.length) {
                    navigateToProduct(suggestions[activeIndex].slug);
                } else {
                    navigateToSearch();
                }
                break;
            case 'Escape':
                setOpen(false);
                inputRef.current?.blur();
                break;
        }
    };

    return (
        <div ref={containerRef} className={`relative ${className}`}>
            <div className="relative">
                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <Input
                    ref={inputRef}
                    type="text"
                    placeholder={placeholder}
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    onKeyDown={handleKeyDown}
                    onFocus={() => {
                        if (suggestions.length > 0) setOpen(true);
                    }}
                    className={`pl-10 pr-9 ${inputClassName}`}
                    autoComplete="off"
                />
                {loading && (
                    <Loader2 className="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-muted-foreground" />
                )}
            </div>

            {/* Suggestions dropdown */}
            {open && suggestions.length > 0 && (
                <div className="absolute left-0 right-0 top-full z-[60] mt-1 max-h-[400px] overflow-y-auto rounded-lg border bg-popover shadow-lg">
                    {suggestions.map((product, index) => {
                        const hasOffer = isOfferActive(product);
                        const displayPrice = hasOffer ? product.offer_price! : product.price;

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
                                    src={`/${product.thumb_image}`}
                                    alt={product.name}
                                    className="h-10 w-10 shrink-0 rounded-md border object-cover"
                                    loading="lazy"
                                />
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-sm font-medium">{product.name}</p>
                                    <p className="text-xs text-muted-foreground">
                                        {product.category?.name || ''}
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

                    {/* Show all results link */}
                    <button
                        type="button"
                        className="flex w-full items-center justify-center gap-2 border-t px-3 py-2.5 text-sm text-primary transition-colors hover:bg-accent"
                        onClick={navigateToSearch}
                    >
                        <Search className="h-3.5 w-3.5" />
                        Все результаты по "{query}"
                    </button>
                </div>
            )}

            {/* No results */}
            {open && suggestions.length === 0 && !loading && query.trim().length >= 2 && (
                <div className="absolute left-0 right-0 top-full z-[60] mt-1 rounded-lg border bg-popover p-4 text-center text-sm text-muted-foreground shadow-lg">
                    Ничего не найдено
                </div>
            )}
        </div>
    );
}
