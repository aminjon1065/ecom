import { Switch } from '@/components/ui/switch';
import category from '@/routes/admin/category';
import { router } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Props {
    categoryId: number;
    initialStatus: boolean;
}

export function CategoryStatusSwitch({ categoryId, initialStatus }: Props) {
    const [checked, setChecked] = useState(initialStatus);
    const [loading, setLoading] = useState(false);

    function toggle() {
        if (loading) return;

        setLoading(true);
        setChecked((prev) => !prev); // optimistic UI

        router.patch(
            category.toggleStatus.url(categoryId),
            {},
            {
                preserveScroll: true,
                preserveState: true,
                onError: () => {
                    // rollback если ошибка
                    setChecked(initialStatus);
                },
                onSuccess: () =>
                    toast.success(
                        `Успешно ${initialStatus ? 'отключено' : 'включено'}`,
                    ),
                onFinish: () => {
                    setLoading(false);
                },
            },
        );
    }

    return (
        <Switch checked={checked} disabled={loading} onCheckedChange={toggle} />
    );
}
