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
import subCategory from '@/routes/admin/sub-category';
import { Category } from '@/types/category';
import { SubCategory } from '@/types/sub-category';
import { useForm } from '@inertiajs/react';
import { SquarePen } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Props {
    subCategoryItem: SubCategory;
    categories: Category[];
}

export function EditSubCategoryModal({ subCategoryItem, categories }: Props) {
    const [open, setOpen] = useState(false);

    const { data, setData, patch, processing, errors } = useForm<{
        name: string;
        category_id: number;
        status: boolean;
    }>({
        name: subCategoryItem.name,
        category_id: subCategoryItem.category_id,
        status: subCategoryItem.status,
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();

        patch(subCategory.update(subCategoryItem.id).url, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toast.success('Подкатегория обновлена');
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
                    <DialogTitle>Редактировать подкатегорию</DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    {/* Category */}
                    <div>
                        <Label>Категория</Label>
                        <select
                            className="w-full rounded-md border px-3 py-2 text-sm"
                            value={data.category_id}
                            onChange={(e) =>
                                setData('category_id', Number(e.target.value))
                            }
                        >
                            {categories.map((cat) => (
                                <option key={cat.id} value={cat.id}>
                                    {cat.name}
                                </option>
                            ))}
                        </select>
                        {errors.category_id && (
                            <p className="text-sm text-destructive">
                                {errors.category_id}
                            </p>
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

                    {/* Status */}
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
