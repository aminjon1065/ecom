import { useEffect, useRef } from 'react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import AuthLayout from '@/layouts/auth-layout';
import TextLink from '@/components/text-link';
import { login } from '@/routes';
import { callback } from '@/routes/auth/telegram';
import { Head, router, usePage } from '@inertiajs/react';
import { Send } from 'lucide-react';

interface TelegramLoginProps {
    telegramBotUsername: string;
}

interface TelegramUser {
    id: number;
    first_name: string;
    last_name?: string;
    username?: string;
    photo_url?: string;
    auth_date: number;
    hash: string;
}

export default function TelegramLogin({ telegramBotUsername }: TelegramLoginProps) {
    const telegramRef = useRef<HTMLDivElement>(null);
    const { errors } = usePage().props as any;

    useEffect(() => {
        // Define global callback for Telegram widget
        (window as any).onTelegramAuth = (user: TelegramUser) => {
            router.post(callback.url(), {
                id: user.id,
                first_name: user.first_name,
                last_name: user.last_name || '',
                username: user.username || '',
                photo_url: user.photo_url || '',
                auth_date: user.auth_date,
                hash: user.hash,
            });
        };

        // Load Telegram Login Widget script
        if (telegramRef.current) {
            const script = document.createElement('script');
            script.src = 'https://telegram.org/js/telegram-widget.js?22';
            script.setAttribute('data-telegram-login', telegramBotUsername);
            script.setAttribute('data-size', 'large');
            script.setAttribute('data-onauth', 'onTelegramAuth(user)');
            script.setAttribute('data-request-access', 'write');
            script.setAttribute('data-radius', '8');
            script.async = true;
            telegramRef.current.innerHTML = '';
            telegramRef.current.appendChild(script);
        }

        return () => {
            delete (window as any).onTelegramAuth;
        };
    }, [telegramBotUsername]);

    return (
        <AppHeaderLayout>
            <AuthLayout
                title="Вход через Telegram"
                description="Быстрый и безопасный вход через ваш аккаунт Telegram"
            >
                <Head title="Вход через Telegram" />

                <div className="flex flex-col items-center gap-6">
                    {/* Telegram icon */}
                    <div className="flex h-16 w-16 items-center justify-center rounded-full bg-[#2AABEE]/10">
                        <Send className="h-8 w-8 text-[#2AABEE]" />
                    </div>

                    {/* Instructions */}
                    <div className="text-center text-sm text-muted-foreground space-y-2">
                        <p>Нажмите кнопку ниже для авторизации через Telegram.</p>
                        <p>Мы получим только ваше имя и фото профиля.</p>
                    </div>

                    {/* Telegram Widget */}
                    <div ref={telegramRef} className="flex justify-center py-2" />

                    {/* Error display */}
                    {errors?.telegram && (
                        <div className="rounded-md bg-destructive/10 px-4 py-3 text-sm text-destructive">
                            {errors.telegram}
                        </div>
                    )}

                    {/* Divider */}
                    <div className="relative w-full">
                        <div className="absolute inset-0 flex items-center">
                            <span className="w-full border-t" />
                        </div>
                        <div className="relative flex justify-center text-xs uppercase">
                            <span className="bg-background px-2 text-muted-foreground">или</span>
                        </div>
                    </div>

                    {/* Back to email login */}
                    <div className="text-center text-sm text-muted-foreground">
                        <TextLink href={login()}>
                            Войти по email и паролю
                        </TextLink>
                    </div>
                </div>
            </AuthLayout>
        </AppHeaderLayout>
    );
}
