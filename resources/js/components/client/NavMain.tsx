import { Button } from '@/components/ui/button';
import type { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronRight, LayoutGrid, Menu, X } from 'lucide-react';
import { ImgHTMLAttributes, useState } from 'react';

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
    const { categories } = usePage<SharedData & { categories: Category[] }>()
        .props;
    const [open, setOpen] = useState(false);
    const [activeCategory, setActiveCategory] = useState<Category | null>(null);
    const [mobileOpen, setMobileOpen] = useState(false);
    const [mobileExpanded, setMobileExpanded] = useState<number | null>(null);
    const [mobileSubExpanded, setMobileSubExpanded] = useState<number | null>(
        null,
    );

    const cats = categories || [];

    return (
        <>
            {/* Desktop Mega Menu */}
            <div className="relative hidden md:block">
                <Button
                    variant="ghost"
                    className="gap-2 font-medium"
                    onMouseEnter={() => setOpen(true)}
                    onClick={() => setOpen(!open)}
                >
                    <LayoutGrid className="h-4 w-4" />
                    Каталог
                </Button>

                {open && (
                    <>
                        {/* Backdrop */}
                        <div
                            className="fixed inset-0 z-40"
                            onClick={() => {
                                setOpen(false);
                                setActiveCategory(null);
                            }}
                        />

                        {/* Menu panel */}
                        <div
                            className="absolute top-full left-0 z-50 flex rounded-lg border bg-popover shadow-xl"
                            onMouseLeave={() => {
                                setOpen(false);
                                setActiveCategory(null);
                            }}
                        >
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
                                    <div
                                        key={cat.id}
                                        className={`flex cursor-pointer items-center justify-between px-4 py-2 text-sm transition-colors hover:bg-accent ${
                                            activeCategory?.id === cat.id
                                                ? 'bg-accent font-medium'
                                                : ''
                                        }`}
                                        onMouseEnter={() =>
                                            setActiveCategory(cat)
                                        }
                                        onClick={() => {
                                            if (!cat.sub_categories?.length) {
                                                setOpen(false);
                                            }
                                        }}
                                    >
                                        <Link
                                            href={`/products?category=${cat.id}`}
                                            onClick={() => setOpen(false)}
                                            className="flex flex-1 items-center gap-2"
                                        >
                                            <img
                                                src={`${cat.icon}`}
                                                alt={cat.name}
                                                className={"w-6 h-6"}
                                            />
                                            <span>{cat.name}</span>
                                        </Link>
                                        {cat.sub_categories?.length > 0 && (
                                            <ChevronRight className="h-3.5 w-3.5 text-muted-foreground" />
                                        )}
                                    </div>
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

            {/* Mobile Catalog Button */}
            <Button
                variant="ghost"
                size="icon"
                className="md:hidden"
                onClick={() => setMobileOpen(true)}
            >
                <Menu className="h-5 w-5" />
            </Button>

            {/* Mobile Drawer */}
            {mobileOpen && (
                <>
                    <div
                        className="fixed inset-0 z-50 bg-black/50"
                        onClick={() => setMobileOpen(false)}
                    />
                    <div className="fixed inset-y-0 left-0 z-50 w-80 overflow-y-auto bg-background shadow-xl">
                        <div className="flex items-center justify-between border-b px-4 py-3">
                            <span className="font-semibold">Каталог</span>
                            <Button
                                variant="ghost"
                                size="icon"
                                onClick={() => setMobileOpen(false)}
                            >
                                <X className="h-5 w-5" />
                            </Button>
                        </div>

                        <div className="py-2">
                            <Link
                                href="/products"
                                onClick={() => setMobileOpen(false)}
                                className="block px-4 py-2.5 text-sm font-semibold text-primary"
                            >
                                Все товары
                            </Link>

                            {cats.map((cat) => (
                                <div
                                    key={cat.id}
                                    className="border-b last:border-0"
                                >
                                    <div
                                        className="flex cursor-pointer items-center justify-between px-4 py-2.5"
                                        onClick={() =>
                                            setMobileExpanded(
                                                mobileExpanded === cat.id
                                                    ? null
                                                    : cat.id,
                                            )
                                        }
                                    >
                                        <Link
                                            href={`/products?category=${cat.id}`}
                                            onClick={(e) => {
                                                if (
                                                    cat.sub_categories?.length >
                                                    0
                                                ) {
                                                    e.preventDefault();
                                                } else {
                                                    setMobileOpen(false);
                                                }
                                            }}
                                            className="flex items-center gap-2 text-sm font-medium"
                                        >
                                            {cat.icon && (
                                                <img src={`${cat.icon}`} alt={cat.name} />
                                            )}
                                            {cat.name}
                                        </Link>
                                        {cat.sub_categories?.length > 0 && (
                                            <ChevronRight
                                                className={`h-4 w-4 text-muted-foreground transition-transform ${
                                                    mobileExpanded === cat.id
                                                        ? 'rotate-90'
                                                        : ''
                                                }`}
                                            />
                                        )}
                                    </div>

                                    {mobileExpanded === cat.id &&
                                        cat.sub_categories?.length > 0 && (
                                            <div className="bg-muted/30 pb-2">
                                                <Link
                                                    href={`/products?category=${cat.id}`}
                                                    onClick={() =>
                                                        setMobileOpen(false)
                                                    }
                                                    className="block px-6 py-1.5 text-xs text-primary hover:underline"
                                                >
                                                    Смотреть все в «{cat.name}»
                                                </Link>
                                                {cat.sub_categories.map(
                                                    (sub) => (
                                                        <div key={sub.id}>
                                                            <div
                                                                className="flex cursor-pointer items-center justify-between px-6 py-2"
                                                                onClick={(
                                                                    e,
                                                                ) => {
                                                                    e.stopPropagation();
                                                                    setMobileSubExpanded(
                                                                        mobileSubExpanded ===
                                                                            sub.id
                                                                            ? null
                                                                            : sub.id,
                                                                    );
                                                                }}
                                                            >
                                                                <Link
                                                                    href={`/products?category=${cat.id}&sub_category=${sub.id}`}
                                                                    onClick={(
                                                                        e,
                                                                    ) => {
                                                                        if (
                                                                            sub
                                                                                .child_category
                                                                                ?.length >
                                                                            0
                                                                        ) {
                                                                            e.preventDefault();
                                                                        } else {
                                                                            setMobileOpen(
                                                                                false,
                                                                            );
                                                                        }
                                                                    }}
                                                                    className="text-sm font-medium"
                                                                >
                                                                    {sub.name}
                                                                </Link>
                                                                {sub
                                                                    .child_category
                                                                    ?.length >
                                                                    0 && (
                                                                    <ChevronRight
                                                                        className={`h-3.5 w-3.5 text-muted-foreground transition-transform ${
                                                                            mobileSubExpanded ===
                                                                            sub.id
                                                                                ? 'rotate-90'
                                                                                : ''
                                                                        }`}
                                                                    />
                                                                )}
                                                            </div>

                                                            {mobileSubExpanded ===
                                                                sub.id &&
                                                                sub
                                                                    .child_category
                                                                    ?.length >
                                                                    0 && (
                                                                    <div className="pb-1 pl-10">
                                                                        {sub.child_category.map(
                                                                            (
                                                                                child,
                                                                            ) => (
                                                                                <Link
                                                                                    key={
                                                                                        child.id
                                                                                    }
                                                                                    href={`/products?category=${cat.id}&sub_category=${sub.id}&child_category=${child.id}`}
                                                                                    onClick={() =>
                                                                                        setMobileOpen(
                                                                                            false,
                                                                                        )
                                                                                    }
                                                                                    className="block py-1.5 text-sm text-muted-foreground hover:text-foreground"
                                                                                >
                                                                                    {
                                                                                        child.name
                                                                                    }
                                                                                </Link>
                                                                            ),
                                                                        )}
                                                                    </div>
                                                                )}
                                                        </div>
                                                    ),
                                                )}
                                            </div>
                                        )}
                                </div>
                            ))}
                        </div>
                    </div>
                </>
            )}
        </>
    );
}
