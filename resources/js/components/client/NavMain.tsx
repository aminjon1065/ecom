import { GalleryVerticalEndIcon } from 'lucide-react';
import * as React from 'react';

import {
    NavigationMenu,
    NavigationMenuContent,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    NavigationMenuTrigger,
} from '@/components/ui/navigation-menu';
import { useIsMobile } from '@/hooks/use-mobile';
import { Link } from '@inertiajs/react';

export function NavMain() {
    const isMobile = useIsMobile();
    return (
        <NavigationMenu viewport={isMobile}>
            <NavigationMenuList className="flex-wrap">
                <NavigationMenuItem>
                    <NavigationMenuTrigger
                        className={
                            'cursor-pointer bg-sidebar-primary-foreground dark:bg-[#161617]'
                        }
                    >
                        <div className="flex items-center gap-1 space-x-2">
                            <GalleryVerticalEndIcon /> Каталог
                        </div>
                    </NavigationMenuTrigger>
                    <NavigationMenuContent>
                        <ul className="grid gap-2 md:w-100 lg:w-125 lg:grid-cols-[.75fr_1fr]">
                            <ListItem href="/docs" title="Introduction">
                                Re-usable components built using Radix UI and
                                Tailwind CSS.
                            </ListItem>
                            <ListItem
                                href="/docs/installation"
                                title="Installation"
                            >
                                How to install dependencies and structure your
                                app.
                            </ListItem>
                        </ul>
                    </NavigationMenuContent>
                </NavigationMenuItem>

                {/*<NavigationMenuItem className="hidden md:block">*/}
                {/*    <Link href={'#'}>Блог</Link>*/}
                {/*</NavigationMenuItem>*/}
            </NavigationMenuList>
        </NavigationMenu>
    );
}

function ListItem({
    title,
    children,
    href,
    ...props
}: React.ComponentPropsWithoutRef<'li'> & { href: string }) {
    return (
        <li {...props}>
            <NavigationMenuLink asChild>
                <Link href={href}>
                    <div className="text-sm leading-none font-medium">
                        {title}
                    </div>
                    <p className="line-clamp-2 text-sm leading-snug text-muted-foreground">
                        {children}
                    </p>
                </Link>
            </NavigationMenuLink>
        </li>
    );
}
