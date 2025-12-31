import { AddChildCategoryModal } from '@/components/admin/child-category/add-child-category-modal';
import { ChildCategoryStatusSwitch } from '@/components/admin/child-category/child-category-status-switch';
import { DeleteChildCategoryDialog } from '@/components/admin/child-category/delete-child-category-dialog';
import { EditChildCategoryModal } from '@/components/admin/child-category/edit-child-category-modal';
import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import childCategory from '@/routes/admin/child-category';
import type { BreadcrumbItem } from '@/types';
import { Category } from '@/types/category';
import { ChildCategory } from '@/types/child-category';
import { PaginatedResponse } from '@/types/pagination';
import { SubCategory } from '@/types/sub-category';

interface Props {
    childCategories: PaginatedResponse<ChildCategory>;
    categories: Category[];
    subCategories: SubCategory[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Дашборд',
        href: dashboard().url,
    },
    {
        title: 'Дочерние категории',
        href: childCategory.index().url,
    },
];

const AllChildCategory = ({
    childCategories,
    categories,
    subCategories,
}: Props) => {
    const columns: Column<ChildCategory>[] = [
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
            key: 'category',
            label: 'Категория',
            render: (row) => <span>{row.category?.name}</span>,
        },
        {
            key: 'sub_category',
            label: 'Подкатегория',
            render: (row) => <span>{row.sub_category?.name}</span>,
        },
        {
            key: 'status',
            label: 'Статус',
            render: (row) => (
                <ChildCategoryStatusSwitch
                    childCategoryId={row.id}
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
                    <EditChildCategoryModal
                        childCategoryItem={row}
                        categories={categories}
                        subCategories={subCategories}
                    />
                    <DeleteChildCategoryDialog
                        childCategoryId={row.id}
                        childCategoryName={row.name}
                    />
                </div>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">
                        Дочерние категории
                    </h1>
                    <AddChildCategoryModal
                        categories={categories}
                        subCategories={subCategories}
                    />
                </div>

                <DataTable data={childCategories.data} columns={columns} />

                <div className="flex justify-end gap-2">
                    <Pagination
                        currentPage={childCategories.current_page}
                        lastPage={childCategories.last_page}
                        path={childCategories.path}
                    />
                </div>
            </div>
        </AppLayout>
    );
};

export default AllChildCategory;
