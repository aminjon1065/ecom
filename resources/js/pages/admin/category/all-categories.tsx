import { AddCategoryModal } from '@/components/admin/add-category-modal';
import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import category from '@/routes/admin/category';
import type { BreadcrumbItem } from '@/types';
import { Category } from '@/types/category';
import { PaginatedResponse } from '@/types/pagination';
import { router } from '@inertiajs/react';
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Дашборд',
        href: dashboard().url,
    },
    {
        title: 'Категории',
        href: category.index().url,
    },
];

interface Props {
    categories: PaginatedResponse<Category>;
}

const AllCategories = ({ categories }: Props) => {
    const columns: Column<Category>[] = [
        {
            key: 'id',
            label: 'ID',
            className: 'w-[70px]',
        },
        {
            key: 'name',
            label: 'Название',
        },
        {
            key: 'slug',
            label: 'Slug',
            className: 'text-muted-foreground text-sm',
        },
        {
            key: 'icon',
            label: 'Иконка',
            render: (row) =>
                row.icon ? (
                    <img
                        src={`/storage/${row.icon}`}
                        alt={row.name}
                        className="h-10 w-10 rounded object-cover"
                    />
                ) : (
                    <span className="text-muted-foreground">—</span>
                ),
        },
        {
            key: 'status',
            label: 'Статус',
            render: (row) => (
                <Badge variant={row.status ? 'default' : 'secondary'}>
                    {row.status ? 'Активна' : 'Выключена'}
                </Badge>
            ),
        },
        {
            key: 'actions',
            label: '',
            className: 'text-right',
            render: (row) => (
                <div className="flex justify-end gap-2">
                    <Button
                        size="sm"
                        variant="outline"
                        onClick={() =>
                            router.visit(`/categories/${row.id}/edit`)
                        }
                    >
                        Редактировать
                    </Button>
                </div>
            ),
        },
    ];
    console.log(categories);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Категории</h1>
                    <AddCategoryModal />
                </div>
                <DataTable data={categories.data} columns={columns} />
                <div className="flex justify-end gap-2">
                    <Pagination
                        currentPage={categories.current_page}
                        lastPage={categories.last_page}
                        path={categories.path}
                    />
                </div>
            </div>
        </AppLayout>
    );
};

export default AllCategories;
