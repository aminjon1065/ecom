import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import AuthLayout from '@/layouts/auth-layout';
import { store } from '@/routes/auth/google/phone';
import { Form, Head } from '@inertiajs/react';

interface GooglePhoneProps {
    googleUser: {
        id: string;
        name: string;
        email: string;
        avatar: string | null;
    };
}

export default function GooglePhone({ googleUser }: GooglePhoneProps) {
    return (
        <AppHeaderLayout>
            <AuthLayout
                title="Введите номер телефона"
                description={`Почти готово, ${googleUser.name}! Укажите ваш номер телефона для завершения регистрации.`}
            >
                <Head title="Номер телефона" />

                <Form
                    {...store.form()}
                    className="flex flex-col gap-6"
                >
                    {({ processing, errors }) => (
                        <>
                            <div className="grid gap-6">
                                <div className="grid gap-2">
                                    <Label htmlFor="phone">Номер телефона</Label>
                                    <Input
                                        id="phone"
                                        type="tel"
                                        name="phone"
                                        required
                                        autoFocus
                                        autoComplete="tel"
                                        placeholder="+992 900 00 00 00"
                                    />
                                    <InputError message={errors.phone} />
                                </div>

                                <Button
                                    type="submit"
                                    className="mt-2 w-full"
                                    disabled={processing}
                                >
                                    {processing && <Spinner />}
                                    Завершить регистрацию
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </AuthLayout>
        </AppHeaderLayout>
    );
}
