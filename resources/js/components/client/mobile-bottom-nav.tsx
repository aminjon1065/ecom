import { Link, usePage } from '@inertiajs/react';
import { Heart, Home, LayoutGrid, ShoppingCart, User } from 'lucide-react';
import type { SharedData } from '@/types';

interface Props {
    onCatalogOpen: () => void;
    catalogOpen: boolean;
}

export function MobileBottomNav({ onCatalogOpen, catalogOpen }: Props) {
    const { auth, cartCount, wishlistCount } = usePage<SharedData & { cartCount?: number; wishlistCount?: number }>().props;
    const url = usePage().url;

    const isActive = (path: string) => {
        if (path === '/') return url === '/' && !catalogOpen;
        return url.startsWith(path) && !catalogOpen;
    };

    return (
        <nav className="fixed bottom-0 left-0 right-0 z-50 border-t bg-background/95 backdrop-blur supports-backdrop-blur:bg-background/60 md:hidden">
            <div className="flex items-center justify-around">
                {/* Главная */}
                <Link
                    href="/"
                    className={`flex flex-1 flex-col items-center gap-0.5 py-2 transition-colors ${
                        isActive('/') ? 'text-primary' : 'text-muted-foreground active:text-primary'
                    }`}
                >
                    <Home className="h-5 w-5" />
                    <span className="text-[10px]">Главная</span>
                </Link>

                {/* Каталог */}
                <button
                    onClick={onCatalogOpen}
                    className={`flex flex-1 flex-col items-center gap-0.5 py-2 transition-colors ${
                        catalogOpen ? 'text-primary' : 'text-muted-foreground active:text-primary'
                    }`}
                >
                    <LayoutGrid className="h-5 w-5" />
                    <span className="text-[10px]">Каталог</span>
                </button>

                {/* Корзина */}
                <Link
                    href={auth.user ? '/cart' : '/login'}
                    className={`flex flex-1 flex-col items-center gap-0.5 py-2 transition-colors ${
                        isActive('/cart') ? 'text-primary' : 'text-muted-foreground active:text-primary'
                    }`}
                >
                    <div className="relative">
                        <ShoppingCart className="h-5 w-5" />
                        {(cartCount ?? 0) > 0 && (
                            <span className="absolute -top-1.5 -right-2.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary px-1 text-[9px] font-medium text-primary-foreground">
                                {cartCount}
                            </span>
                        )}
                    </div>
                    <span className="text-[10px]">Корзина</span>
                </Link>

                {/* Избранное */}
                <Link
                    href={auth.user ? '/wishlist' : '/login'}
                    className={`flex flex-1 flex-col items-center gap-0.5 py-2 transition-colors ${
                        isActive('/wishlist') ? 'text-primary' : 'text-muted-foreground active:text-primary'
                    }`}
                >
                    <div className="relative">
                        <Heart className="h-5 w-5" />
                        {(wishlistCount ?? 0) > 0 && (
                            <span className="absolute -top-1.5 -right-2.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-destructive px-1 text-[9px] font-medium text-white">
                                {wishlistCount}
                            </span>
                        )}
                    </div>
                    <span className="text-[10px]">Избранное</span>
                </Link>

                {/* Профиль */}
                <Link
                    href={auth.user ? '/account' : '/login'}
                    className={`flex flex-1 flex-col items-center gap-0.5 py-2 transition-colors ${
                        isActive('/account') ? 'text-primary' : 'text-muted-foreground active:text-primary'
                    }`}
                >
                    <User className="h-5 w-5" />
                    <span className="text-[10px]">Профиль</span>
                </Link>
            </div>
            {/* Safe area for iPhones with home indicator */}
            <div className="h-[env(safe-area-inset-bottom)]" />
        </nav>
    );
}
