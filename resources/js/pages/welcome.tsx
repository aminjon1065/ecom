import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Head } from '@inertiajs/react';

export default function Welcome() {
    return (
        <AppHeaderLayout>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link
                    href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600"
                    rel="stylesheet"
                />
            </Head>
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]">
                <header className="mb-6 w-full max-w-83.75 text-sm not-has-[nav]:hidden lg:max-w-4xl">

                </header>

                <div className="hidden h-14.5 lg:block"></div>
            </div>
        </AppHeaderLayout>
    );
}
