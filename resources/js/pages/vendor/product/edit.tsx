import { Editor } from '@/components/blocks/editor-00/editor';
import VendorLayout from '@/layouts/app/vendor/app-layout';
import { Brand } from '@/types/brand';
import { Category } from '@/types/category';
import { ChildCategory } from '@/types/child-category';
import { SubCategory } from '@/types/sub-category';
import { useForm, Head } from '@inertiajs/react';
import React, { useEffect, useMemo, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { type BreadcrumbItem } from '@/types';
import { initialValue } from '@/pages/editor-00';

interface ProductData {
    id: number;
    name: string;
    code: number;
    thumb_image: string;
    category_id: number;
    sub_category_id: number | null;
    child_category_id: number | null;
    brand_id: number;
    qty: number;
    sku: string | null;
    price: number;
    cost_price: number | null;
    offer_price: number | null;
    offer_start_date: string | null;
    offer_end_date: string | null;
    short_description: string;
    long_description: string;
    video_link: string | null;
    seo_title: string | null;
    seo_description: string | null;
    product_type: string | null;
}

interface Props {
    product: ProductData;
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

export default function VendorEditProduct({ product, categories, subCategories, childCategories, brands }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Дашборд', href: '/vendor' },
        { title: 'Товары', href: '/vendor/products' },
        { title: 'Редактирование', href: `/vendor/products/${product.id}/edit` },
    ];

    const [thumbPreview, setThumbPreview] = useState<string | null>(null);

    const parsedLongDesc = (() => {
        try { return JSON.parse(product.long_description); } catch { return initialValue; }
    })();

    const { data, setData, post, processing, errors } = useForm({
        _method: 'PUT',
        name: product.name,
        code: product.code,
        thumb_image: null as File | null,
        category_id: String(product.category_id),
        sub_category_id: product.sub_category_id ? String(product.sub_category_id) : '',
        child_category_id: product.child_category_id ? String(product.child_category_id) : '',
        brand_id: String(product.brand_id),
        qty: product.qty,
        sku: product.sku || '',
        price: product.price,
        cost_price: product.cost_price || 0,
        offer_price: product.offer_price || 0,
        offer_start_date: product.offer_start_date,
        offer_end_date: product.offer_end_date,
        short_description: product.short_description,
        long_description: product.long_description || JSON.stringify(initialValue),
        video_link: product.video_link || '',
        seo_title: product.seo_title || '',
        seo_description: product.seo_description || '',
        product_type: product.product_type || 'Новый',
    });

    useEffect(() => {
        return () => { if (thumbPreview) URL.revokeObjectURL(thumbPreview); };
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
        post(`/vendor/products/${product.id}`, { forceFormData: true });
    }

    return (
        <VendorLayout breadcrumbs={breadcrumbs}>
            <Head title={`Редактировать: ${product.name}`} />

            <form onSubmit={submit} className="mx-auto w-full max-w-5xl space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Основная информация</CardTitle>
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

                <Card>
                    <CardHeader><CardTitle>Категория и бренд</CardTitle></CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-5">
                        <div className="space-y-2">
                            <Label>Категория</Label>
                            <Select value={data.category_id} onValueChange={(v) => { setData('category_id', v); setData('sub_category_id', ''); setData('child_category_id', ''); }}>
                                <SelectTrigger className="w-full"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    {categories.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>Подкатегория</Label>
                            <Select value={data.sub_category_id} disabled={!data.category_id} onValueChange={(v) => { setData('sub_category_id', v); setData('child_category_id', ''); }}>
                                <SelectTrigger className="w-full"><SelectValue placeholder="Выберите" /></SelectTrigger>
                                <SelectContent>
                                    {filteredSub.map((s) => <SelectItem key={s.id} value={String(s.id)}>{s.name}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>Дочерняя</Label>
                            <Select value={data.child_category_id} disabled={!data.sub_category_id} onValueChange={(v) => setData('child_category_id', v)}>
                                <SelectTrigger className="w-full"><SelectValue placeholder="Выберите" /></SelectTrigger>
                                <SelectContent>
                                    {filteredChild.map((c) => <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>)}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>Бренд</Label>
                            <Select value={data.brand_id} onValueChange={(v) => setData('brand_id', v)}>
                                <SelectTrigger className="w-full"><SelectValue /></SelectTrigger>
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

                <Card>
                    <CardHeader><CardTitle>Цены</CardTitle></CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-3">
                        <div className="space-y-2">
                            <Label>Цена</Label>
                            <Input type="number" value={data.price} onChange={(e) => setData('price', Number(e.target.value))} />
                        </div>
                        <div className="space-y-2">
                            <Label>Себестоимость</Label>
                            <Input type="number" value={data.cost_price} onChange={(e) => setData('cost_price', Number(e.target.value))} />
                        </div>
                        <div className="space-y-2">
                            <Label>Цена со скидкой</Label>
                            <Input type="number" value={data.offer_price} onChange={(e) => setData('offer_price', Number(e.target.value))} />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>Описание</CardTitle></CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label>Короткое описание</Label>
                            <Input value={data.short_description} onChange={(e) => setData('short_description', e.target.value)} />
                        </div>
                        <div className="space-y-2">
                            <Label>Полное описание</Label>
                            <Editor
                                editorSerializedState={parsedLongDesc}
                                onSerializedChange={(value) => setData('long_description', JSON.stringify(value))}
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>SEO</CardTitle></CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label>SEO Заголовок</Label>
                            <Input value={data.seo_title} onChange={(e) => setData('seo_title', e.target.value)} />
                        </div>
                        <div className="space-y-2">
                            <Label>SEO Описание</Label>
                            <Textarea value={data.seo_description} onChange={(e) => setData('seo_description', e.target.value)} />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader><CardTitle>Изображение</CardTitle></CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label>Текущее изображение</Label>
                            <img src={product.thumb_image} className="h-24 rounded border object-contain" alt={product.name} />
                        </div>
                        <div className="space-y-2">
                            <Label>Новое изображение (необязательно)</Label>
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
                            {thumbPreview && <img src={thumbPreview} className="h-24 rounded border object-contain" alt="Preview" />}
                        </div>
                    </CardContent>
                </Card>

                <div className="flex justify-end pb-8">
                    <Button size="lg" disabled={processing} type="submit">
                        Сохранить изменения
                    </Button>
                </div>
            </form>
        </VendorLayout>
    );
}
