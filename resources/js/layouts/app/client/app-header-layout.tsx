import logo from '@/assets/images/logo.png';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
} from '@/components/ui/navigation-menu';
import { Separator } from '@/components/ui/separator';
import { Bell, Github, Plus, SearchIcon, User } from 'lucide-react';
import { ReactNode } from 'react';
import { NavMain } from '@/components/client/NavMain';
type AppHeaderLayoutProps = {
    children: ReactNode;
};
const AppHeaderLayout = ({ children }: AppHeaderLayoutProps) => {
    return (
        <div className="flex min-h-screen flex-col">
            {/* Header */}
            <header className="sticky top-0 z-50 mb-4 w-full border-b bg-background/90 px-[1.4rem] backdrop-blur supports-backdrop-blur:bg-background/90 md:px-[4rem] lg:px-[6rem] xl:px-[8rem] 2xl:px-[12rem]">
                <div className="flex h-14 items-center gap-4 px-4">
                    {/* Left: Logo / Brand */}
                    <div className="flex items-center gap-2 font-semibold">
                        <img src={logo} alt="Neutron" />
                    </div>
                    <Separator orientation="vertical" className="h-6" />
                    {/* Center: Navigation */}
                    <NavMain />

                    {/* Spacer */}
                    <div className="flex-1" />

                    {/* Right: Search */}
                    <div className="relative w-64">
                        <Input placeholder="Поиск..." className="h-8" />
                        <span className="absolute top-1/2 right-2 -translate-y-1/2 text-xs text-muted-foreground">
                            <SearchIcon className={'h-4 w-4'} />
                        </span>
                    </div>

                    {/* Icons */}
                    <Button variant="ghost" size="icon">
                        <Github className="h-4 w-4" />
                    </Button>

                    <Button variant="ghost" size="icon">
                        <Bell className="h-4 w-4" />
                    </Button>

                    {/* CTA */}
                    <Button size="sm" className="gap-1">
                        <User className="h-4 w-4" />
                        Login
                    </Button>
                </div>
            </header>

            {/* Content */}
            <main className="flex-1">{children}</main>
        </div>
    );
};

export default AppHeaderLayout;
