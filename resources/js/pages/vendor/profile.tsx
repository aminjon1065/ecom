import VendorLayout from '@/layouts/app/vendor/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import React, { useState, useEffect } from 'react';

interface Vendor {
    id: number;
    shop_name: string;
    banner: string | null;
    description: string | null;
    address: string | null;
    facebook_url: string | null;
    telegram_url: string | null;
    instagram_url: string | null;
    status: boolean;
}

interface Props {
    vendor: Vendor | null;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Дашборд', href: '/vendor' },
    { title: 'Профиль магазина', href: '/vendor/profile' },
];

export default function VendorProfile({ vendor }: Props) {
    const [bannerPreview, setBannerPreview] = useState<string | null>(null);

    const { data, setData, post, processing, errors } = useForm({
        _method: 'PUT',
        shop_name: vendor?.shop_name || '',
        description: vendor?.description || '',
        address: vendor?.address || '',
        banner: null as File | null,
        facebook_url: vendor?.facebook_url || '',
        telegram_url: vendor?.telegram_url || '',
        instagram_url: vendor?.instagram_url || '',
    });

    useEffect(() => {
        return () => { if (bannerPreview) URL.revokeObjectURL(bannerPreview); };
    }, [bannerPreview]);

    if (!vendor) {
        return (
            <VendorLayout breadcrumbs={breadcrumbs}>
                <Head title="Профиль магазина" />
                <div className="flex min-h-[400px] items-center justify-center">
                    <Card className="max-w-md">
                        <CardContent className="pt-6 text-center">
                            <h3 className="text-lg font-semibold">Профиль не найден</h3>
                            <p className="mt-2 text-sm text-muted-foreground">
                                Обратитесь к администратору для создания вашего профиля продавца.
                            </p>
                        </CardContent>
                    </Card>
                </div>
            </VendorLayout>
        );
    }

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post('/vendor/profile', { forceFormData: true });
    }

    return (
        <VendorLayout breadcrumbs={breadcrumbs}>
            <Head title="Профиль магазина" />

            <form onSubmit={submit} className="mx-auto max-w-3xl space-y-6">
                <div className="flex items-center justify-between">
                    <h2 className="text-2xl font-bold">Настройки магазина</h2>
                    <Badge variant={vendor.status ? 'default' : 'secondary'}>
                        {vendor.status ? 'Магазин активен' : 'Ожидает одобрения'}
                    </Badge>
                </div>

                {/* Banner */}
                <Card>
                    <CardHeader>
                        <CardTitle>Баннер магазина</CardTitle>
                        <CardDescription>Загрузите баннер для вашего магазина (рекомендуемый размер: 1200x300)</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        {(bannerPreview || vendor.banner) && (
                            <img
                                src={bannerPreview || `/storage/${vendor.banner}`}
                                alt="Баннер"
                                className="h-40 w-full rounded-lg border object-cover"
                            />
                        )}
                        <Input
                            type="file"
                            accept="image/*"
                            onChange={(e) => {
                                const f = e.target.files?.[0];
                                if (!f) return;
                                if (bannerPreview) URL.revokeObjectURL(bannerPreview);
                                setData('banner', f);
                                setBannerPreview(URL.createObjectURL(f));
                            }}
                        />
                        {errors.banner && <p className="text-xs text-destructive">{errors.banner}</p>}
                    </CardContent>
                </Card>

                {/* Shop Info */}
                <Card>
                    <CardHeader>
                        <CardTitle>Информация о магазине</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="shop_name">Название магазина</Label>
                            <Input
                                id="shop_name"
                                value={data.shop_name}
                                onChange={(e) => setData('shop_name', e.target.value)}
                            />
                            {errors.shop_name && <p className="text-xs text-destructive">{errors.shop_name}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="description">Описание</Label>
                            <Textarea
                                id="description"
                                rows={4}
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                placeholder="Расскажите о вашем магазине..."
                            />
                            {errors.description && <p className="text-xs text-destructive">{errors.description}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="address">Адрес</Label>
                            <Input
                                id="address"
                                value={data.address}
                                onChange={(e) => setData('address', e.target.value)}
                                placeholder="Город, улица, дом"
                            />
                            {errors.address && <p className="text-xs text-destructive">{errors.address}</p>}
                        </div>
                    </CardContent>
                </Card>

                {/* Social Links */}
                <Card>
                    <CardHeader>
                        <CardTitle>Социальные сети</CardTitle>
                        <CardDescription>Ссылки на ваши страницы в социальных сетях</CardDescription>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="facebook_url">Facebook</Label>
                            <Input
                                id="facebook_url"
                                type="url"
                                value={data.facebook_url}
                                onChange={(e) => setData('facebook_url', e.target.value)}
                                placeholder="https://facebook.com/..."
                            />
                            {errors.facebook_url && <p className="text-xs text-destructive">{errors.facebook_url}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="telegram_url">Telegram</Label>
                            <Input
                                id="telegram_url"
                                type="url"
                                value={data.telegram_url}
                                onChange={(e) => setData('telegram_url', e.target.value)}
                                placeholder="https://t.me/..."
                            />
                            {errors.telegram_url && <p className="text-xs text-destructive">{errors.telegram_url}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="instagram_url">Instagram</Label>
                            <Input
                                id="instagram_url"
                                type="url"
                                value={data.instagram_url}
                                onChange={(e) => setData('instagram_url', e.target.value)}
                                placeholder="https://instagram.com/..."
                            />
                            {errors.instagram_url && <p className="text-xs text-destructive">{errors.instagram_url}</p>}
                        </div>
                    </CardContent>
                </Card>

                <div className="flex justify-end pb-8">
                    <Button size="lg" disabled={processing} type="submit">
                        Сохранить настройки
                    </Button>
                </div>
            </form>
        </VendorLayout>
    );
}
