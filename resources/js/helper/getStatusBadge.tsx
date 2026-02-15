import { Badge } from '@/components/ui/badge';

type Props = {
    order_status: string;
};

const GetStatusBadge = ({ order_status }: Props) => {
    const statusMap: Record<
        string,
        {
            label: string;
            variant: 'default' | 'secondary' | 'destructive' | 'outline';
        }
    > = {
        pending: { label: 'В обработке', variant: 'secondary' },
        processing: { label: 'Обрабатывается', variant: 'default' },
        delivered: { label: 'Доставлен', variant: 'default' },
        completed: { label: 'Завершен', variant: 'default' },
        cancelled: { label: 'Отменен', variant: 'destructive' },
    };

    const config = statusMap[order_status] ?? {
        label: order_status,
        variant: 'outline',
    };

    return <Badge variant={config.variant}>{config.label}</Badge>;
};

export default GetStatusBadge;
