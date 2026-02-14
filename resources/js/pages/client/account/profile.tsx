import { Head, Link, router, usePage, useForm } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LayoutDashboard, ShoppingBag, MapPin, User, Mail, Phone, Save } from 'lucide-react';

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

function AccountSidebar({ activePath }: { activePath: string }) {
  const navItems = [
    { href: '/account', label: 'Дашборд', icon: LayoutDashboard },
    { href: '/account/orders', label: 'Заказы', icon: ShoppingBag },
    { href: '/account/addresses', label: 'Адреса', icon: MapPin },
    { href: '/account/profile', label: 'Профиль', icon: User },
  ];

  return (
    <aside className="w-full lg:w-64 space-y-2">
      <Card>
        <CardContent className="p-4">
          <nav className="space-y-1">
            {navItems.map((item) => {
              const Icon = item.icon;
              const isActive = activePath === item.href;

              return (
                <Link
                  key={item.href}
                  href={item.href}
                  className={`flex items-center gap-3 px-3 py-2 rounded-md transition-colors ${
                    isActive
                      ? 'bg-primary text-primary-foreground'
                      : 'hover:bg-muted'
                  }`}
                >
                  <Icon className="h-5 w-5" />
                  <span className="font-medium">{item.label}</span>
                </Link>
              );
            })}
          </nav>
        </CardContent>
      </Card>
    </aside>
  );
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
    <AppHeaderLayout>
      <Head title="Мой профиль" />

      <div className="container mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold mb-8">Мой профиль</h1>

        <div className="flex flex-col lg:flex-row gap-6">
          <AccountSidebar activePath="/account/profile" />

          <div className="flex-1">
            <Card>
              <CardHeader>
                <CardTitle>Личная информация</CardTitle>
              </CardHeader>
              <CardContent>
                <form onSubmit={handleSubmit} className="space-y-6">
                  <div className="space-y-2">
                    <Label htmlFor="name">
                      <User className="h-4 w-4 inline mr-2" />
                      Имя *
                    </Label>
                    <Input
                      id="name"
                      type="text"
                      value={data.name}
                      onChange={(e) => setData('name', e.target.value)}
                      placeholder="Введите ваше имя"
                      required
                    />
                    {errors.name && <p className="text-sm text-destructive">{errors.name}</p>}
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="email">
                      <Mail className="h-4 w-4 inline mr-2" />
                      Email
                    </Label>
                    <Input
                      id="email"
                      type="email"
                      value={auth.user.email}
                      disabled
                      className="bg-muted cursor-not-allowed"
                    />
                    <p className="text-sm text-muted-foreground">
                      Email нельзя изменить
                    </p>
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="phone">
                      <Phone className="h-4 w-4 inline mr-2" />
                      Телефон
                    </Label>
                    <Input
                      id="phone"
                      type="tel"
                      value={data.phone}
                      onChange={(e) => setData('phone', e.target.value)}
                      placeholder="+996 XXX XXX XXX"
                    />
                    {errors.phone && <p className="text-sm text-destructive">{errors.phone}</p>}
                  </div>

                  <div className="flex justify-end gap-2 pt-4">
                    <Button
                      type="submit"
                      disabled={processing || !isDirty}
                    >
                      <Save className="h-4 w-4 mr-2" />
                      {processing ? 'Сохранение...' : 'Сохранить изменения'}
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
                    <h3 className="font-medium mb-2">Пароль</h3>
                    <p className="text-sm text-muted-foreground mb-3">
                      Для изменения пароля используйте функцию восстановления пароля
                    </p>
                    <Link href="/forgot-password">
                      <Button variant="outline">Изменить пароль</Button>
                    </Link>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppHeaderLayout>
  );
}
