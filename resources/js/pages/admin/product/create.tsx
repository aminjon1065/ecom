import AppLayout from '@/layouts/app/admin/app-layout';
import { Brand } from '@/types/brand';
import { Category } from '@/types/category';
import { ChildCategory } from '@/types/child-category';
import { SubCategory } from '@/types/sub-category';
import { useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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

interface Props {
    categories: Category[];
    subCategories: SubCategory[];
    childCategories: ChildCategory[];
    brands: Brand[];
}

export default function CreateProduct({
    categories,
    subCategories,
    childCategories,
    brands,
}: Props) {
    const [thumbPreview, setThumbPreview] = useState<string | null>(null);
    const [galleryPreview, setGalleryPreview] = useState<string[]>([]);

    const { data, setData, post, processing } = useForm({
        name: '',
        code: '',
        slug: '',
        thumb_image: null as File | null,
        gallery: [] as File[],
        category_id: '',
        sub_category_id: '',
        child_category_id: '',
        brand_id: '',
        qty: 0,
        price: '',
        offer_price: '',
        offer_start_date: '',
        offer_end_date: '',
        short_description: '',
        long_description: '',
        video_link: '',
        first_source_link: '',
        second_source_link: '',
        seo_title: '',
        seo_description: '',
        status: true,
    });

    const filteredSub = useMemo(
        () =>
            subCategories.filter(
                (s) => s.category_id === Number(data.category_id),
            ),
        [data.category_id],
    );

    const filteredChild = useMemo(
        () =>
            childCategories.filter(
                (c) => c.sub_category_id === Number(data.sub_category_id),
            ),
        [data.sub_category_id],
    );

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(route('products.store'), {
            forceFormData: true,
        });
    }

    return (
        <AppLayout>
            <form onSubmit={submit} className="max-w-5xl space-y-8">
                {/* BASIC */}
                <Card>
                    <CardHeader>
                        <CardTitle>Основная информация</CardTitle>
                        <CardDescription>
                            Название, код и slug продукта
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-2">
                        <div className="space-y-2">
                            <Label>Название</Label>
                            <Input
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                            />
                        </div>

                        <div className="space-y-2">
                            <Label>Код</Label>
                            <Input
                                value={data.code}
                                onChange={(e) =>
                                    setData('code', e.target.value)
                                }
                            />
                        </div>

                        <div className="space-y-2 md:col-span-2">
                            <Label>Slug</Label>
                            <Input
                                placeholder="Оставь пустым — сгенерируется"
                                value={data.slug}
                                onChange={(e) =>
                                    setData('slug', e.target.value)
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
                            Основное изображение и галерея
                        </CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label>Основное изображение</Label>
                            <Input
                                type="file"
                                onChange={(e) => {
                                    const f = e.target.files?.[0];
                                    if (f) {
                                        setData('thumb_image', f);
                                        setThumbPreview(URL.createObjectURL(f));
                                    }
                                }}
                            />
                            {thumbPreview && (
                                <img
                                    src={thumbPreview}
                                    className="h-32 rounded border object-contain"
                                />
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label>Галерея</Label>
                            <Input
                                type="file"
                                multiple
                                onChange={(e) => {
                                    const files = Array.from(
                                        e.target.files || [],
                                    );
                                    setData('gallery', files);
                                    setGalleryPreview(
                                        files.map((f) =>
                                            URL.createObjectURL(f),
                                        ),
                                    );
                                }}
                            />
                            <div className="grid grid-cols-4 gap-2">
                                {galleryPreview.map((src, i) => (
                                    <img
                                        key={i}
                                        src={src}
                                        className="h-24 rounded border object-cover"
                                    />
                                ))}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* CATEGORY & BRAND */}
                <Card>
                    <CardHeader>
                        <CardTitle>Категории и бренд</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-4 md:grid-cols-2">
                        <Select
                            onValueChange={(v) => setData('category_id', v)}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Категория" />
                            </SelectTrigger>
                            <SelectContent>
                                {categories.map((c) => (
                                    <SelectItem key={c.id} value={String(c.id)}>
                                        {c.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Select
                            disabled={!data.category_id}
                            onValueChange={(v) => setData('sub_category_id', v)}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Подкатегория" />
                            </SelectTrigger>
                            <SelectContent>
                                {filteredSub.map((s) => (
                                    <SelectItem key={s.id} value={String(s.id)}>
                                        {s.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Select
                            disabled={!data.sub_category_id}
                            onValueChange={(v) =>
                                setData('child_category_id', v)
                            }
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Дочерняя категория" />
                            </SelectTrigger>
                            <SelectContent>
                                {filteredChild.map((c) => (
                                    <SelectItem key={c.id} value={String(c.id)}>
                                        {c.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Select onValueChange={(v) => setData('brand_id', v)}>
                            <SelectTrigger>
                                <SelectValue placeholder="Бренд" />
                            </SelectTrigger>
                            <SelectContent>
                                {brands.map((b) => (
                                    <SelectItem key={b.id} value={String(b.id)}>
                                        {b.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
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

                <Button size="lg" disabled={processing}>
                    Сохранить продукт
                </Button>
            </form>
        </AppLayout>
    );
}
