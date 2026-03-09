import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppAccountLayout from '@/layouts/app/client/account/app-account-layout';
import { SharedData } from '@/types';
import { useForm, usePage } from '@inertiajs/react';
import { CheckCircle, KeyRound, Lock, Mail, Phone, Save, User } from 'lucide-react';

interface ProfileProps {
    isSocialOnly: boolean;
}

export default function AccountProfile({ isSocialOnly }: ProfileProps) {
    const { auth } = usePage<SharedData>().props;

    const profileForm = useForm({
        name: auth.user.name,
        phone: auth.user.phone || '',
    });

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const handleProfileSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        profileForm.put('/account/profile', {
            preserveScroll: true,
        });
    };

    const handlePasswordSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        passwordForm.put('/account/password', {
            preserveScroll: true,
            onSuccess: () => {
                passwordForm.reset();
            },
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
                    <form onSubmit={handleProfileSubmit} className="space-y-6">
                        <div className="space-y-2">
                            <Label htmlFor="name">
                                <User className="mr-2 inline h-4 w-4" />
                                Имя *
                            </Label>
                            <Input
                                id="name"
                                type="text"
                                value={profileForm.data.name}
                                onChange={(e) =>
                                    profileForm.setData('name', e.target.value)
                                }
                                placeholder="Введите ваше имя"
                                required
                            />
                            {profileForm.errors.name && (
                                <p className="text-sm text-destructive">
                                    {profileForm.errors.name}
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
                                value={profileForm.data.phone}
                                onChange={(e) =>
                                    profileForm.setData('phone', e.target.value)
                                }
                                placeholder="+992 XXX XXX XXX"
                            />
                            {profileForm.errors.phone && (
                                <p className="text-sm text-destructive">
                                    {profileForm.errors.phone}
                                </p>
                            )}
                        </div>

                        <div className="flex justify-end gap-2 pt-4">
                            <Button
                                type="submit"
                                disabled={profileForm.processing || !profileForm.isDirty}
                            >
                                <Save className="mr-2 h-4 w-4" />
                                {profileForm.processing
                                    ? 'Сохранение...'
                                    : 'Сохранить изменения'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>

            <Card className="mt-6">
                <CardHeader>
                    <CardTitle>
                        <KeyRound className="mr-2 inline h-5 w-5" />
                        Изменить пароль
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={handlePasswordSubmit} className="space-y-6">
                        {!isSocialOnly && (
                            <div className="space-y-2">
                                <Label htmlFor="current_password">
                                    <Lock className="mr-2 inline h-4 w-4" />
                                    Текущий пароль *
                                </Label>
                                <Input
                                    id="current_password"
                                    type="password"
                                    value={passwordForm.data.current_password}
                                    onChange={(e) =>
                                        passwordForm.setData('current_password', e.target.value)
                                    }
                                    placeholder="Введите текущий пароль"
                                    autoComplete="current-password"
                                />
                                {passwordForm.errors.current_password && (
                                    <p className="text-sm text-destructive">
                                        {passwordForm.errors.current_password}
                                    </p>
                                )}
                            </div>
                        )}

                        <div className="space-y-2">
                            <Label htmlFor="password">
                                <Lock className="mr-2 inline h-4 w-4" />
                                Новый пароль *
                            </Label>
                            <Input
                                id="password"
                                type="password"
                                value={passwordForm.data.password}
                                onChange={(e) =>
                                    passwordForm.setData('password', e.target.value)
                                }
                                placeholder="Минимум 8 символов"
                                autoComplete="new-password"
                            />
                            {passwordForm.errors.password && (
                                <p className="text-sm text-destructive">
                                    {passwordForm.errors.password}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="password_confirmation">
                                <Lock className="mr-2 inline h-4 w-4" />
                                Подтвердите пароль *
                            </Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                value={passwordForm.data.password_confirmation}
                                onChange={(e) =>
                                    passwordForm.setData('password_confirmation', e.target.value)
                                }
                                placeholder="Повторите новый пароль"
                                autoComplete="new-password"
                            />
                        </div>

                        {passwordForm.recentlySuccessful && (
                            <div className="flex items-center gap-2 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700 dark:bg-green-950 dark:text-green-400">
                                <CheckCircle className="h-4 w-4" />
                                Пароль успешно изменён
                            </div>
                        )}

                        <div className="flex justify-end gap-2 pt-4">
                            <Button
                                type="submit"
                                disabled={passwordForm.processing}
                            >
                                <KeyRound className="mr-2 h-4 w-4" />
                                {passwordForm.processing
                                    ? 'Сохранение...'
                                    : 'Обновить пароль'}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </AppAccountLayout>
    );
}
