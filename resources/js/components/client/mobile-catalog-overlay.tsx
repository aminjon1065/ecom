import type { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { ImgHTMLAttributes, useEffect, useState } from 'react';

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
    icon?: string | ImgHTMLAttributes<HTMLImageElement> | undefined;
    sub_categories: SubCategory[];
}

interface Props {
    open: boolean;
    onClose: () => void;
}

export function MobileCatalogOverlay({ open, onClose }: Props) {
    const { categoriesMenu } = usePage<SharedData & { categoriesMenu: Category[] }>().props;
    const cats = categoriesMenu || [];

    const [expandedCat, setExpandedCat] = useState<number | null>(null);
    const [expandedSub, setExpandedSub] = useState<number | null>(null);

    useEffect(() => {
        if (open) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
            setExpandedCat(null);
            setExpandedSub(null);
        }
        return () => {
            document.body.style.overflow = '';
        };
    }, [open]);

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-[55] bg-background md:hidden" style={{ top: '101px', bottom: '56px' }}>
            <div className="h-full overflow-y-auto">
                <div className="py-2">
                    {/* Скидки & Бренды quick links */}
                    <Link
                        href="/products"
                        onClick={onClose}
                        className="flex items-center gap-3 px-4 py-3 text-sm font-medium transition-colors active:bg-accent"
                    >
                        <span>Все товары</span>
                    </Link>

                    <div className="mx-4 border-b" />

                    {/* Category List */}
                    {cats.map((cat) => (
                        <div key={cat.id}>
                            <div className="flex items-center">
                                <Link
                                    href={`/products?category=${cat.id}`}
                                    onClick={(e) => {
                                        if (cat.sub_categories?.length > 0) {
                                            e.preventDefault();
                                            setExpandedCat(expandedCat === cat.id ? null : cat.id);
                                            setExpandedSub(null);
                                        } else {
                                            onClose();
                                        }
                                    }}
                                    className={`flex flex-1 items-center gap-3 px-4 py-3 text-sm transition-colors active:bg-accent ${
                                        expandedCat === cat.id ? 'font-medium text-primary' : ''
                                    }`}
                                >
                                    {cat.icon && typeof cat.icon === 'string' && (
                                        <img src={`${cat.icon}`} alt={cat.name} className="h-6 w-6" />
                                    )}
                                    <span>{cat.name}</span>
                                </Link>
                                {cat.sub_categories?.length > 0 && (
                                    <button
                                        onClick={() => {
                                            setExpandedCat(expandedCat === cat.id ? null : cat.id);
                                            setExpandedSub(null);
                                        }}
                                        className="px-4 py-3"
                                    >
                                        <ChevronRight
                                            className={`h-4 w-4 text-muted-foreground transition-transform ${
                                                expandedCat === cat.id ? 'rotate-90' : ''
                                            }`}
                                        />
                                    </button>
                                )}
                            </div>

                            {/* Sub-categories */}
                            {expandedCat === cat.id && cat.sub_categories?.length > 0 && (
                                <div className="bg-muted/30">
                                    <Link
                                        href={`/products?category=${cat.id}`}
                                        onClick={onClose}
                                        className="block px-8 py-2 text-xs text-primary active:underline"
                                    >
                                        Все в «{cat.name}»
                                    </Link>
                                    {cat.sub_categories.map((sub) => (
                                        <div key={sub.id}>
                                            <div className="flex items-center">
                                                <Link
                                                    href={`/products?category=${cat.id}&sub_category=${sub.id}`}
                                                    onClick={(e) => {
                                                        if (sub.child_category?.length > 0) {
                                                            e.preventDefault();
                                                            setExpandedSub(expandedSub === sub.id ? null : sub.id);
                                                        } else {
                                                            onClose();
                                                        }
                                                    }}
                                                    className="flex-1 px-8 py-2.5 text-sm active:bg-accent"
                                                >
                                                    {sub.name}
                                                </Link>
                                                {sub.child_category?.length > 0 && (
                                                    <button
                                                        onClick={() =>
                                                            setExpandedSub(expandedSub === sub.id ? null : sub.id)
                                                        }
                                                        className="px-4 py-2.5"
                                                    >
                                                        <ChevronRight
                                                            className={`h-3.5 w-3.5 text-muted-foreground transition-transform ${
                                                                expandedSub === sub.id ? 'rotate-90' : ''
                                                            }`}
                                                        />
                                                    </button>
                                                )}
                                            </div>

                                            {/* Child categories */}
                                            {expandedSub === sub.id && sub.child_category?.length > 0 && (
                                                <div className="bg-muted/20 pb-1">
                                                    {sub.child_category.map((child) => (
                                                        <Link
                                                            key={child.id}
                                                            href={`/products?category=${cat.id}&sub_category=${sub.id}&child_category=${child.id}`}
                                                            onClick={onClose}
                                                            className="block px-12 py-2 text-sm text-muted-foreground active:text-foreground"
                                                        >
                                                            {child.name}
                                                        </Link>
                                                    ))}
                                                </div>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
