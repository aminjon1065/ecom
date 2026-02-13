import { Editor } from '@/components/blocks/editor-00/editor';
import VendorLayout from '@/layouts/app/vendor/app-layout';
import { Brand } from '@/types/brand';
import { Category } from '@/types/category';
import { ChildCategory } from '@/types/child-category';
import { SubCategory } from '@/types/sub-category';
import { useForm } from '@inertiajs/react';
import React, { useEffect, useMemo, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { initialValue } from '@/pages/editor-00';

interface Props {
    categories: Category[];
    subCategories: SubCategory[];
    childCategories: ChildCategory[];
    brands: Brand[];
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: '/vendor' },
    { title: 'Товары', href: '/vendor/products' },
    { title: 'Новый товар', href: '/vendor/products/create' },
];

const productTypes = [
    { name: 'Топ' },
    { name: 'Рекомендуемый' },
    { name: 'Новый' },
    { name: 'Лучший' },
];

export default function VendorCreateProduct({ categories, subCategories, childCategories, brands }: Props) {
    const [thumbPreview, setThumbPreview] = useState<string | null>(null);
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        code: 0,
        thumb_image: null as File | null,
        category_id: '',
        sub_category_id: '',
        child_category_id: '',
        brand_id: brands.length ? String(brands[0].id) : '',
        qty: 0,
        sku: '',
        price: 0,
        cost_price: 0,
        offer_price: 0,
        offer_start_date: null as string | null,
        offer_end_date: null as string | null,
        short_description: '',
        long_description: JSON.stringify(initialValue),
        video_link: '',
        seo_title: '',
        seo_description: '',
        product_type: 'Новый',
    });

    useEffect(() => {
        return () => {
            if (thumbPreview) URL.revokeObjectURL(thumbPreview);
        };
    }, [thumbPreview]);

    const filteredSub = useMemo(
        () => subCategories.filter((s) => s.category_id === Number(data.category_id)),
        [data.category_id, subCategories],
    );

    const filteredChild = useMemo(
        () => childCategories.filter((c) => c.sub_category_id === Number(data.sub_category_id)),
        [data.sub_category_id, childCategories],
    );

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post('/vendor/products', { forceFormData: true });
    }

    return (
        <VendorLayout breadcrumbs={breadcrumbs}>
            <Head title="Добавить товар" />

            <form onSubmit={submit} className="mx-auto w-full max-w-5xl space-y-6">
                {/* Basic Info */}
                <Card>
                    <CardHeader>
                        <CardTitle>Основная информация</CardTitle>
                        <CardDescription>Название, код, количество</CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-2">
                        <div className="space-y-2">
                            <Label htmlFor="name">Название</Label>
                            <Input id="name" value={data.name} onChange={(e) => setData('name', e.target.value)} />
                            {errors.name && <p className="text-xs text-destructive">{errors.name}</p>}
                        </div>
                        <div className="flex gap-3">
                            <div className="flex-1 space-y-2">
                                <Label htmlFor="code">Код</Label>
                                <Input id="code" type="number" value={data.code} onChange={(e) => setData('code', Number(e.target.value))} />
                            </div>
                            <div className="flex-1 space-y-2">
                                <Label htmlFor="sku">SKU</Label>
                                <Input id="sku" value={data.sku} onChange={(e) => setData('sku', e.target.value)} />
                            </div>
                            <div className="flex-1 space-y-2">
                                <Label htmlFor="qty">Кол-во</Label>
                                <Input id="qty" type="number" value={data.qty} onChange={(e) => setData('qty', Number(e.target.value))} />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Categories */}
                <Card>
                    <CardHeader>
                        <CardTitle>Категория и бренд</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-5">
                        <div className="space-y-2">
                            <Label>Категория</Label>
                            <Select onValueChange={(v) => { setData('category_id', v); setData('sub_category_id', ''); setData('child_category_id', ''); }}>
                                <SelectTrigger className="w-full"><SelectValue placeholder="Выберите" /></SelectTrigger>
                                <SelectContent>
                                    {categories.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>Подкатегория</Label>
                            <Select disabled={!data.category_id} onValueChange={(v) => { setData('sub_category_id', v); setData('child_category_id', ''); }}>
                                <SelectTrigger className="w-full"><SelectValue placeholder="Выберите" /></SelectTrigger>
                                <SelectContent>
                                    {filteredSub.map((s) => <SelectItem key={s.id} value={String(s.id)}>{s.name}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>Дочерняя</Label>
                            <Select disabled={!data.sub_category_id} onValueChange={(v) => setData('child_category_id', v)}>
                                <SelectTrigger className="w-full"><SelectValue placeholder="Выберите" /></SelectTrigger>
                                <SelectContent>
                                    {filteredChild.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>Бренд</Label>
                            <Select value={data.brand_id} onValueChange={(v) => setData('brand_id', v)}>
                                <SelectTrigger className="w-full"><SelectValue placeholder="Бренд" /></SelectTrigger>
                                <SelectContent>
                                    {brands.map((b) => <SelectItem key={b.id} value={String(b.id)}>{b.name}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>Тип</Label>
                            <Select value={data.product_type} onValueChange={(v) => setData('product_type', v)}>
                                <SelectTrigger className="w-full"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    {productTypes.map((t) => <SelectItem key={t.name} value={t.name}>{t.name}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                {/* Pricing */}
                <Card>
                    <CardHeader>
                        <CardTitle>Цены</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-3">
                        <div className="space-y-2">
                            <Label htmlFor="price">Цена</Label>
                            <Input id="price" type="number" value={data.price} onChange={(e) => setData('price', Number(e.target.value))} />
                            {errors.price && <p className="text-xs text-destructive">{errors.price}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="cost_price">Себестоимость</Label>
                            <Input id="cost_price" type="number" value={data.cost_price} onChange={(e) => setData('cost_price', Number(e.target.value))} />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="offer_price">Цена со скидкой</Label>
                            <Input id="offer_price" type="number" value={data.offer_price} onChange={(e) => setData('offer_price', Number(e.target.value))} />
                        </div>
                    </CardContent>
                </Card>

                {/* Description */}
                <Card>
                    <CardHeader>
                        <CardTitle>Описание</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="short_description">Короткое описание</Label>
                            <Input
                                id="short_description"
                                value={data.short_description}
                                onChange={(e) => setData('short_description', e.target.value)}
                            />
                            {errors.short_description && <p className="text-xs text-destructive">{errors.short_description}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label>Полное описание</Label>
                            <Editor
                                editorSerializedState={JSON.parse(data.long_description)}
                                onSerializedChange={(value) => setData('long_description', JSON.stringify(value))}
                            />
                        </div>
                    </CardContent>
                </Card>

                {/* SEO */}
                <Card>
                    <CardHeader>
                        <CardTitle>SEO</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="seo_title">SEO Заголовок</Label>
                            <Input id="seo_title" value={data.seo_title} onChange={(e) => setData('seo_title', e.target.value)} />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="seo_description">SEO Описание</Label>
                            <Textarea id="seo_description" value={data.seo_description} onChange={(e) => setData('seo_description', e.target.value)} />
                        </div>
                    </CardContent>
                </Card>

                {/* Image */}
                <Card>
                    <CardHeader>
                        <CardTitle>Изображение</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label>Основное изображение</Label>
                            <Input
                                type="file"
                                accept="image/*"
                                onChange={(e) => {
                                    const f = e.target.files?.[0];
                                    if (!f) return;
                                    if (thumbPreview) URL.revokeObjectURL(thumbPreview);
                                    setData('thumb_image', f);
                                    setThumbPreview(URL.createObjectURL(f));
                                }}
                            />
                            {errors.thumb_image && <p className="text-xs text-destructive">{errors.thumb_image}</p>}
                            {thumbPreview && (
                                <img src={thumbPreview} className="h-32 rounded border object-contain" alt="Preview" />
                            )}
                        </div>
                    </CardContent>
                </Card>

                <div className="flex justify-end pb-8">
                    <Button size="lg" disabled={processing} type="submit">
                        Создать товар
                    </Button>
                </div>
            </form>
        </VendorLayout>
    );
}
