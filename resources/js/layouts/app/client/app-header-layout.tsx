import AppLogo from '@/components/app-logo';
import { NavMain } from '@/components/client/NavMain';
import { ModeToggle } from '@/components/mode-toggle';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Tooltip,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { login } from '@/routes';
import { index as cartIndex } from '@/routes/cart';
import { index as wishlistIndex } from '@/routes/wishlist';
import { dashboard as accountDashboard } from '@/routes/account';
import type { SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Heart, LogIn, Search, ShoppingCart, User } from 'lucide-react';
import { ReactNode, useState } from 'react';

type AppHeaderLayoutProps = {
    children: ReactNode;
};

const AppHeaderLayout = ({ children }: AppHeaderLayoutProps) => {
    const { auth, cartCount, wishlistCount } = usePage<SharedData & { cartCount?: number; wishlistCount?: number }>().props;
    const [searchQuery, setSearchQuery] = useState('');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        if (searchQuery.trim()) {
            router.get('/products', { search: searchQuery });
        }
    };

    return (
        <div className="flex min-h-screen flex-col justify-between">
            <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-backdrop-blur:bg-background/60">
                <div className="mx-auto flex h-14 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <Link href="/" className="flex shrink-0 items-center gap-2 font-semibold">
                        <AppLogo />
                    </Link>

                    <NavMain />

                    <form onSubmit={handleSearch} className="relative hidden w-full  md:block">
                        <Input
                            placeholder="Поиск товаров..."
                            className="h-9 pr-8"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                        />
                        <button type="submit" className="absolute top-1/2 right-2.5 -translate-y-1/2 text-muted-foreground hover:text-foreground">
                            <Search className="h-4 w-4" />
                        </button>
                    </form>

                    <div className="flex items-center gap-1">
                        {auth.user && (
                            <>
                                <Tooltip>
                                    <TooltipTrigger asChild>
                                        <Link href={wishlistIndex().url}>
                                            <Button variant="ghost" size="icon" className="relative">
                                                <Heart className="h-4 w-4" />
                                                {(wishlistCount ?? 0) > 0 && (
                                                    <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-destructive text-[10px] text-white">
                                                        {wishlistCount}
                                                    </span>
                                                )}
                                            </Button>
                                        </Link>
                                    </TooltipTrigger>
                                    <TooltipContent>Избранные</TooltipContent>
                                </Tooltip>

                                <Tooltip>
                                    <TooltipTrigger asChild>
                                        <Link href={cartIndex().url}>
                                            <Button variant="ghost" size="icon" className="relative">
                                                <ShoppingCart className="h-4 w-4" />
                                                {(cartCount ?? 0) > 0 && (
                                                    <span className="absolute -top-1 -right-1 flex h-4 w-4 items-center justify-center rounded-full bg-primary text-[10px] text-primary-foreground">
                                                        {cartCount}
                                                    </span>
                                                )}
                                            </Button>
                                        </Link>
                                    </TooltipTrigger>
                                    <TooltipContent>Корзина</TooltipContent>
                                </Tooltip>
                            </>
                        )}

                        <ModeToggle />

                        {auth.user ? (
                            <Link href={accountDashboard().url}>
                                <Button variant="ghost" size="sm" className="gap-1.5">
                                    <User className="h-4 w-4" />
                                    <span className="hidden sm:inline">{auth.user.name}</span>
                                </Button>
                            </Link>
                        ) : (
                            <Link href={login().url}>
                                <Button size="sm" className="gap-1.5">
                                    <LogIn className="h-4 w-4" />
                                    Войти
                                </Button>
                            </Link>
                        )}
                    </div>
                </div>
            </header>

            <main className="flex-1">{children}</main>

            <footer className="border-t bg-muted/40">
                <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                    <div className="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <h3 className="mb-3 font-semibold">О нас</h3>
                            <p className="text-sm text-muted-foreground">
                                Современная платформа электронной коммерции с широким ассортиментом товаров.
                            </p>
                        </div>
                        <div>
                            <h3 className="mb-3 font-semibold">Категории</h3>
                            <ul className="space-y-2 text-sm text-muted-foreground">
                                <li><Link href="/products" className="hover:text-foreground">Все товары</Link></li>
                                <li><Link href="/products?sort=latest" className="hover:text-foreground">Новинки</Link></li>
                                <li><Link href="/products?sort=popular" className="hover:text-foreground">Популярные</Link></li>
                            </ul>
                        </div>
                        <div>
                            <h3 className="mb-3 font-semibold">Информация</h3>
                            <ul className="space-y-2 text-sm text-muted-foreground">
                                <li><a href="#" className="hover:text-foreground">Доставка</a></li>
                                <li><a href="#" className="hover:text-foreground">Оплата</a></li>
                                <li><a href="#" className="hover:text-foreground">Возврат</a></li>
                            </ul>
                        </div>
                        <div>
                            <h3 className="mb-3 font-semibold">Контакты</h3>
                            <ul className="space-y-2 text-sm text-muted-foreground">
                                <li>info@ecom.tj</li>
                                <li>+992 (00) 000-00-00</li>
                            </ul>
                        </div>
                    </div>
                    <div className="mt-8 border-t pt-6 text-center text-sm text-muted-foreground">
                        &copy; {new Date().getFullYear()} Ecom. Все права защищены.
                    </div>
                </div>
            </footer>
        </div>
    );
};

export default AppHeaderLayout;
