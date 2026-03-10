import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import { Head, useForm } from '@inertiajs/react';
import { LayoutGrid, Save } from 'lucide-react';

interface CategoryOption {
    id: number;
    name: string;
}

interface SectionTypeOption {
    value: string;
    label: string;
}

interface SectionFormItem {
    type: string;
    category_id: string;
}

interface StoredSection {
    id: number;
    position: number;
    type: string;
    category_id: number | null;
    category_name: string | null;
}

interface Props {
    sections: StoredSection[];
    categories: CategoryOption[];
    sectionTypes: SectionTypeOption[];
}

const breadcrumbs = [
    { title: 'Дашборд', href: dashboard().url },
    { title: 'Блоки главной', href: '/admin/home-page-sections' },
];

const CATEGORY_TYPE = 'category';

function buildInitialSections(sections: StoredSection[]): SectionFormItem[] {
    if (sections.length > 0) {
        return sections.map((section) => ({
            type: section.type,
            category_id: section.category_id ? String(section.category_id) : '',
        }));
    }

    return [{ type: CATEGORY_TYPE, category_id: '' }];
}

export default function HomePageSectionIndex({
    sections,
    categories,
    sectionTypes,
}: Props) {
    const { data, setData, put, processing, errors } = useForm<{
        sections: SectionFormItem[];
    }>({
        sections: buildInitialSections(sections),
    });

    const updateSection = (
        index: number,
        field: keyof SectionFormItem,
        value: string,
    ) => {
        setData(
            'sections',
            data.sections.map((section, sectionIndex) =>
                sectionIndex === index
                    ? {
                          ...section,
                          [field]: value,
                          ...(field === 'type' && value !== CATEGORY_TYPE
                              ? { category_id: '' }
                              : {}),
                      }
                    : section,
            ),
        );
    };

    const updateCount = (count: string) => {
        const parsedCount = Number(count);
        const nextSections = Array.from({ length: parsedCount }, (_, index) => {
            return (
                data.sections[index] ?? {
                    type: CATEGORY_TYPE,
                    category_id: '',
                }
            );
        });

        setData('sections', nextSections);
    };

    const submit = (event: React.FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        put('/admin/home-page-sections');
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Блоки главной страницы" />

            <form onSubmit={submit} className="mx-auto max-w-4xl space-y-6">
                <div className="rounded-xl border bg-background p-6">
                    <div className="flex items-start justify-between gap-4">
                        <div className="space-y-1">
                            <h1 className="text-xl font-semibold">
                                Блоки главной страницы
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Выберите от 1 до 4 блоков. Для каждого блока
                                можно указать категорию или готовый тип витрины
                                вроде акций и скидок.
                            </p>
                        </div>
                        <Button type="submit" disabled={processing}>
                            <Save className="mr-2 h-4 w-4" />
                            Сохранить
                        </Button>
                    </div>

                    <div className="mt-6 max-w-xs space-y-2">
                        <Label htmlFor="block-count">Количество блоков</Label>
                        <Select
                            value={String(data.sections.length)}
                            onValueChange={updateCount}
                        >
                            <SelectTrigger id="block-count">
                                <SelectValue placeholder="Выберите количество" />
                            </SelectTrigger>
                            <SelectContent>
                                {[1, 2, 3, 4].map((count) => (
                                    <SelectItem
                                        key={count}
                                        value={String(count)}
                                    >
                                        {count}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.sections && (
                            <p className="text-sm text-destructive">
                                {errors.sections}
                            </p>
                        )}
                    </div>
                </div>

                <div className="grid gap-4">
                    {data.sections.map((section, index) => (
                        <div
                            key={index}
                            className="rounded-xl border bg-background p-6"
                        >
                            <div className="mb-4 flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-muted">
                                    <LayoutGrid className="h-5 w-5" />
                                </div>
                                <div>
                                    <h2 className="font-medium">
                                        Блок {index + 1}
                                    </h2>
                                    <p className="text-sm text-muted-foreground">
                                        Этот блок появится на главной странице в
                                        выбранном порядке.
                                    </p>
                                </div>
                            </div>

                            <div className="grid gap-4 md:grid-cols-2">
                                <div className="space-y-2">
                                    <Label>Тип блока</Label>
                                    <Select
                                        value={section.type}
                                        onValueChange={(value) =>
                                            updateSection(index, 'type', value)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue placeholder="Выберите тип блока" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {sectionTypes.map((type) => (
                                                <SelectItem
                                                    key={type.value}
                                                    value={type.value}
                                                >
                                                    {type.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors[`sections.${index}.type`] && (
                                        <p className="text-sm text-destructive">
                                            {errors[`sections.${index}.type`]}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label>Категория</Label>
                                    <Select
                                        value={section.category_id || undefined}
                                        onValueChange={(value) =>
                                            updateSection(
                                                index,
                                                'category_id',
                                                value,
                                            )
                                        }
                                        disabled={section.type !== CATEGORY_TYPE}
                                    >
                                        <SelectTrigger>
                                            <SelectValue
                                                placeholder={
                                                    section.type === CATEGORY_TYPE
                                                        ? 'Выберите категорию'
                                                        : 'Нужно только для блока категории'
                                                }
                                            />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {categories.map((category) => (
                                                <SelectItem
                                                    key={category.id}
                                                    value={String(category.id)}
                                                >
                                                    {category.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    {errors[`sections.${index}.category_id`] && (
                                        <p className="text-sm text-destructive">
                                            {
                                                errors[
                                                    `sections.${index}.category_id`
                                                ]
                                            }
                                        </p>
                                    )}
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            </form>
        </AppLayout>
    );
}
