import { Switch } from '@/components/ui/switch';
import subCategory from '@/routes/admin/sub-category';
import { router } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Props {
    subCategoryId: number;
    initialStatus: boolean;
}

export function SubCategoryStatusSwitch({ subCategoryId, initialStatus }: Props) {
    const [checked, setChecked] = useState(initialStatus);
    const [loading, setLoading] = useState(false);

    function toggle() {
        if (loading) return;

        setLoading(true);
        setChecked((prev) => !prev); // optimistic UI

        router.patch(
            subCategory.toggleStatus.url(subCategoryId),
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
