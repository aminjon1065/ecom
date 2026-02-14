import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';
import { ArrowLeft, Search, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface Props {
    open: boolean;
    onClose: () => void;
}

export function MobileSearchOverlay({ open, onClose }: Props) {
    const [query, setQuery] = useState('');
    const inputRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (open) {
            // Small delay to let the overlay animate in
            const t = setTimeout(() => inputRef.current?.focus(), 100);
            // Prevent body scroll
            document.body.style.overflow = 'hidden';
            return () => {
                clearTimeout(t);
                document.body.style.overflow = '';
            };
        }
    }, [open]);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        if (query.trim()) {
            router.get('/products', { search: query });
            onClose();
            setQuery('');
        }
    };

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-[60] bg-background md:hidden">
            <div className="flex h-14 items-center gap-2 border-b px-3">
                <Button
                    variant="ghost"
                    size="icon"
                    className="shrink-0"
                    onClick={() => {
                        onClose();
                        setQuery('');
                    }}
                >
                    <ArrowLeft className="h-5 w-5" />
                </Button>

                <form onSubmit={handleSearch} className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        ref={inputRef}
                        type="search"
                        placeholder="Поиск товаров..."
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        className="h-10 pl-10 pr-10"
                    />
                    {query && (
                        <button
                            type="button"
                            onClick={() => setQuery('')}
                            className="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground"
                        >
                            <X className="h-4 w-4" />
                        </button>
                    )}
                </form>
            </div>

            <div className="p-4">
                <p className="text-sm text-muted-foreground">
                    Введите название товара для поиска
                </p>
            </div>
        </div>
    );
}
