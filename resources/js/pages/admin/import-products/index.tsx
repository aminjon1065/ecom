import { router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app/admin/app-layout';
import products from '@/routes/admin/products';

type ImportError = {
    row: number;
    errors: string[];
};

interface Props {
    importErrors?: ImportError[];
}

export default function Import({ importErrors }: Props) {
    const { data, setData, processing, reset } = useForm<{
        file: File | null;
    }>({
        file: null,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();

        if (!data.file) return;

        router.post(
            products.import().url,
            {
                file: data.file,
            },
            {
                forceFormData: true,
                onSuccess: () => {
                    reset();
                },
            },
        );
    };

    return (
        <AppLayout>
            <div className="max-w-3xl space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>Импорт товаров</CardTitle>
                    </CardHeader>

                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="file">Excel файл (.xlsx)</Label>
                                <Input
                                    id="file"
                                    type="file"
                                    accept=".xlsx,.xls"
                                    onChange={(e) =>
                                        setData(
                                            'file',
                                            e.target.files?.[0] ?? null,
                                        )
                                    }
                                />
                            </div>

                            <Button type="submit" disabled={processing}>
                                {processing
                                    ? 'Импортируется...'
                                    : 'Импортировать'}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                {/* Ошибки импорта */}
                {importErrors && importErrors.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Ошибки импорта</CardTitle>
                        </CardHeader>

                        <CardContent className="space-y-3">
                            {importErrors.map((error, index) => (
                                <Alert key={index} variant="destructive">
                                    <AlertTitle>Строка {error.row}</AlertTitle>
                                    <AlertDescription>
                                        <ul className="list-disc pl-5">
                                            {error.errors.map((e, i) => (
                                                <li key={i}>{e}</li>
                                            ))}
                                        </ul>
                                    </AlertDescription>
                                </Alert>
                            ))}
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
