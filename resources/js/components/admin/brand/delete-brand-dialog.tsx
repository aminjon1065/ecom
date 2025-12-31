import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import brand from '@/routes/admin/brand';
import { router } from '@inertiajs/react';
import { Trash } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Props {
    brandId: number;
    brandName: string;
}

export function DeleteBrandDialog({ brandId, brandName }: Props) {
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);

    function destroy() {
        if (loading) return;
        setLoading(true);
        router.delete(brand.destroy(brandId), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                toast.success('Бренд удалён');
                setOpen(false);
            },
            onFinish: () => setLoading(false),
        });
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button size="sm" variant="destructive">
                    <Trash className="mr-1 h-4 w-4" />
                    Удалить
                </Button>
            </DialogTrigger>

            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Удалить бренд?</DialogTitle>
                </DialogHeader>

                <p className="text-sm text-muted-foreground">
                    Бренд <strong>{brandName}</strong> будет удалён без
                    возможности восстановления.
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
