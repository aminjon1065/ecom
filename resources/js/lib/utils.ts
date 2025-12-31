import { InertiaLinkProps } from '@inertiajs/react';
import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function isSameUrl(
    url1: NonNullable<InertiaLinkProps['href']>,
    url2: NonNullable<InertiaLinkProps['href']>,
) {
    return resolveUrl(url1) === resolveUrl(url2);
}

export function resolveUrl(url: NonNullable<InertiaLinkProps['href']>): string {
    return typeof url === 'string' ? url : url.url;
}

export function buildPages(current: number, last: number): (number | '...')[] {
    const pages: (number | '...')[] = [];

    // если страниц мало — показываем всё
    if (last <= 7) {
        for (let i = 1; i <= last; i++) pages.push(i);
        return pages;
    }

    // 1. Всегда первая страница
    pages.push(1);

    // 2. ЛЕВАЯ ЧАСТЬ
    if (current > 4) {
        pages.push('...');
    }

    // 3. ОСНОВНОЙ ДИАПАЗОН вокруг текущей
    const start = Math.max(2, current - 1);
    const end = Math.min(last - 1, current + 1);

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    // 4. ПРАВАЯ ЧАСТЬ
    if (current < last - 3) {
        pages.push('...');
    }

    // 5. Всегда последняя страница
    pages.push(last);

    return pages;
}
