import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, profile } from '@/routes/vendor';
import product from '@/routes/vendor/product';
import order from '@/routes/vendor/order';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import {
    Box,
    LayoutGrid,
    Plus,
    ShoppingCart,
    Store,
} from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Дашборд',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const mainMenu: NavItem[] = [
    {
        title: 'Товары',
        href: product.index(),
        icon: Box,
        children: [
            {
                title: 'Все товары',
                href: product.index(),
            },
            {
                title: 'Добавить товар',
                href: product.create(),
                icon: Plus,
            },
        ],
    },
    {
        title: 'Заказы',
        href: order.index(),
        icon: ShoppingCart,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Настройки магазина',
        href: profile(),
        icon: Store,
    },
];

export function VendorSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} title={'Панель продавца'} />
                <NavMain items={mainMenu} title={'Управление'} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
