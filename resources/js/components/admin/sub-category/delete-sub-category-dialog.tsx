import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { router } from '@inertiajs/react';
import { Trash } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import subCategory from '@/routes/admin/sub-category';

interface Props {
    subCategoryId: number;
    subCategoryName: string;
}

export function DeleteSubCategoryDialog({ subCategoryId, subCategoryName }: Props) {
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);

    function destroy() {
        if (loading) return;

        setLoading(true);

        router.delete(subCategory.destroy(subCategoryId).url, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toast.success('Категория удалена');
                setOpen(false);
            },
            onFinish: () => setLoading(false),
        });
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button size="sm" variant="destructive">
                    <Trash />
                    Удалить
                </Button>
            </DialogTrigger>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Удалить подкатегорию?</DialogTitle>
                </DialogHeader>

                <p className="text-sm text-muted-foreground">
                    Подкатегория <strong>{subCategoryName}</strong> будет удалена
                    без возможности восстановления.
                </p>

                <div className="flex justify-end gap-2 pt-4">
                    <Button
                        variant="outline"
                        onClick={() => setOpen(false)}
                        disabled={loading}
                    >
                        Отмена
                    </Button>

                    <Button
                        variant="destructive"
                        onClick={destroy}
                        disabled={loading}
                    >
                        Удалить
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
