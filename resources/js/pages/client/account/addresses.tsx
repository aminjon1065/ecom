import { Head, Link, router, useForm } from '@inertiajs/react';
import AppHeaderLayout from '@/layouts/app/client/app-header-layout';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { LayoutDashboard, ShoppingBag, MapPin, User, Plus, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface Address {
  id: number;
  address: string;
  description?: string | null;
  created_at: string;
}

interface Props {
  addresses: Address[];
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

function AddressDialog({ address, onClose }: { address?: Address; onClose: () => void }) {
  const { data, setData, post, put, processing, errors, reset } = useForm({
    address: address?.address || '',
    description: address?.description || '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();

    if (address) {
      put(`/account/addresses/${address.id}`, {
        onSuccess: () => {
          reset();
          onClose();
        },
      });
    } else {
      post('/account/addresses', {
        onSuccess: () => {
          reset();
          onClose();
        },
      });
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <div className="space-y-2">
        <Label htmlFor="address">Адрес *</Label>
        <Textarea
          id="address"
          value={data.address}
          onChange={(e) => setData('address', e.target.value)}
          placeholder="Введите ваш адрес"
          rows={3}
          required
        />
        {errors.address && <p className="text-sm text-destructive">{errors.address}</p>}
      </div>

      <div className="space-y-2">
        <Label htmlFor="description">Описание (необязательно)</Label>
        <Input
          id="description"
          value={data.description}
          onChange={(e) => setData('description', e.target.value)}
          placeholder="Например: Дом, Офис, Квартира"
        />
        {errors.description && <p className="text-sm text-destructive">{errors.description}</p>}
      </div>

      <div className="flex justify-end gap-2 pt-4">
        <Button type="button" variant="outline" onClick={onClose}>
          Отмена
        </Button>
        <Button type="submit" disabled={processing}>
          {address ? 'Обновить' : 'Добавить'}
        </Button>
      </div>
    </form>
  );
}

export default function AccountAddresses({ addresses }: Props) {
  const [dialogOpen, setDialogOpen] = useState(false);
  const [editingAddress, setEditingAddress] = useState<Address | undefined>();

  const handleDelete = (addressId: number) => {
    if (confirm('Вы уверены, что хотите удалить этот адрес?')) {
      router.delete(`/account/addresses/${addressId}`, {
        preserveScroll: true,
      });
    }
  };

  const handleEdit = (address: Address) => {
    setEditingAddress(address);
    setDialogOpen(true);
  };

  const handleAddNew = () => {
    setEditingAddress(undefined);
    setDialogOpen(true);
  };

  const handleCloseDialog = () => {
    setDialogOpen(false);
    setEditingAddress(undefined);
  };

  return (
    <AppHeaderLayout>
      <Head title="Мои адреса" />

      <div className="container mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold mb-8">Мои адреса</h1>

        <div className="flex flex-col lg:flex-row gap-6">
          <AccountSidebar activePath="/account/addresses" />

          <div className="flex-1">
            <Card>
              <CardHeader className="flex flex-row items-center justify-between">
                <CardTitle>Адреса доставки</CardTitle>
                <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                  <DialogTrigger asChild>
                    <Button onClick={handleAddNew}>
                      <Plus className="h-4 w-4 mr-2" />
                      Добавить адрес
                    </Button>
                  </DialogTrigger>
                  <DialogContent>
                    <DialogHeader>
                      <DialogTitle>
                        {editingAddress ? 'Редактировать адрес' : 'Добавить новый адрес'}
                      </DialogTitle>
                    </DialogHeader>
                    <AddressDialog address={editingAddress} onClose={handleCloseDialog} />
                  </DialogContent>
                </Dialog>
              </CardHeader>
              <CardContent>
                {addresses.length > 0 ? (
                  <div className="space-y-4">
                    {addresses.map((address) => (
                      <Card key={address.id}>
                        <CardContent className="p-4">
                          <div className="flex items-start justify-between gap-4">
                            <div className="flex-1">
                              <div className="flex items-center gap-2 mb-2">
                                <MapPin className="h-5 w-5 text-muted-foreground" />
                                {address.description && (
                                  <span className="font-semibold">{address.description}</span>
                                )}
                              </div>
                              <p className="text-muted-foreground">{address.address}</p>
                              <p className="text-sm text-muted-foreground mt-2">
                                Добавлен:{' '}
                                {new Date(address.created_at).toLocaleDateString('ru-RU', {
                                  year: 'numeric',
                                  month: 'long',
                                  day: 'numeric',
                                })}
                              </p>
                            </div>
                            <div className="flex gap-2">
                              <Button
                                variant="outline"
                                size="icon"
                                onClick={() => handleEdit(address)}
                              >
                                <Pencil className="h-4 w-4" />
                              </Button>
                              <Button
                                variant="destructive"
                                size="icon"
                                onClick={() => handleDelete(address.id)}
                              >
                                <Trash2 className="h-4 w-4" />
                              </Button>
                            </div>
                          </div>
                        </CardContent>
                      </Card>
                    ))}
                  </div>
                ) : (
                  <div className="text-center py-12">
                    <MapPin className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                    <p className="text-muted-foreground mb-4">У вас пока нет сохраненных адресов</p>
                    <Button onClick={handleAddNew}>
                      <Plus className="h-4 w-4 mr-2" />
                      Добавить первый адрес
                    </Button>
                  </div>
                )}
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </AppHeaderLayout>
  );
}
