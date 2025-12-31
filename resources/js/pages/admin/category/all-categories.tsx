import { AddCategoryModal } from '@/components/admin/category/add-category-modal';
import { CategoryStatusSwitch } from '@/components/admin/category/category-status-switch';
import { DeleteCategoryDialog } from '@/components/admin/category/delete-category-dialog';
import { EditCategoryModal } from '@/components/admin/category/edit-category-modal';
import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import category from '@/routes/admin/category';
import type { BreadcrumbItem } from '@/types';
import { Category } from '@/types/category';
import { PaginatedResponse } from '@/types/pagination';
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
                <CategoryStatusSwitch
                    categoryId={row.id}
                    initialStatus={row.status}
                />
            ),
        },
        {
            key: 'actions',
            label: '',
            className: 'text-right',
            render: (row) => (
                <div className="flex justify-end gap-2">
                    <EditCategoryModal categoryItem={row} />
                    <DeleteCategoryDialog
                        categoryId={row.id}
                        categoryName={row.name}
                    />
                </div>
            ),
        },
    ];
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
