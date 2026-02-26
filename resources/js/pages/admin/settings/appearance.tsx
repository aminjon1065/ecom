import { Head } from '@inertiajs/react';

import AppearanceTabs from '@/components/appearance-tabs';
import HeadingSmall from '@/components/heading-small';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/app/admin/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { edit as editAppearance } from '@/routes/admin/appearance';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Настройки внешнего вида',
        href: editAppearance().url,
    },
];

export default function Appearance() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Настройки внешнего вида" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall
                        title="Настройки внешнего вида"
                        description="Измените настройки внешнего вида аккаунта"
                    />
                    <AppearanceTabs />
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
