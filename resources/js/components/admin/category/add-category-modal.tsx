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
import { useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { useState } from 'react';

export function AddCategoryModal() {
    const [open, setOpen] = useState(false);
    const [preview, setPreview] = useState<string | null>(null);

    const { data, setData, post, processing, errors, reset } = useForm<{
        name: string;
        icon: File | null;
        status: boolean;
    }>({
        name: '',
        icon: null,
        status: true,
    });

    function onFileChange(e: React.ChangeEvent<HTMLInputElement>) {
        const file = e.target.files?.[0] || null;
        setData('icon', file);

        if (file) {
            setPreview(URL.createObjectURL(file));
        } else {
            setPreview(null);
        }
    }

    function submit(e: React.FormEvent) {
        e.preventDefault();

        post(category.store().url, {
            forceFormData: true,
            onSuccess: () => {
                reset();
                setPreview(null);
                setOpen(false);
            },
        });
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button>
                    <Plus />
                    Добавить категорию
                </Button>
            </DialogTrigger>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Новая категория</DialogTitle>
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
                                alt="preview"
                                className="mt-2 h-20 w-20 rounded border object-cover"
                            />
                        )}

                        {errors.icon && (
                            <p className="text-sm text-destructive">
                                {errors.icon}
                            </p>
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
