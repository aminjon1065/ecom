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
import category from '@/routes/admin/category';
import { Category } from '@/types/category';
import { useForm } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';
interface Props {
    categoryItem: Category;
}

export function EditCategoryModal({ categoryItem }: Props) {
    const [open, setOpen] = useState(false);
    const [preview, setPreview] = useState<string | null>(
        categoryItem.icon ? `/storage/${categoryItem.icon}` : null,
    );

    const { data, setData, patch, processing, errors } = useForm<{
        name: string;
        icon: File | null;
        status: boolean;
    }>({
        name: categoryItem.name,
        icon: null,
        status: categoryItem.status,
    });

    function onFileChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0] || null;
        setData('icon', file);

        if (file) {
            setPreview(URL.createObjectURL(file));
        }
    }

    function submit(e: React.FormEvent) {
        e.preventDefault();

        patch(category.update(categoryItem.id).url, {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toast.success('Успешно обновлено');
                setOpen(false);
            },
        });
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button size="sm" variant="outline">
                    Редактировать
                </Button>
            </DialogTrigger>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Редактировать категорию</DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
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

                    <div>
                        <Label>Иконка</Label>
                        <Input
                            type="file"
                            accept="image/*"
                            onChange={onFileChange}
                        />

                        {preview && (
                            <img
                                src={preview}
                                className="mt-2 h-20 w-20 rounded object-cover"
                                alt={'Иконка'}
                            />
                        )}
                    </div>

                    <div className="flex items-center justify-between">
                        <Label>Активна</Label>
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
