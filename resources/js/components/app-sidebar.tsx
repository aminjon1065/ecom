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
    Send,
    Settings,
    ShoppingCart,
    Store,
    Users,
} from 'lucide-react';

import brand from '@/routes/admin/brand';
import category from '@/routes/admin/category';
import childCategory from '@/routes/admin/child-category';
import coupon from '@/routes/admin/coupon';
import flashSale from '@/routes/admin/flash-sale';
import order from '@/routes/admin/order';
import product from '@/routes/admin/product';
import products from '@/routes/admin/products';
import review from '@/routes/admin/review';
import sellerProduct from '@/routes/admin/seller-product';
import shippingRule from '@/routes/admin/shipping-rule';
import slider from '@/routes/admin/slider';
import subCategory from '@/routes/admin/sub-category';
import subscriber from '@/routes/admin/subscriber';
import user from '@/routes/admin/user';
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
                href: sellerProduct.index(),
            },
            {
                title: 'Отзывы',
                href: review.index(),
            },
        ],
    },
    {
        title: 'Заказы',
        href: order.index(),
        icon: ShoppingCart,
    },
    {
        title: 'Э-коммерция',
        href: flashSale.index(),
        icon: Store,
        children: [
            {
                title: 'Распродажа',
                href: flashSale.index(),
            },
            {
                title: 'Купоны',
                href: coupon.index(),
            },
            {
                title: 'Правило доставки',
                href: shippingRule.index(),
            },
        ],
    },
    {
        title: 'Управления сайтом',
        href: slider.index(),
        icon: Settings,
        children: [
            {
                title: 'Слайдер',
                href: slider.index(),
            },
        ],
    },
    {
        title: 'Импорт',
        href: products.import().url,
        icon: Download,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Пользователи',
        href: user.index(),
        icon: Users,
    },
    {
        title: 'Рассылки',
        href: subscriber.index(),
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
