import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ReactNode } from 'react';

export interface Column<T> {
    key: keyof T | string;
    label: string;
    render?: (row: T) => ReactNode;
    className?: string;
}

interface Props<T> {
    data: T[];
    columns: Column<T>[];
    loading?: boolean;
}

export function DataTable<T extends { id: number | string }>({
    data,
    columns,
    loading = false,
}: Props<T>) {
    if (loading) {
        return (
            <div className="py-6 text-sm text-muted-foreground">Загрузка…</div>
        );
    }

    if (!data.length) {
        return (
            <div className="py-6 text-sm text-muted-foreground">Нет данных</div>
        );
    }

    return (
        <Table>
            <TableHeader>
                <TableRow>
                    {columns.map((col) => (
                        <TableHead
                            key={String(col.key)}
                            className={col.className}
                        >
                            {col.label}
                        </TableHead>
                    ))}
                </TableRow>
            </TableHeader>

            <TableBody>
                {data.map((row) => (
                    <TableRow key={row.id}>
                        {columns.map((col) => (
                            <TableCell
                                key={String(col.key)}
                                className={col.className}
                            >
                                {col.render
                                    ? col.render(row)
                                    : String(row[col.key as keyof T])}
                            </TableCell>
                        ))}
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
