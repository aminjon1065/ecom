import { Switch } from '@/components/ui/switch';
import brand from '@/routes/admin/brand';
import { router } from '@inertiajs/react';
import { useState } from 'react';
import { toast } from 'sonner';

interface Props {
    brandId: number;
    initialStatus: boolean;
}

export function BrandFeatureSwitch({ brandId, initialStatus }: Props) {
    const [checked, setChecked] = useState(initialStatus);
    const [loading, setLoading] = useState(false);

    function toggle(nextValue: boolean) {
        if (loading) return;

        setLoading(true);
        setChecked(nextValue); // optimistic UI

        router.patch(
            brand.toggleFeature(brandId).url,
            {},
            {
                preserveScroll: true,
                preserveState: true,
                onError: () => {
                    setChecked(initialStatus); // rollback
                },
                onSuccess: () => {
                    toast.success(
                        nextValue
                            ? 'Бренд показывается'
                            : 'Бренд не показывается',
                    );
                },
                onFinish: () => setLoading(false),
            },
        );
    }

    return (
        <Switch checked={checked} disabled={loading} onCheckedChange={toggle} />
    );
}
