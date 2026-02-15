import { Card, CardContent } from '@/components/ui/card';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { logout } from '@/routes';
import { Link } from '@inertiajs/react';
import {
    LayoutDashboard,
    LogOutIcon,
    MapPin,
    ShoppingBag,
    User,
} from 'lucide-react';
import { ReactNode } from 'react';

interface Props {
    activePath: string;
    children: ReactNode;
    title: string;
}

function AccountSidebar({ activePath }: { activePath: string }) {
    const navItems = [
        { href: '/account', label: 'Дашборд', icon: LayoutDashboard },
        { href: '/account/orders', label: 'Заказы', icon: ShoppingBag },
        { href: '/account/addresses', label: 'Адреса', icon: MapPin },
        { href: '/account/profile', label: 'Профиль', icon: User },
    ];

    return (
        <aside className="w-full space-y-2 lg:w-64">
            <Card>
                <CardContent className="p-4">
                    <nav className="space-y-1">
                        {navItems.map((item) => {
                            const Icon = item.icon;
                            const isActive = activePath === item.href;

                            return (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className={`flex items-center gap-3 rounded-md px-3 py-2 transition-colors ${
                                        isActive
                                            ? 'bg-primary text-primary-foreground'
                                            : 'hover:bg-muted'
                                    }`}
                                >
                                    <Icon className="h-5 w-5" />
                                    <span className="font-medium">
                                        {item.label}
                                    </span>
                                </Link>
                            );
                        })}
                        <Link
                            href={logout()}
                            method={'post'}
                            className={`flex items-center gap-3 w-full rounded-md px-3 py-2 transition-colors hover:bg-muted`}
                        >
                            <LogOutIcon className="h-5 w-5" />
                            <span className="font-medium">Выход</span>
                        </Link>
                    </nav>
                </CardContent>
            </Card>
        </aside>
    );
}

export default function AppAccountLayout({
    activePath,
    children,
    title,
}: Props) {
    return (
        <AppHeaderLayout>
            <div className="container mx-auto px-4 py-8">
                <h1 className="mb-8 text-3xl font-bold">{title}</h1>

                <div className="flex flex-col gap-6 lg:flex-row">
                    <AccountSidebar activePath={activePath} />
                    <div className="flex-1">{children}</div>
                </div>
            </div>
        </AppHeaderLayout>
    );
}
