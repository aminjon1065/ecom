import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app/admin/app-layout';
import product from '@/routes/admin/product';
import { PaginatedResponse } from '@/types/pagination';
import { Product } from '@/types/product';
import { router } from '@inertiajs/react';
import { toast } from 'sonner';

const columns: Column<Product>[] = [
    {
        key: 'code',
        label: 'Код',
        className: 'text-center',
    },
    {
        key: 'thumb_image',
        label: '',
        className: 'w-[70px]',
        render: (row) => (
            <img
                src={`${row.thumb_image}`}
                alt={row.name}
                className="h-12 w-12 rounded border object-cover"
            />
        ),
    },
    {
        key: 'name',
        label: 'Название',
        render: (row) => (
            <div className="space-y-1">
                <div className="font-medium">{row.name}</div>
                <div className="text-xs text-muted-foreground">{row.slug}</div>
            </div>
        ),
    },
    {
        key: 'price',
        label: 'Цена',
        render: (row) => (
            <div className="text-sm">
                {row.offer_price ? (
                    <>
                        <div className="text-muted-foreground line-through">
                            {row.price}
                        </div>
                        <div className="font-medium text-primary">
                            {row.offer_price}
                        </div>
                    </>
                ) : (
                    row.price
                )}
            </div>
        ),
    },
    {
        key: 'qty',
        label: 'Остаток',
        className: 'text-center',
    },
    {
        key: 'status',
        label: 'Активен',
        className: 'text-center',
        render: (row) => (
            <Switch
                checked={row.status}
                onCheckedChange={() =>
                    router.patch(
                        product.toggleStatus(row.id).url,
                        {},
                        {
                            preserveScroll: true,
                            onSuccess: () => toast.success('Статус обновлен'),
                        },
                    )
                }
            />
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
                    onClick={() => console.log(row.name)}
                >
                    Редактировать
                </Button>
            </div>
        ),
    },
];
interface Props {
    products: PaginatedResponse<Product>;
}
const Index = ({ products }: Props) => {
    return (
        <AppLayout>
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Продукты</h1>

                    <Button onClick={() => router.visit(product.create().url)}>
                        Добавить продукт
                    </Button>
                </div>
                <DataTable data={products.data} columns={columns} />
                <div className="flex justify-end">
                    <Pagination
                        currentPage={products.current_page}
                        lastPage={products.last_page}
                        path={products.path}
                    />
                </div>
            </div>
        </AppLayout>
    );
};

export default Index;
