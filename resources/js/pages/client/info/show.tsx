import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Head, Link } from '@inertiajs/react';

interface Props {
    title: string;
    description: string;
    sections: {
        title: string;
        items: string[];
    }[];
}

export default function ClientInfoShow({
    title,
    description,
    sections,
}: Props) {
    return (
        <AppHeaderLayout>
            <Head title={title} />

            <div className="mx-auto max-w-4xl space-y-8 px-4 py-6 sm:px-6 sm:py-10 lg:px-8">
                <div className="space-y-3">
                    <Link
                        href="/"
                        className="text-sm text-muted-foreground hover:text-foreground"
                    >
                        На главную
                    </Link>
                    <div className="space-y-2">
                        <h1 className="text-3xl font-semibold tracking-tight">
                            {title}
                        </h1>
                        <p className="max-w-2xl text-sm leading-6 text-muted-foreground sm:text-base">
                            {description}
                        </p>
                    </div>
                </div>

                <div className="grid gap-4">
                    {sections.map((section) => (
                        <section
                            key={section.title}
                            className="rounded-xl border bg-background p-5 sm:p-6"
                        >
                            <h2 className="mb-3 text-lg font-medium">
                                {section.title}
                            </h2>
                            <ul className="space-y-2 text-sm leading-6 text-muted-foreground">
                                {section.items.map((item) => (
                                    <li key={item}>{item}</li>
                                ))}
                            </ul>
                        </section>
                    ))}
                </div>
            </div>
        </AppHeaderLayout>
    );
}
