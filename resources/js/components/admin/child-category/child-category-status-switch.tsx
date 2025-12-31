import { Switch } from '@/components/ui/switch';
import childCategory from '@/routes/admin/child-category';
import { router } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Props {
    childCategoryId: number;
    initialStatus: boolean;
}

export function ChildCategoryStatusSwitch({
    childCategoryId,
    initialStatus,
}: Props) {
    const [checked, setChecked] = useState(initialStatus);
    const [loading, setLoading] = useState(false);

    function toggle(nextValue: boolean) {
        if (loading) return;

        setLoading(true);
        setChecked(nextValue); // optimistic UI

        router.patch(
            childCategory.toggleStatus(childCategoryId).url,
            {},
            {
                preserveScroll: true,
                preserveState: true,
                onError: () => {
                    // rollback
                    setChecked(initialStatus);
                },
                onSuccess: () => {
                    toast.success(nextValue ? 'Включено' : 'Отключено');
                },
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
