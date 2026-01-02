import { Editor } from '@/components/blocks/editor-00/editor';
import AppLayout from '@/layouts/app/admin/app-layout';
import { Brand } from '@/types/brand';
import { Category } from '@/types/category';
import { ChildCategory } from '@/types/child-category';
import { SubCategory } from '@/types/sub-category';
import { useForm } from '@inertiajs/react';
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
import product from '@/routes/admin/product';

interface Props {
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

export default function CreateProduct({
    categories,
    subCategories,
    childCategories,
    brands,
}: Props) {
    const [thumbPreview, setThumbPreview] = useState<string | null>(null);
    const { data, setData, post, processing } = useForm({
        name: '',
        code: 0,
        thumb_image: null as File | null,
        gallery: [] as File[],
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
        first_source_link: '',
        second_source_link: '',
        seo_title: '',
        seo_description: '',
        is_approved: true,
        status: true,
        product_type: 'Топ',
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
        post(product.store().url, {
            forceFormData: true,
        });
    }

    return (
        <AppLayout>
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
                            <Label htmlFor={'name'}>Название</Label>
                            <Input
                                id={'name'}
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                            />
                        </div>

                        <div className="w-full justify-between space-x-2 md:flex">
                            <div className="w-full space-y-2">
                                <Label htmlFor={'code'}>Код</Label>
                                <Input
                                    id={'code'}
                                    value={data.code}
                                    onChange={(e) =>
                                        setData('code', Number(e.target.value))
                                    }
                                />
                            </div>
                            <div className="w-full space-y-2">
                                <Label htmlFor={'sku'}>Складской номер</Label>
                                <Input
                                    id={'sku'}
                                    value={data.sku}
                                    onChange={(e) =>
                                        setData('sku', e.target.value)
                                    }
                                />
                            </div>
                            <div className="w-full space-y-2">
                                <Label htmlFor={'qty'}>
                                    Количество на складе
                                </Label>
                                <Input
                                    id={'qty'}
                                    type={'number'}
                                    value={data.qty}
                                    onChange={(e) =>
                                        setData('qty', Number(e.target.value))
                                    }
                                />
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
                                onValueChange={(v) => {
                                    setData('category_id', v);
                                    setData('sub_category_id', '');
                                    setData('child_category_id', '');
                                }}
                            >
                                <SelectTrigger className={'w-full'}>
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
                        </div>
                        <div className="space-y-2">
                            <Label>Подкатегория</Label>
                            <Select
                                disabled={!data.category_id}
                                onValueChange={(v) => {
                                    setData('sub_category_id', v);
                                    setData('child_category_id', '');
                                }}
                            >
                                <SelectTrigger className={'w-full'}>
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
                                disabled={!data.sub_category_id}
                                onValueChange={(v) =>
                                    setData('child_category_id', v)
                                }
                            >
                                <SelectTrigger className={'w-full'}>
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

                        <div className="w-full space-y-2">
                            <Label>Бренд</Label>
                            <Select
                                value={data.brand_id}
                                onValueChange={(v) => setData('brand_id', v)}
                            >
                                <SelectTrigger className={'w-full'}>
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
                        </div>
                        <div className="w-full space-y-2">
                            <Label>Тип продукта</Label>
                            <Select
                                value={data.product_type}
                                onValueChange={(v) =>
                                    setData('product_type', v)
                                }
                            >
                                <SelectTrigger className={'w-full'}>
                                    <SelectValue placeholder="Топ" />
                                </SelectTrigger>
                                <SelectContent>
                                    {productTypes.map((b) => (
                                        <SelectItem
                                            key={b.name}
                                            value={String(b.name)}
                                        >
                                            {b.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                {/* PRICE & COST & OFFER PRICE*/}
                <Card>
                    <CardHeader>
                        <CardTitle>Цены</CardTitle>
                        <CardDescription>
                            Цена, себестоимость и скидки
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-5">
                        <div className="space-y-2">
                            <Label htmlFor={'price'}>Цена</Label>
                            <Input
                                id={'price'}
                                type={'number'}
                                value={data.price}
                                onChange={(e) =>
                                    setData('price', Number(e.target.value))
                                }
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor={'cost_price'}>Себестоимость</Label>
                            <Input
                                type={'number'}
                                id={'cost_price'}
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
                            <Label htmlFor={'offer_price'}>Скидка</Label>
                            <Input
                                type={'number'}
                                id={'offer_price'}
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
                                className={'w-full'}
                                value={data.offer_start_date}
                                onChange={(v) => setData('offer_start_date', v)}
                                placeholder="Начало"
                            />
                        </div>

                        <div className="flex flex-col gap-2">
                            <Label>Дата окончания акции</Label>
                            <DatePicker
                                className={'w-full'}
                                value={data.offer_end_date}
                                onChange={(v) => setData('offer_end_date', v)}
                                placeholder="Окончание"
                            />
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Описание продукта</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col items-center justify-between space-y-4">
                        <div className="w-full space-y-2">
                            <Label htmlFor={'short_description'}>
                                Короткое описание
                            </Label>
                            <Input
                                id={'short_description'}
                                value={data.short_description}
                                onChange={(e) =>
                                    setData('short_description', e.target.value)
                                }
                            />
                        </div>
                        <div className="w-full">
                            <Label htmlFor={'long_description'}>
                                Полное описание
                            </Label>
                            <Editor
                                editorSerializedState={JSON.parse(
                                    data.long_description,
                                )}
                                onSerializedChange={(value) =>
                                    setData(
                                        'long_description',
                                        JSON.stringify(value),
                                    )
                                }
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>
                            SEO(Search Engine Optimization) - для поисковой
                            оптимизации
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-col items-center justify-between space-y-4">
                        <div className="w-full space-y-2">
                            <Label htmlFor={'seo_title'}>SEO Заголовок</Label>
                            <Input
                                id={'seo_title'}
                                value={data.seo_title}
                                onChange={(e) =>
                                    setData('seo_title', e.target.value)
                                }
                            />
                        </div>
                        <div className="w-full">
                            <Label htmlFor={'seo_description'}>
                                Полное описание
                            </Label>
                            <Textarea
                                id={'seo_description'}
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
                            Основное изображение продукта(thumb image)
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label>Основное изображение</Label>
                            <Input
                                type="file"
                                accept={'image/*'}
                                onChange={(e) => {
                                    const f = e.target.files?.[0];
                                    if (!f) return;

                                    if (thumbPreview) {
                                        URL.revokeObjectURL(thumbPreview);
                                    }

                                    const previewUrl = URL.createObjectURL(f);

                                    setData('thumb_image', f);
                                    setThumbPreview(previewUrl);
                                }}
                            />
                            {thumbPreview && (
                                <img
                                    src={thumbPreview}
                                    className="h-32 rounded border object-contain"
                                    alt={'Product img'}
                                />
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* STATUS */}
                <Card>
                    <CardContent className="flex items-center justify-between">
                        <div>
                            <CardTitle>Статус</CardTitle>
                            <CardDescription>
                                Показывать продукт в магазине
                            </CardDescription>
                        </div>
                        <Switch
                            checked={data.status}
                            onCheckedChange={(v) => setData('status', v)}
                        />
                    </CardContent>
                </Card>

                <div className="flex justify-end">
                    <Button size="lg" disabled={processing} type={'submit'}>
                        Сохранить продукт
                    </Button>
                </div>
            </form>
        </AppLayout>
    );
}
