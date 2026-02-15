import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppAccountLayout from '@/layouts/app/client/account/app-account-layout';
import { router, useForm } from '@inertiajs/react';
import { MapPin, Pencil, Plus, Trash2 } from 'lucide-react';
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

function AddressDialog({
    address,
    onClose,
}: {
    address?: Address;
    onClose: () => void;
}) {
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
                {errors.address && (
                    <p className="text-sm text-destructive">{errors.address}</p>
                )}
            </div>

            <div className="space-y-2">
                <Label htmlFor="description">Описание (необязательно)</Label>
                <Input
                    id="description"
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                    placeholder="Например: Дом, Офис, Квартира"
                />
                {errors.description && (
                    <p className="text-sm text-destructive">
                        {errors.description}
                    </p>
                )}
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
        <AppAccountLayout
            activePath={'/account/addresses'}
            title={'Мои адреса'}
        >
            <Card>
                <CardHeader className="flex flex-row items-center justify-between">
                    <CardTitle>Адреса доставки</CardTitle>
                    <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                        <DialogTrigger asChild>
                            <Button onClick={handleAddNew}>
                                <Plus className="mr-2 h-4 w-4" />
                                Добавить адрес
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    {editingAddress
                                        ? 'Редактировать адрес'
                                        : 'Добавить новый адрес'}
                                </DialogTitle>
                            </DialogHeader>
                            <AddressDialog
                                address={editingAddress}
                                onClose={handleCloseDialog}
                            />
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
                                                <div className="mb-2 flex items-center gap-2">
                                                    <MapPin className="h-5 w-5 text-muted-foreground" />
                                                    {address.description && (
                                                        <span className="font-semibold">
                                                            {
                                                                address.description
                                                            }
                                                        </span>
                                                    )}
                                                </div>
                                                <p className="text-muted-foreground">
                                                    {address.address}
                                                </p>
                                                <p className="mt-2 text-sm text-muted-foreground">
                                                    Добавлен:{' '}
                                                    {new Date(
                                                        address.created_at,
                                                    ).toLocaleDateString(
                                                        'ru-RU',
                                                        {
                                                            year: 'numeric',
                                                            month: 'long',
                                                            day: 'numeric',
                                                        },
                                                    )}
                                                </p>
                                            </div>
                                            <div className="flex gap-2">
                                                <Button
                                                    variant="outline"
                                                    size="icon"
                                                    onClick={() =>
                                                        handleEdit(address)
                                                    }
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="destructive"
                                                    size="icon"
                                                    onClick={() =>
                                                        handleDelete(address.id)
                                                    }
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
                        <div className="py-12 text-center">
                            <MapPin className="mx-auto mb-4 h-12 w-12 text-muted-foreground" />
                            <p className="mb-4 text-muted-foreground">
                                У вас пока нет сохраненных адресов
                            </p>
                            <Button onClick={handleAddNew}>
                                <Plus className="mr-2 h-4 w-4" />
                                Добавить первый адрес
                            </Button>
                        </div>
                    )}
                </CardContent>
            </Card>
        </AppAccountLayout>
    );
}
