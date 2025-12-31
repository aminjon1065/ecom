import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';
import { buildPages } from '@/lib/utils';

interface Props {
    currentPage: number;
    lastPage: number;
    path: string;
}

export function Pagination({ currentPage, lastPage, path }: Props) {
    if (lastPage <= 1) return null;

    const pages = buildPages(currentPage, lastPage);

    function go(page: number) {
        router.visit(`${path}?page=${page}`, {
            preserveScroll: true,
            preserveState: true,
        });
    }

    return (
        <div className="mt-4 flex items-center gap-1">
            {/* PREV */}
            <Button
                size="sm"
                variant="outline"
                disabled={currentPage === 1}
                onClick={() => go(currentPage - 1)}
            >
                Пред
            </Button>

            {pages.map((page, index) =>
                page === '...' ? (
                    <span key={index} className="px-2 text-muted-foreground">
                        …
                    </span>
                ) : (
                    <Button
                        key={index}
                        size="sm"
                        variant={page === currentPage ? 'default' : 'outline'}
                        onClick={() => go(page)}
                    >
                        {page}
                    </Button>
                ),
            )}

            {/* NEXT */}
            <Button
                size="sm"
                variant="outline"
                disabled={currentPage === lastPage}
                onClick={() => go(currentPage + 1)}
            >
                След
            </Button>
        </div>
    );
}
