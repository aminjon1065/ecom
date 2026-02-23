import { Editor } from '@/components/blocks/editor-00/editor';
import AppLayout from '@/layouts/app/admin/app-layout';
import productRoutes from '@/routes/admin/product';
import { Brand } from '@/types/brand';
import { Category } from '@/types/category';
import { ChildCategory } from '@/types/child-category';
import { SubCategory } from '@/types/sub-category';
import { Head, useForm } from '@inertiajs/react';
import React, { useEffect, useMemo, useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { initialValue } from '@/pages/editor-00';

interface ProductFull {
    id: number;
    name: string;
    code: number;
    sku: string | null;
    thumb_image: string;
    category_id: number;
    sub_category_id: number | null;
    child_category_id: number | null;
    brand_id: number;
    qty: number;
    price: number;
    cost_price: number | null;
    offer_price: number | null;
    offer_start_date: string | null;
    offer_end_date: string | null;
    short_description: string | null;
    long_description: string;
    video_link: string | null;
    first_source_link: string | null;
    second_source_link: string | null;
    seo_title: string | null;
    seo_description: string | null;
    product_type: string | null;
    status: boolean;
    is_approved: boolean;
}

interface Props {
    product: ProductFull;
    categories: Category[];
    subCategories: SubCategory[];
    childCategories: ChildCategory[];
    brands: Brand[];
}

const productTypes = [
    { name: 'Топ' },
    { name: 'Рекомендуемый' },
    { name: 'Новый' },
    { name: 'Лучший' },
];

export default function AdminEditProduct({
    product,
    categories,
    subCategories,
    childCategories,
    brands,
}: Props) {
    const [thumbPreview, setThumbPreview] = useState<string | null>(null);

    const parsedLongDesc = useMemo(() => {
        try {
            return JSON.parse(product.long_description);
        } catch {
            return initialValue;
        }
    }, [product.long_description]);

    const { data, setData, post, processing, errors } = useForm({
        _method: 'PUT',
        name: product.name,
        code: product.code,
        sku: product.sku ?? '',
        thumb_image: null as File | null,
        gallery: [] as File[],
        category_id: String(product.category_id),
        sub_category_id: product.sub_category_id
            ? String(product.sub_category_id)
            : '',
        child_category_id: product.child_category_id
            ? String(product.child_category_id)
            : '',
        brand_id: String(product.brand_id),
        qty: product.qty,
        price: product.price,
        cost_price: product.cost_price ?? 0,
        offer_price: product.offer_price ?? 0,
        offer_start_date: product.offer_start_date,
        offer_end_date: product.offer_end_date,
        short_description: product.short_description ?? '',
        long_description: product.long_description || JSON.stringify(initialValue),
        video_link: product.video_link ?? '',
        first_source_link: product.first_source_link ?? '',
        second_source_link: product.second_source_link ?? '',
        seo_title: product.seo_title ?? '',
        seo_description: product.seo_description ?? '',
        product_type: product.product_type ?? 'Топ',
        status: product.status,
        is_approved: product.is_approved,
    });

    useEffect(() => {
        return () => {
            if (thumbPreview) URL.revokeObjectURL(thumbPreview);
        };
    }, [thumbPreview]);

    const filteredSub = useMemo(
        () =>
            subCategories.filter(
                (s) => s.category_id === Number(data.category_id),
            ),
        [data.category_id, subCategories],
    );

    const filteredChild = useMemo(
        () =>
            childCategories.filter(
                (c) => c.sub_category_id === Number(data.sub_category_id),
            ),
        [data.sub_category_id, childCategories],
    );

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(productRoutes.update(product.id).url, { forceFormData: true });
    }

    return (
        <AppLayout>
            <Head title={`Редактировать: ${product.name}`} />

            <form
                onSubmit={submit}
                className="mx-auto w-full max-w-10/12 space-y-8"
            >
                {/* BASIC */}
                <Card>
                    <CardHeader>
                        <CardTitle>Основная информация</CardTitle>
                        <CardDescription>
                            Название, код, кол-во и складской номер продукта
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="name">Название</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                            />
                            {errors.name && (
                                <p className="text-xs text-destructive">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div className="w-full justify-between space-x-2 md:flex">
                            <div className="w-full space-y-2">
                                <Label htmlFor="code">Код</Label>
                                <Input
                                    id="code"
                                    type="number"
                                    value={data.code}
                                    onChange={(e) =>
                                        setData('code', Number(e.target.value))
                                    }
                                />
                                {errors.code && (
                                    <p className="text-xs text-destructive">
                                        {errors.code}
                                    </p>
                                )}
                            </div>
                            <div className="w-full space-y-2">
                                <Label htmlFor="sku">Складской номер</Label>
                                <Input
                                    id="sku"
                                    value={data.sku}
                                    onChange={(e) =>
                                        setData('sku', e.target.value)
                                    }
                                />
                            </div>
                            <div className="w-full space-y-2">
                                <Label htmlFor="qty">
                                    Количество на складе
                                </Label>
                                <Input
                                    id="qty"
                                    type="number"
                                    value={data.qty}
                                    onChange={(e) =>
                                        setData('qty', Number(e.target.value))
                                    }
                                />
                                {errors.qty && (
                                    <p className="text-xs text-destructive">
                                        {errors.qty}
                                    </p>
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* CATEGORY & BRAND */}
                <Card>
                    <CardHeader>
                        <CardTitle>Категории и бренд</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-5">
                        <div className="space-y-2">
                            <Label>Категория</Label>
                            <Select
                                value={data.category_id}
                                onValueChange={(v) => {
                                    setData('category_id', v);
                                    setData('sub_category_id', '');
                                    setData('child_category_id', '');
                                }}
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Категория" />
                                </SelectTrigger>
                                <SelectContent>
                                    {categories.map((c) => (
                                        <SelectItem
                                            key={c.id}
                                            value={String(c.id)}
                                        >
                                            {c.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.category_id && (
                                <p className="text-xs text-destructive">
                                    {errors.category_id}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label>Подкатегория</Label>
                            <Select
                                value={data.sub_category_id}
                                disabled={!data.category_id}
                                onValueChange={(v) => {
                                    setData('sub_category_id', v);
                                    setData('child_category_id', '');
                                }}
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Подкатегория" />
                                </SelectTrigger>
                                <SelectContent>
                                    {filteredSub.map((s) => (
                                        <SelectItem
                                            key={s.id}
                                            value={String(s.id)}
                                        >
                                            {s.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <Label>Дочерняя категория</Label>
                            <Select
                                value={data.child_category_id}
                                disabled={!data.sub_category_id}
                                onValueChange={(v) =>
                                    setData('child_category_id', v)
                                }
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Дочерняя категория" />
                                </SelectTrigger>
                                <SelectContent>
                                    {filteredChild.map((c) => (
                                        <SelectItem
                                            key={c.id}
                                            value={String(c.id)}
                                        >
                                            {c.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>

                        <div className="space-y-2">
                            <Label>Бренд</Label>
                            <Select
                                value={data.brand_id}
                                onValueChange={(v) => setData('brand_id', v)}
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue placeholder="Бренд" />
                                </SelectTrigger>
                                <SelectContent>
                                    {brands.map((b) => (
                                        <SelectItem
                                            key={b.id}
                                            value={String(b.id)}
                                        >
                                            {b.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.brand_id && (
                                <p className="text-xs text-destructive">
                                    {errors.brand_id}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label>Тип продукта</Label>
                            <Select
                                value={data.product_type}
                                onValueChange={(v) =>
                                    setData('product_type', v)
                                }
                            >
                                <SelectTrigger className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {productTypes.map((t) => (
                                        <SelectItem
                                            key={t.name}
                                            value={t.name}
                                        >
                                            {t.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                {/* PRICES */}
                <Card>
                    <CardHeader>
                        <CardTitle>Цены</CardTitle>
                        <CardDescription>
                            Цена, себестоимость и скидки
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-5">
                        <div className="space-y-2">
                            <Label htmlFor="price">Цена</Label>
                            <Input
                                id="price"
                                type="number"
                                value={data.price}
                                onChange={(e) =>
                                    setData('price', Number(e.target.value))
                                }
                            />
                            {errors.price && (
                                <p className="text-xs text-destructive">
                                    {errors.price}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="cost_price">Себестоимость</Label>
                            <Input
                                id="cost_price"
                                type="number"
                                value={data.cost_price}
                                onChange={(e) =>
                                    setData(
                                        'cost_price',
                                        Number(e.target.value),
                                    )
                                }
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="offer_price">Скидка</Label>
                            <Input
                                id="offer_price"
                                type="number"
                                value={data.offer_price}
                                onChange={(e) =>
                                    setData(
                                        'offer_price',
                                        Number(e.target.value),
                                    )
                                }
                            />
                        </div>

                        <div className="flex flex-col gap-2">
                            <Label>Дата начала акции</Label>
                            <DatePicker
                                className="w-full"
                                value={data.offer_start_date}
                                onChange={(v) =>
                                    setData('offer_start_date', v)
                                }
                                placeholder="Начало"
                            />
                        </div>

                        <div className="flex flex-col gap-2">
                            <Label>Дата окончания акции</Label>
                            <DatePicker
                                className="w-full"
                                value={data.offer_end_date}
                                onChange={(v) => setData('offer_end_date', v)}
                                placeholder="Окончание"
                            />
                        </div>
                    </CardContent>
                </Card>

                {/* DESCRIPTIONS */}
                <Card>
                    <CardHeader>
                        <CardTitle>Описание продукта</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col items-center justify-between space-y-4">
                        <div className="w-full space-y-2">
                            <Label htmlFor="short_description">
                                Короткое описание
                            </Label>
                            <Input
                                id="short_description"
                                value={data.short_description}
                                onChange={(e) =>
                                    setData('short_description', e.target.value)
                                }
                            />
                        </div>
                        <div className="w-full">
                            <Label>Полное описание</Label>
                            <Editor
                                editorSerializedState={parsedLongDesc}
                                onSerializedChange={(value) =>
                                    setData(
                                        'long_description',
                                        JSON.stringify(value),
                                    )
                                }
                            />
                            {errors.long_description && (
                                <p className="text-xs text-destructive">
                                    {errors.long_description}
                                </p>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* SEO */}
                <Card>
                    <CardHeader>
                        <CardTitle>SEO (поисковая оптимизация)</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col items-center justify-between space-y-4">
                        <div className="w-full space-y-2">
                            <Label htmlFor="seo_title">SEO Заголовок</Label>
                            <Input
                                id="seo_title"
                                value={data.seo_title}
                                onChange={(e) =>
                                    setData('seo_title', e.target.value)
                                }
                            />
                        </div>
                        <div className="w-full space-y-2">
                            <Label htmlFor="seo_description">SEO Описание</Label>
                            <Textarea
                                id="seo_description"
                                value={data.seo_description}
                                onChange={(e) =>
                                    setData('seo_description', e.target.value)
                                }
                            />
                        </div>
                    </CardContent>
                </Card>

                {/* MEDIA */}
                <Card>
                    <CardHeader>
                        <CardTitle>Медиа</CardTitle>
                        <CardDescription>
                            Основное изображение продукта
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {/* Current image */}
                        <div className="space-y-2">
                            <Label>Текущее изображение</Label>
                            <img
                                src={`/storage/${product.thumb_image}`}
                                alt={product.name}
                                className="h-32 rounded border object-contain"
                            />
                        </div>

                        {/* Replace image */}
                        <div className="space-y-2">
                            <Label>Новое изображение (необязательно)</Label>
                            <Input
                                type="file"
                                accept="image/*"
                                onChange={(e) => {
                                    const f = e.target.files?.[0];
                                    if (!f) return;
                                    if (thumbPreview)
                                        URL.revokeObjectURL(thumbPreview);
                                    setData('thumb_image', f);
                                    setThumbPreview(URL.createObjectURL(f));
                                }}
                            />
                            {errors.thumb_image && (
                                <p className="text-xs text-destructive">
                                    {errors.thumb_image}
                                </p>
                            )}
                            {thumbPreview && (
                                <img
                                    src={thumbPreview}
                                    className="h-32 rounded border object-contain"
                                    alt="Preview"
                                />
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* STATUS, APPROVAL & SOURCES */}
                <Card>
                    <CardHeader>
                        <CardTitle>Статус, одобрение и источники</CardTitle>
                        <CardDescription>
                            Видимость в магазине и ссылки поставщиков
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="flex flex-col justify-between space-y-3">
                        <div className="flex items-center justify-between gap-2 rounded-md border p-3">
                            <Label htmlFor="status">Статус (активен)</Label>
                            <Switch
                                id="status"
                                checked={data.status}
                                onCheckedChange={(v) => setData('status', v)}
                            />
                        </div>

                        <div className="flex items-center justify-between gap-2 rounded-md border p-3">
                            <Label htmlFor="is_approved">Одобрен</Label>
                            <Switch
                                id="is_approved"
                                checked={data.is_approved}
                                onCheckedChange={(v) =>
                                    setData('is_approved', v)
                                }
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="video_link">Ссылка на видео</Label>
                            <Input
                                id="video_link"
                                value={data.video_link}
                                onChange={(e) =>
                                    setData('video_link', e.target.value)
                                }
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="first_source_link">
                                Первый источник
                            </Label>
                            <Input
                                id="first_source_link"
                                value={data.first_source_link}
                                onChange={(e) =>
                                    setData('first_source_link', e.target.value)
                                }
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="second_source_link">
                                Второй источник
                            </Label>
                            <Input
                                id="second_source_link"
                                value={data.second_source_link}
                                onChange={(e) =>
                                    setData(
                                        'second_source_link',
                                        e.target.value,
                                    )
                                }
                            />
                        </div>
                    </CardContent>
                </Card>

                <div className="flex items-center justify-between pb-8">
                    <Button
                        type="button"
                        variant="outline"
                        onClick={() => window.history.back()}
                    >
                        Назад
                    </Button>
                    <Button size="lg" disabled={processing} type="submit">
                        Сохранить изменения
                    </Button>
                </div>
            </form>
        </AppLayout>
    );
}
