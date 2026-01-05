import { Column, DataTable } from '@/components/datatable';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import AppLayout from '@/layouts/app/admin/app-layout';
import product from '@/routes/admin/product';
import { Brand } from '@/types/brand';
import { Category } from '@/types/category';
import { PaginatedResponse } from '@/types/pagination';
import { Product } from '@/types/product';
import { router } from '@inertiajs/react';
import { useState } from 'react';
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
                src={`/storage${row.thumb_image}`}
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
    filters: {
        search?: string;
        category_id?: string;
        brand_id?: string;
        status?: string;
    };
    categories: Category[];
    brands: Brand[];
}

const Index = ({
    products,
    filters: initialFilters,
    categories,
    brands,
}: Props) => {
    const [filters, setFilters] = useState({
        search: initialFilters.search ?? '',
        category_id: initialFilters.category_id ?? 'all',
        brand_id: initialFilters.brand_id ?? 'all',
        status: initialFilters.status ?? 'all',
    });

    function applyFilters() {
        router.get(
            product.index().url,
            {
                search: filters.search || undefined,
                category_id:
                    filters.category_id === 'all'
                        ? undefined
                        : filters.category_id,
                brand_id:
                    filters.brand_id === 'all' ? undefined : filters.brand_id,
                status: filters.status === 'all' ? undefined : filters.status,
                page: 1,
            },
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    }

    return (
        <AppLayout>
            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h1 className="text-xl font-semibold">Продукты</h1>

                    <Button onClick={() => router.visit(product.create().url)}>
                        Добавить продукт
                    </Button>
                </div>
                <div className="rounded-lg border p-4">
                    <div className="grid gap-4 md:grid-cols-5">
                        {/* SEARCH */}
                        <Input
                            placeholder="Поиск: название, код, SKU"
                            value={filters.search}
                            onChange={(e) =>
                                setFilters((f) => ({
                                    ...f,
                                    search: e.target.value,
                                }))
                            }
                            onKeyDown={(e) => {
                                if (
                                    e.key === 'Enter' &&
                                    filters.search.trim()
                                ) {
                                    e.preventDefault();
                                    applyFilters();
                                }
                            }}
                        />

                        {/* CATEGORY */}
                        <Select
                            value={filters.category_id}
                            onValueChange={(v) =>
                                setFilters((f) => ({
                                    ...f,
                                    category_id: v,
                                }))
                            }
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Категория" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    Все категории
                                </SelectItem>
                                {categories.map((c) => (
                                    <SelectItem key={c.id} value={String(c.id)}>
                                        {c.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        {/* BRAND */}
                        <Select
                            value={filters.brand_id}
                            onValueChange={(v) =>
                                setFilters((f) => ({
                                    ...f,
                                    brand_id: v,
                                }))
                            }
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Бренд" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Все бренды</SelectItem>
                                {brands.map((b) => (
                                    <SelectItem key={b.id} value={String(b.id)}>
                                        {b.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        {/* STATUS */}
                        <Select
                            value={filters.status}
                            onValueChange={(v) =>
                                setFilters((f) => ({
                                    ...f,
                                    status: v,
                                }))
                            }
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Статус" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Статус</SelectItem>
                                <SelectItem value="1">Активные</SelectItem>
                                <SelectItem value="0">Неактивные</SelectItem>
                            </SelectContent>
                        </Select>

                        {/* ACTIONS */}
                        <div className="flex gap-2">
                            <Button onClick={applyFilters}>Применить</Button>

                            <Button
                                variant="outline"
                                onClick={() => {
                                    setFilters({
                                        search: '',
                                        category_id: '',
                                        brand_id: '',
                                        status: '',
                                    });

                                    router.get(product.index().url);
                                }}
                            >
                                Сбросить
                            </Button>
                        </div>
                    </div>
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
