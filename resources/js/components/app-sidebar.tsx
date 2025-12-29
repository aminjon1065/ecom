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
import { dashboard } from '@/routes/admin';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import {
    Box,
    LayoutGrid,
    Menu,
    Rss,
    Send,
    Settings,
    ShoppingCart,
    Store,
    Users,
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
        title: 'Категория',
        href: '/#',
        icon: Menu,
        children: [
            {
                title: 'Категория',
                href: '/category',
            },
            {
                title: 'Подкатегория',
                href: '/sub-category',
            },
            {
                title: 'Дочерняя категория',
                href: '/child-category',
            },
        ],
    },
    {
        title: 'Товары',
        href: '/#',
        icon: Box,
        children: [
            {
                title: 'Бренды',
                href: '/brand',
            },
            {
                title: 'Продукты',
                href: '/products',
            },
            {
                title: 'Продукты продавцов',
                href: '/seller-products',
            },
            {
                title: 'Оценка продукта',
                href: '/reviews',
            },
        ],
    },
    {
        title: 'Заказы',
        href: '/orders',
        icon: ShoppingCart,
    },
    {
        title: 'Э-коммерция',
        href: '/#',
        icon: Store,
        children: [
            {
                title: 'Распродажа',
                href: '/flash-sale',
            },
            {
                title: 'Купоны',
                href: '/coupons',
            },
            {
                title: 'Правило доставки',
                href: '/shipping-rule',
            },
        ],
    },
    {
        title: 'Управления сайтом',
        href: '/#',
        icon: Settings,
        children: [
            {
                title: 'Слайдер',
                href: '/slider',
            },
            {
                title: 'О нас',
                href: '/about',
            },
        ],
    },
    {
        title: 'Блог',
        href: '/#',
        icon: Rss,
        children: [
            {
                title: 'Категории',
                href: '/blog-category',
            },
            {
                title: 'Публикация',
                href: '/post',
            },
            {
                title: 'Комментарии',
                href: '/blog-comments',
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Пользователи',
        href: '/users',
        icon: Users,
    },
    {
        title: 'Рассылки',
        href: '/subscribers',
        icon: Send,
    },
];

export function AppSidebar() {
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
                <NavMain items={mainNavItems} title={'Dashboard'} />
                <NavMain items={mainMenu} title={'E-commerce'} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
