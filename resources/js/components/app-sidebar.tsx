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
    Download,
    LayoutGrid,
    Menu,
    Rss,
    Send,
    Settings,
    ShoppingCart,
    Store,
    Users,
} from 'lucide-react';

import brand from '@/routes/admin/brand';
import category from '@/routes/admin/category';
import childCategory from '@/routes/admin/child-category';
import product from '@/routes/admin/product';
import subCategory from '@/routes/admin/sub-category';
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
        title: 'Категории',
        href: category.index(),
        icon: Menu,
        children: [
            {
                title: 'Категории',
                href: category.index(),
            },
            {
                title: 'Подкатегория',
                href: subCategory.index(),
            },
            {
                title: 'Дочерняя категория',
                href: childCategory.index(),
            },
        ],
    },
    {
        title: 'Товары',
        href: product.index(),
        icon: Box,
        children: [
            {
                title: 'Бренды',
                href: brand.index(),
            },
            {
                title: 'Продукты',
                href: product.index(),
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
    {
        title: 'Импорт',
        href: '/orders',
        icon: Download,
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
