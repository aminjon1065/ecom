// Pages/Admin/Brand/Index.tsx
import { AddBrandModal } from '@/components/admin/brand/add-brand-modal';
import { BrandFeatureSwitch } from '@/components/admin/brand/brand-feature-switch';
import { BrandStatusSwitch } from '@/components/admin/brand/brand-status-switch';
import { DeleteBrandDialog } from '@/components/admin/brand/delete-brand-dialog';
import { EditBrandModal } from '@/components/admin/brand/edit-brand-modal';
import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import brand from '@/routes/admin/brand';
import type { BreadcrumbItem } from '@/types';
import { Brand } from '@/types/brand';
import { PaginatedResponse } from '@/types/pagination';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Дашборд',
        href: dashboard().url,
    },
    {
        title: 'Категории',
        href: brand.index().url,
    },
];

interface Props {
    brands: PaginatedResponse<Brand>;
}

export default function BrandIndex({ brands }: Props) {
    const columns: Column<Brand>[] = [
        {
            key: 'logo',
            label: 'Логотип',
            render: (row) => (
                <img
                    src={`/storage/${row.logo}`}
                    className="h-10 w-20 object-contain"
                />
            ),
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
            key: 'is_featured',
            label: 'Показать',
            render: (row) => (
                <BrandFeatureSwitch
                    brandId={row.id}
                    initialStatus={row.is_featured}
                />
            ),
        },
        {
            key: 'status',
            label: 'Статус',
            className: 'text-right',
            render: (row) => (
                <BrandStatusSwitch
                    brandId={row.id}
                    initialStatus={row.status}
                />
            ),
        },
        {
            key: 'action',
            label: '',
            render: (row) => (
                <div className="flex justify-end gap-2">
                    <EditBrandModal brandItem={row} />
                    <DeleteBrandDialog brandId={row.id} brandName={row.name} />
                </div>
            ),
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Бренды</h1>
                    <AddBrandModal />
                </div>

                <DataTable data={brands.data} columns={columns} />

                <div className="flex justify-end">
                    <Pagination
                        currentPage={brands.current_page}
                        lastPage={brands.last_page}
                        path={brands.path}
                    />
                </div>
            </div>
        </AppLayout>
    );
}
