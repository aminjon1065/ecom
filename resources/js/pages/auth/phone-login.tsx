import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import AuthLayout from '@/layouts/auth-layout';
import { Head, useForm } from '@inertiajs/react';

export default function PhoneLogin() {
    const phoneForm = useForm({
        phone: '',
    });

    const otpForm = useForm({
        phone: '',
        otp: '',
    });

    return (
        <AppHeaderLayout>
            <AuthLayout
                title="Вход по телефону"
                description="Получите код по SMS и подтвердите вход"
            >
                <Head title="Вход по телефону" />

                <form
                    className="space-y-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        phoneForm.post('/auth/phone/otp', {
                            onSuccess: () => {
                                otpForm.setData('phone', phoneForm.data.phone);
                            },
                        });
                    }}
                >
                    <div className="space-y-2">
                        <Label htmlFor="phone">Телефон</Label>
                        <Input
                            id="phone"
                            value={phoneForm.data.phone}
                            onChange={(event) =>
                                phoneForm.setData('phone', event.target.value)
                            }
                            placeholder="+992900000000"
                        />
                        <InputError message={phoneForm.errors.phone} />
                    </div>
                    <Button type="submit" disabled={phoneForm.processing}>
                        Отправить код
                    </Button>
                </form>

                <form
                    className="mt-6 space-y-4"
                    onSubmit={(event) => {
                        event.preventDefault();
                        otpForm.post('/auth/phone/verify');
                    }}
                >
                    <div className="space-y-2">
                        <Label htmlFor="otp-phone">Телефон</Label>
                        <Input
                            id="otp-phone"
                            value={otpForm.data.phone}
                            onChange={(event) =>
                                otpForm.setData('phone', event.target.value)
                            }
                            placeholder="+992900000000"
                        />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="otp">Код</Label>
                        <Input
                            id="otp"
                            value={otpForm.data.otp}
                            onChange={(event) =>
                                otpForm.setData('otp', event.target.value)
                            }
                            placeholder="123456"
                        />
                        <InputError message={otpForm.errors.otp} />
                    </div>
                    <Button type="submit" disabled={otpForm.processing}>
                        Подтвердить
                    </Button>
                </form>
            </AuthLayout>
        </AppHeaderLayout>
    );
}
