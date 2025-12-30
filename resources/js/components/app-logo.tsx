import AppLogoIcon from './app-logo-icon';
import { Link } from '@inertiajs/react';

export default function AppLogo() {
    return (
        <>
            <div className="flex aspect-square size-8 items-center shadow justify-center rounded-md bg-sidebar-primary-foreground text-sidebar-primary-foreground">
                <AppLogoIcon className="size-5 fill-current bg-white" />
            </div>
            <div className="ml-1 grid flex-1 text-left text-sm">
                <span className="truncate leading-tight font-semibold">
                    Neutron
                </span>
            </div>
        </>
    );
}
