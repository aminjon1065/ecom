import { AddSubCategoryModal } from '@/components/admin/sub-category/add-sub-category-modal';
import { DeleteSubCategoryDialog } from '@/components/admin/sub-category/delete-sub-category-dialog';
import { EditSubCategoryModal } from '@/components/admin/sub-category/edit-sub-category-modal';
import { SubCategoryStatusSwitch } from '@/components/admin/sub-category/sub-category-status-switch';
import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import subCategory from '@/routes/admin/sub-category';
import type { BreadcrumbItem } from '@/types';
import { Category } from '@/types/category';
import { PaginatedResponse } from '@/types/pagination';
import { SubCategory } from '@/types/sub-category';

interface Props {
    subCategories: PaginatedResponse<SubCategory>;
    categories: Category[];
}
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Дашборд',
        href: dashboard().url,
    },
    {
        title: 'Подкатегории',
        href: subCategory.index().url,
    },
];

const AllSubCategory = ({ subCategories, categories }: Props) => {
    const columns: Column<SubCategory>[] = [
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
            label: 'Категория',
            className: 'text-muted-foreground text-sm',
            render: (row) => <span>{row.category.name}</span>,
        },
        {
            key: 'status',
            label: 'Статус',
            render: (row) => (
                <SubCategoryStatusSwitch
                    subCategoryId={row.id}
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
                    <EditSubCategoryModal
                        subCategoryItem={row}
                        categories={categories}
                    />
                    <DeleteSubCategoryDialog
                        subCategoryId={row.id}
                        subCategoryName={row.name}
                    />
                </div>
            ),
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Подкатегории</h1>
                    <AddSubCategoryModal categories={categories} />
                </div>
                <DataTable data={subCategories.data} columns={columns} />
                <div className="flex justify-end gap-2">
                    <Pagination
                        currentPage={subCategories.current_page}
                        lastPage={subCategories.last_page}
                        path={subCategories.path}
                    />
                </div>
            </div>
        </AppLayout>
    );
};

export default AllSubCategory;
