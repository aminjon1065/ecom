import VendorLayout from '@/layouts/app/vendor/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Pagination } from '@/components/pagination';
import { Plus, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface Product {
    id: number;
    name: string;
    thumb_image: string;
    price: number;
    qty: number;
    status: boolean;
    is_approved: boolean;
    created_at: string;
    category: { id: number; name: string } | null;
    brand: { id: number; name: string } | null;
}

interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
    products: PaginatedProducts;
    filters: {
        search?: string;
        status?: string;
        is_approved?: string;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: '/vendor' },
    { title: 'Товары', href: '/vendor/products' },
];

function formatCurrency(value: number): string {
    return value.toLocaleString('ru-RU', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

export default function VendorProducts({ products, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    function applyFilters(newFilters: Record<string, string>) {
        router.get('/vendor/products', { ...filters, ...newFilters }, {
            preserveState: true,
            preserveScroll: true,
        });
    }

    function handleSearch(e: React.FormEvent) {
        e.preventDefault();
        applyFilters({ search });
    }

    function handleDelete(id: number) {
        if (confirm('Удалить товар?')) {
            router.delete(`/vendor/products/${id}`, { preserveScroll: true });
        }
    }

    return (
        <VendorLayout breadcrumbs={breadcrumbs}>
            <Head title="Мои товары" />

            <div className="space-y-4">
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Мои товары</h2>
                    <Button asChild>
                        <Link href="/vendor/products/create">
                            <Plus className="mr-2 h-4 w-4" />
                            Добавить товар
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <div className="flex flex-wrap items-center gap-3">
                            <form onSubmit={handleSearch} className="flex gap-2">
                                <Input
                                    placeholder="Поиск по названию..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="w-64"
                                />
                                <Button type="submit" variant="secondary">Найти</Button>
                            </form>
                            <Select
                                value={filters.is_approved ?? 'all'}
                                onValueChange={(v) => applyFilters({ is_approved: v === 'all' ? '' : v })}
                            >
                                <SelectTrigger className="w-40">
                                    <SelectValue placeholder="Статус одобрения" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Все</SelectItem>
                                    <SelectItem value="1">Одобрен</SelectItem>
                                    <SelectItem value="0">На модерации</SelectItem>
                                </SelectContent>
                            </Select>
                            <Select
                                value={filters.status ?? 'all'}
                                onValueChange={(v) => applyFilters({ status: v === 'all' ? '' : v })}
                            >
                                <SelectTrigger className="w-40">
                                    <SelectValue placeholder="Активность" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Все</SelectItem>
                                    <SelectItem value="1">Активен</SelectItem>
                                    <SelectItem value="0">Скрыт</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {products.data.length === 0 ? (
                            <p className="py-8 text-center text-sm text-muted-foreground">
                                Товары не найдены
                            </p>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm">
                                    <thead>
                                        <tr className="border-b">
                                            <th className="pb-3 text-left font-medium">Фото</th>
                                            <th className="pb-3 text-left font-medium">Название</th>
                                            <th className="pb-3 text-left font-medium">Категория</th>
                                            <th className="pb-3 text-left font-medium">Цена</th>
                                            <th className="pb-3 text-left font-medium">Остаток</th>
                                            <th className="pb-3 text-left font-medium">Одобрен</th>
                                            <th className="pb-3 text-left font-medium">Активен</th>
                                            <th className="pb-3 text-right font-medium">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {products.data.map((product) => (
                                            <tr key={product.id} className="border-b last:border-0">
                                                <td className="py-3">
                                                    <img
                                                        src={product.thumb_image}
                                                        alt={product.name}
                                                        className="h-10 w-10 rounded object-cover"
                                                    />
                                                </td>
                                                <td className="py-3">
                                                    <div className="max-w-[200px] truncate font-medium">
                                                        {product.name}
                                                    </div>
                                                </td>
                                                <td className="py-3 text-muted-foreground">
                                                    {product.category?.name ?? '—'}
                                                </td>
                                                <td className="py-3">
                                                    {formatCurrency(product.price)} сом.
                                                </td>
                                                <td className="py-3">
                                                    <span className={product.qty < 5 ? 'font-medium text-red-600' : ''}>
                                                        {product.qty}
                                                    </span>
                                                </td>
                                                <td className="py-3">
                                                    <Badge variant={product.is_approved ? 'default' : 'secondary'}>
                                                        {product.is_approved ? 'Да' : 'Нет'}
                                                    </Badge>
                                                </td>
                                                <td className="py-3">
                                                    <Switch
                                                        checked={product.status}
                                                        onCheckedChange={() => {
                                                            router.patch(`/vendor/products/${product.id}/status`, {}, { preserveScroll: true });
                                                        }}
                                                    />
                                                </td>
                                                <td className="py-3">
                                                    <div className="flex justify-end gap-1">
                                                        <Button size="icon" variant="ghost" asChild>
                                                            <Link href={`/vendor/products/${product.id}/edit`}>
                                                                <Pencil className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                        <Button
                                                            size="icon"
                                                            variant="ghost"
                                                            className="text-destructive"
                                                            onClick={() => handleDelete(product.id)}
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                        {products.last_page > 1 && (
                            <div className="mt-4">
                                <Pagination links={products.links} />
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </VendorLayout>
    );
}
