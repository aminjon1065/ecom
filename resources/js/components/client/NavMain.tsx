import { Button } from '@/components/ui/button';
import type { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronRight, LayoutGrid } from 'lucide-react';
import { ImgHTMLAttributes, useEffect, useRef, useState } from 'react';

interface ChildCategory {
    id: number;
    name: string;
    slug: string;
}

interface SubCategory {
    id: number;
    name: string;
    slug: string;
    child_category: ChildCategory[];
}

interface Category {
    id: number;
    name: string;
    slug: string;
    icon?: string  | ImgHTMLAttributes<HTMLImageElement> | undefined;
    sub_categories: SubCategory[];
}

export function NavMain() {
    const { categoriesMenu } = usePage<
        SharedData & { categoriesMenu: Category[] }
    >().props;
    const [open, setOpen] = useState(false);
    const [activeCategory, setActiveCategory] = useState<Category | null>(null);
    const containerRef = useRef<HTMLDivElement | null>(null);

    const cats = categoriesMenu || [];

    useEffect(() => {
        if (!open) {
            return;
        }

        const handlePointerDown = (event: MouseEvent) => {
            if (!containerRef.current) {
                return;
            }

            if (!containerRef.current.contains(event.target as Node)) {
                setOpen(false);
                setActiveCategory(null);
            }
        };

        document.addEventListener('mousedown', handlePointerDown);

        return () => {
            document.removeEventListener('mousedown', handlePointerDown);
        };
    }, [open]);

    return (
        <>
            {/* Desktop Mega Menu */}
            <div ref={containerRef} className="relative hidden md:block">
                <Button
                    variant="ghost"
                    className="gap-2 font-medium"
                    aria-expanded={open}
                    aria-haspopup="menu"
                    onClick={() => {
                        if (open) {
                            setOpen(false);
                            setActiveCategory(null);

                            return;
                        }

                        setOpen(true);
                        setActiveCategory(cats[0] ?? null);
                    }}
                >
                    <LayoutGrid className="h-4 w-4" />
                    Каталог
                </Button>

                {open && (
                    <>
                        {/* Menu panel */}
                        <div className="absolute top-full left-0 z-50 flex rounded-lg border bg-popover shadow-xl">
                            {/* Left: categories list */}
                            <div className="w-64 border-r py-2">
                                <div className="mb-1 px-4 py-1.5">
                                    <Link
                                        href="/products"
                                        onClick={() => setOpen(false)}
                                        className="text-sm font-semibold text-primary hover:underline"
                                    >
                                        Все товары
                                    </Link>
                                </div>
                                {cats.map((cat) => (
                                    cat.sub_categories?.length > 0 ? (
                                        <button
                                            key={cat.id}
                                            type="button"
                                            className={`flex w-full items-center justify-between px-4 py-2 text-left text-sm transition-colors hover:bg-accent ${
                                                activeCategory?.id === cat.id
                                                    ? 'bg-accent font-medium'
                                                    : ''
                                            }`}
                                            onMouseEnter={() =>
                                                setActiveCategory(cat)
                                            }
                                            onClick={() =>
                                                setActiveCategory(cat)
                                            }
                                        >
                                            <span className="flex flex-1 items-center gap-2">
                                                <img
                                                    src={`/${cat.icon}`}
                                                    alt={cat.name}
                                                    className="h-6 w-6"
                                                />
                                                <span>{cat.name}</span>
                                            </span>
                                            <ChevronRight className="h-3.5 w-3.5 text-muted-foreground" />
                                        </button>
                                    ) : (
                                        <Link
                                            key={cat.id}
                                            href={`/products?category=${cat.id}`}
                                            onClick={() => setOpen(false)}
                                            className={`flex items-center justify-between px-4 py-2 text-sm transition-colors hover:bg-accent ${
                                                activeCategory?.id === cat.id
                                                    ? 'bg-accent font-medium'
                                                    : ''
                                            }`}
                                        >
                                            <span className="flex flex-1 items-center gap-2">
                                                <img
                                                    src={`/${cat.icon}`}
                                                    alt={cat.name}
                                                    className="h-6 w-6"
                                                />
                                                <span>{cat.name}</span>
                                            </span>
                                        </Link>
                                    )
                                ))}
                            </div>

                            {/* Right: subcategories + child categories */}
                            {activeCategory &&
                                activeCategory.sub_categories?.length > 0 && (
                                    <div className="w-125 p-4">
                                        <div className="mb-3">
                                            <Link
                                                href={`/products?category=${activeCategory.id}`}
                                                onClick={() => setOpen(false)}
                                                className="text-base font-semibold hover:text-primary"
                                            >
                                                {activeCategory.name}
                                            </Link>
                                        </div>
                                        <div className="grid grid-cols-2 gap-x-6 gap-y-4">
                                            {activeCategory.sub_categories.map(
                                                (sub) => (
                                                    <div key={sub.id}>
                                                        <Link
                                                            href={`/products?category=${activeCategory.id}&sub_category=${sub.id}`}
                                                            onClick={() =>
                                                                setOpen(false)
                                                            }
                                                            className="text-sm font-semibold hover:text-primary"
                                                        >
                                                            {sub.name}
                                                        </Link>
                                                        {sub.child_category
                                                            ?.length > 0 && (
                                                            <ul className="mt-1.5 space-y-1">
                                                                {sub.child_category.map(
                                                                    (child) => (
                                                                        <li
                                                                            key={
                                                                                child.id
                                                                            }
                                                                        >
                                                                            <Link
                                                                                href={`/products?category=${activeCategory.id}&sub_category=${sub.id}&child_category=${child.id}`}
                                                                                onClick={() =>
                                                                                    setOpen(
                                                                                        false,
                                                                                    )
                                                                                }
                                                                                className="text-sm text-muted-foreground hover:text-foreground"
                                                                            >
                                                                                {
                                                                                    child.name
                                                                                }
                                                                            </Link>
                                                                        </li>
                                                                    ),
                                                                )}
                                                            </ul>
                                                        )}
                                                    </div>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                )}
                        </div>
                    </>
                )}
            </div>

            {/* Mobile catalog is handled by MobileBottomNav + MobileCatalogOverlay */}
        </>
    );
}
