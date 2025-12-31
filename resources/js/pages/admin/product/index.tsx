import AppLayout from '@/layouts/app/admin/app-layout';
import product from '@/routes/admin/product';
import { Link } from '@inertiajs/react';

const Index = () => {
    return (
        <AppLayout>
            Продукты
            <Link href={product.create()}>Add</Link>
        </AppLayout>
    );
};

export default Index;
