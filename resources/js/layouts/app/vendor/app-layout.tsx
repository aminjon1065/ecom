import VendorLayoutTemplate from '@/layouts/app/vendor/sidebar-layout';
import { type BreadcrumbItem } from '@/types';
import { type ReactNode } from 'react';

interface VendorLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}

export default ({ children, breadcrumbs, ...props }: VendorLayoutProps) => (
    <VendorLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
        {children}
    </VendorLayoutTemplate>
);
