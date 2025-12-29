import AppLayout from '@/layouts/app/admin/app-layout';
import { dashboard } from '@/routes/admin';
import category from '@/routes/admin/category';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Дашборд',
        href: dashboard().url,
    },
    {
        title: 'Категории',
        href: category.index().url,
    },
];
const AllCategories = () => {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <div>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ab
                aliquid delectus dolore eius eos eveniet exercitationem fuga
                fugiat hic illum laborum maiores natus neque nisi odio pariatur
                quaerat quas quia, quidem repellendus, reprehenderit sapiente
                sequi tempore vero, vitae! Aliquid, aperiam asperiores
                aspernatur assumenda consequatur cum deserunt dolor eius hic
                laboriosam nisi non, optio quae, quam quibusdam recusandae saepe
                sit tempora ullam ut! Eius, laborum magnam magni molestiae nemo
                nulla odit praesentium quibusdam, quos sapiente tenetur
                voluptas! Aut consequuntur dignissimos error illo numquam vitae.
                Aliquam assumenda dolor, dolorum earum eligendi fugit odio odit
                quasi quisquam quo recusandae totam ut veniam vero.
            </div>
        </AppLayout>
    );
};

export default AllCategories;
