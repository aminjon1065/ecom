import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppAccountLayout from '@/layouts/app/client/account/app-account-layout';
import { Link, useForm, usePage } from '@inertiajs/react';
import { Mail, Phone, Save, User } from 'lucide-react';

interface AuthUser {
    id: number;
    name: string;
    email: string;
    phone?: string;
}

interface PageProps {
    auth: {
        user: AuthUser;
    };
}

export default function AccountProfile() {
    const { auth } = usePage<PageProps>().props;
    const { data, setData, put, processing, errors, isDirty } = useForm({
        name: auth.user.name,
        phone: auth.user.phone || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put('/account/profile', {
            preserveScroll: true,
        });
    };

    return (
        <AppAccountLayout
            activePath={'/account/profile'}
            title={'Мой профиль'}
        >
            <Card>
                <CardHeader>
                    <CardTitle>Личная информация</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div className="space-y-2">
                            <Label htmlFor="name">
                                <User className="mr-2 inline h-4 w-4" />
                                Имя *
                            </Label>
                            <Input
                                id="name"
                                type="text"
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                placeholder="Введите ваше имя"
                                required
                            />
                            {errors.name && (
                                <p className="text-sm text-destructive">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="email">
                                <Mail className="mr-2 inline h-4 w-4" />
                                Email
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                value={auth.user.email}
                                disabled
                                className="cursor-not-allowed bg-muted"
                            />
                            <p className="text-sm text-muted-foreground">
                                Email нельзя изменить
                            </p>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="phone">
                                <Phone className="mr-2 inline h-4 w-4" />
                                Телефон
                            </Label>
                            <Input
                                id="phone"
                                type="tel"
                                value={data.phone}
                                onChange={(e) =>
                                    setData('phone', e.target.value)
                                }
                                placeholder="+992 XXX XXX XXX"
                            />
                            {errors.phone && (
                                <p className="text-sm text-destructive">
                                    {errors.phone}
                                </p>
                            )}
                        </div>

                        <div className="flex justify-end gap-2 pt-4">
                            <Button
                                type="submit"
                                disabled={processing || !isDirty}
                            >
                                <Save className="mr-2 h-4 w-4" />
                                {processing
                                    ? 'Сохранение...'
                                    : 'Сохранить изменения'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <Card className="mt-6">
                <CardHeader>
                    <CardTitle>Безопасность</CardTitle>
                </CardHeader>
                <CardContent>
                    <div className="space-y-4">
                        <div>
                            <h3 className="mb-2 font-medium">Пароль</h3>
                            <p className="mb-3 text-sm text-muted-foreground">
                                Для изменения пароля используйте функцию
                                восстановления пароля
                            </p>
                            <Link href="/forgot-password">
                                <Button variant="outline">
                                    Изменить пароль
                                </Button>
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </AppAccountLayout>
    );
}
