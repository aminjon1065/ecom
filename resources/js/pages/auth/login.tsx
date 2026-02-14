import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import AuthLayout from '@/layouts/auth-layout';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { telegram } from '@/routes/auth';
import { request } from '@/routes/password';
import { Form, Head, Link } from '@inertiajs/react';
import { Send } from 'lucide-react';

interface LoginProps {
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}

export default function Login({
    status,
    canResetPassword,
    canRegister,
}: LoginProps) {
    return (
        <AppHeaderLayout>
            <AuthLayout
                title="Вход в аккаунт"
                description="Введите email и пароль или войдите через Telegram"
            >
                <Head title="Вход" />

                {/* Telegram Login Button */}
                <div className="mb-6">
                    <Link
                        href={telegram.url()}
                        className="flex w-full items-center justify-center gap-2 rounded-md bg-[#2AABEE] px-4 py-2.5 text-sm font-medium text-white transition-colors hover:bg-[#229ED9]"
                    >
                        <Send className="h-4 w-4" />
                        Войти через Telegram
                    </Link>
                </div>

                {/* Divider */}
                <div className="relative mb-6">
                    <div className="absolute inset-0 flex items-center">
                        <span className="w-full border-t" />
                    </div>
                    <div className="relative flex justify-center text-xs uppercase">
                        <span className="bg-background px-2 text-muted-foreground">или</span>
                    </div>
                </div>

                <Form
                    {...store.form()}
                    resetOnSuccess={['password']}
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-6">
                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                        autoFocus
                                        tabIndex={1}
                                        autoComplete="email"
                                        placeholder="email@example.com"
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <div className="flex items-center">
                                        <Label htmlFor="password">
                                            Пароль
                                        </Label>
                                        {canResetPassword && (
                                            <TextLink
                                                href={request()}
                                                className="ml-auto text-sm"
                                                tabIndex={5}
                                            >
                                                Забыли пароль?
                                            </TextLink>
                                        )}
                                    </div>
                                    <Input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                        tabIndex={2}
                                        autoComplete="current-password"
                                        placeholder="Пароль"
                                    />
                                    <InputError message={errors.password} />
                                </div>

                                <div className="flex items-center space-x-3">
                                    <Checkbox
                                        id="remember"
                                        name="remember"
                                        tabIndex={3}
                                    />
                                    <Label htmlFor="remember">
                                        Запомнить меня
                                    </Label>
                                </div>

                                <Button
                                    type="submit"
                                    className="mt-4 w-full"
                                    tabIndex={4}
                                    disabled={processing}
                                    data-test="login-button"
                                >
                                    {processing && <Spinner />}
                                    Войти
                                </Button>
                            </div>

                            {canRegister && (
                                <div className="text-center text-sm text-muted-foreground">
                                    Нет аккаунта?{' '}
                                    <TextLink href={register()} tabIndex={6}>
                                        Регистрация
                                    </TextLink>
                                </div>
                            )}
                        </>
                    )}
                </Form>

                {status && (
                    <div className="mb-4 text-center text-sm font-medium text-green-600">
                        {status}
                    </div>
                )}
            </AuthLayout>
        </AppHeaderLayout>
    );
}
