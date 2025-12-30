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
import { dashboard } from '@/routes/admin';
import type { SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Heart, SearchIcon, ShoppingCart, User } from 'lucide-react';
import { ReactNode } from 'react';
type AppHeaderLayoutProps = {
    children: ReactNode;
};
const AppHeaderLayout = ({ children }: AppHeaderLayoutProps) => {
    const { auth } = usePage<SharedData>().props;
    console.log(auth.user);
    return (
        <div className="flex min-h-screen flex-col">
            {/* Header */}
            <header className="sticky top-0 z-50 mb-4 w-full border-b bg-background/90 px-[1.4rem] backdrop-blur supports-backdrop-blur:bg-background/90 md:px-16 lg:px-24 xl:px-32 2xl:px-48">
                <div className="flex h-14 items-center gap-4 px-4">
                    {/* Logo */}
                    <Link
                        href={'/'}
                        className="flex items-center gap-2 font-semibold"
                    >
                        <AppLogo />
                    </Link>
                    {/* Navigation */}
                    <NavMain />

                    {/* Spacer */}
                    {/*<div className="flex-1" />*/}

                    {/* Search */}
                    <div className="relative w-7/12">
                        <Input placeholder="Поиск..." className="h-8" />
                        <span className="absolute top-1/2 right-2 -translate-y-1/2 text-xs text-muted-foreground">
                            <SearchIcon className={'h-4 w-4'} />
                        </span>
                    </div>

                    {/* Cart & Wishlist */}
                    <div className="flex">
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="icon">
                                    <Heart className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Избранные</p>
                            </TooltipContent>
                        </Tooltip>

                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button variant="ghost" size="icon">
                                    <ShoppingCart className="h-4 w-4" />
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Корзина</p>
                            </TooltipContent>
                        </Tooltip>
                        <ModeToggle />
                    </div>

                    {/*  User */}
                    {auth.user ? (
                        <Link href={dashboard().url}>
                            <Button>Dashboard</Button>
                        </Link>
                    ) : (
                        <Link href={login().url}>
                            <Button size="sm" className="gap-1">
                                <User className="h-4 w-4" />
                                Login
                            </Button>
                        </Link>
                    )}
                </div>
            </header>

            {/* Content */}
            <main className="flex-1">{children}</main>
        </div>
    );
};

export default AppHeaderLayout;
