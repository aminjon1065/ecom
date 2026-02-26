import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import brand from '@/routes/admin/brand';
import { Brand as BrandType } from '@/types/brand';
import { useForm } from '@inertiajs/react';
import { SquarePen } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Props {
    brandItem: BrandType;
}

export function EditBrandModal({ brandItem }: Props) {
    const [open, setOpen] = useState(false);
    const [preview, setPreview] = useState<string | null>(
        brandItem.logo ? `/storage/${brandItem.logo}` : null,
    );

    const { data, setData, patch, processing, errors } = useForm<{
        logo: File | null;
        name: string;
        is_featured: boolean;
        status: boolean;
    }>({
        logo: null,
        name: brandItem.name,
        is_featured: brandItem.is_featured,
        status: brandItem.status,
    });

    function onFileChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0] || null;
        setData('logo', file);

        if (preview) {
            URL.revokeObjectURL(preview);
        }

        if (file) {
            setPreview(URL.createObjectURL(file));
        }
    }

    function submit(e: React.FormEvent) {
        e.preventDefault();

        patch(brand.update(brandItem.id).url, {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toast.success('Бренд обновлён');
                setOpen(false);
            },
        });
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button size="sm" variant="outline">
                    <SquarePen className="mr-1 h-4 w-4" />
                    Редактировать
                </Button>
            </DialogTrigger>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Редактировать бренд</DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    {/* Logo */}
                    <div>
                        <Label>Логотип</Label>
                        <Input
                            type="file"
                            accept="image/*"
                            onChange={onFileChange}
                        />
                        {errors.logo && (
                            <p className="text-sm text-destructive">
                                {errors.logo}
                            </p>
                        )}

                        {preview && (
                            <img
                                src={preview}
                                className="mt-2 h-16 object-contain"
                            />
                        )}
                    </div>

                    {/* Name */}
                    <div>
                        <Label>Название</Label>
                        <Input
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                        />
                        {errors.name && (
                            <p className="text-sm text-destructive">
                                {errors.name}
                            </p>
                        )}
                    </div>

                    {/* Featured */}
                    <div className="flex items-center justify-between">
                        <Label>Показать</Label>
                        <Switch
                            checked={data.is_featured}
                            onCheckedChange={(v) => setData('is_featured', v)}
                        />
                    </div>

                    {/* Status */}
                    <div className="flex items-center justify-between">
                        <Label>Активен</Label>
                        <Switch
                            checked={data.status}
                            onCheckedChange={(v) => setData('status', v)}
                        />
                    </div>

                    <div className="flex justify-end gap-2 pt-4">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => setOpen(false)}
                        >
                            Отмена
                        </Button>

                        <Button type="submit" disabled={processing}>
                            Сохранить
                        </Button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
