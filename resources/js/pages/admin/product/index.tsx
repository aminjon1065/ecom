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
import { Check, Pencil, X } from 'lucide-react';
import { useRef, useState } from 'react';
import { toast } from 'sonner';

/**
 * Inline-editable cell: click to edit, Enter/blur to save, Escape to cancel.
 */
function EditableCell({
    value,
    field,
    productId,
    type = 'text',
}: {
    value: string | number | null;
    field: 'price' | 'qty' | 'sku';
    productId: number;
    type?: 'text' | 'number';
}) {
    const [editing, setEditing] = useState(false);
    const [draft, setDraft] = useState(String(value ?? ''));
    const inputRef = useRef<HTMLInputElement>(null);

    function open() {
        setDraft(String(value ?? ''));
        setEditing(true);
        setTimeout(() => inputRef.current?.select(), 0);
    }

    function save() {
        const trimmed = draft.trim();
        if (trimmed === String(value ?? '')) {
            setEditing(false);
            return;
        }
        router.patch(
            product.updateField(productId).url,
            { field, value: trimmed },
            {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success('Сохранено');
                    setEditing(false);
                },
                onError: () => toast.error('Ошибка сохранения'),
            },
        );
    }

    function cancel() {
        setDraft(String(value ?? ''));
        setEditing(false);
    }

    if (!editing) {
        return (
            <button
                type="button"
                onClick={open}
                className="w-full rounded px-2 py-1 text-left hover:bg-muted transition-colors cursor-pointer"
            >
                {value ?? '—'}
            </button>
        );
    }

    return (
        <div className="flex items-center gap-1">
            <Input
                ref={inputRef}
                type={type}
                value={draft}
                onChange={(e) => setDraft(e.target.value)}
                onKeyDown={(e) => {
                    if (e.key === 'Enter') save();
                    if (e.key === 'Escape') cancel();
                }}
                onBlur={save}
                className="h-8 w-full min-w-[60px]"
                autoFocus
            />
            <Button
                size="icon"
                variant="ghost"
                className="h-7 w-7 shrink-0"
                onMouseDown={(e) => {
                    e.preventDefault();
                    save();
                }}
            >
                <Check className="h-3.5 w-3.5 text-green-600" />
            </Button>
            <Button
                size="icon"
                variant="ghost"
                className="h-7 w-7 shrink-0"
                onMouseDown={(e) => {
                    e.preventDefault();
                    cancel();
                }}
            >
                <X className="h-3.5 w-3.5 text-destructive" />
            </Button>
        </div>
    );
}

const columns: Column<Product>[] = [
    {
        key: 'thumb_image',
        label: 'Фото',
        className: 'w-[72px] text-center',
        render: (row) => {
            const imageSrc = row.thumb_image?.startsWith('http') || row.thumb_image?.startsWith('/')
                ? row.thumb_image
                : `/storage/${row.thumb_image}`;

            return (
                <div className="flex justify-center">
                    <img
                        src={imageSrc}
                        alt={row.name}
                        className="h-10 w-10 rounded object-cover border"
                    />
                </div>
            );
        },
    },
    {
        key: 'name',
        label: 'Название',
        render: (row) => (
            <div className="font-medium">{row.name}</div>
        ),
    },
    {
        key: 'price',
        label: 'Цена',
        className: 'w-[160px]',
        render: (row) => (
            <EditableCell
                value={row.price}
                field="price"
                productId={row.id}
                type="number"
            />
        ),
    },
    {
        key: 'qty',
        label: 'Кол-во',
        className: 'w-[130px]',
        render: (row) => (
            <EditableCell
                value={row.qty}
                field="qty"
                productId={row.id}
                type="number"
            />
        ),
    },
    {
        key: 'sku',
        label: 'SKU',
        className: 'w-[160px]',
        render: (row) => (
            <EditableCell
                value={row.sku}
                field="sku"
                productId={row.id}
            />
        ),
    },
    {
        key: 'status',
        label: 'Активен',
        className: 'w-[80px] text-center',
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
        key: 'id',
        label: '',
        className: 'w-[48px] text-center',
        render: (row) => (
            <Button
                size="icon"
                variant="ghost"
                className="h-8 w-8"
                onClick={() => router.visit(product.edit(row.id).url)}
                title="Редактировать"
            >
                <Pencil className="h-4 w-4" />
            </Button>
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
