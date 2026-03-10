import * as React from 'react';
import { format, isValid, parseISO } from 'date-fns';
import { Calendar } from '@/components/ui/calendar';
import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { ChevronDown } from 'lucide-react';
import { cn } from '@/lib/utils';

interface DatePickerProps {
    value: string | null; // YYYY-MM-DD
    onChange: (value: string | null) => void;
    placeholder?: string;
    disabled?: boolean;
    className?: string;
}

function parseDateValue(value: string): Date | undefined {
    const normalizedValue = value.trim();

    if (normalizedValue === '') {
        return undefined;
    }

    const dateOnlyMatch = normalizedValue.match(/^(\d{4})-(\d{2})-(\d{2})$/);

    if (dateOnlyMatch) {
        const [, year, month, day] = dateOnlyMatch;
        const localDate = new Date(Number(year), Number(month) - 1, Number(day));

        return isValid(localDate) ? localDate : undefined;
    }

    const isoDate = parseISO(normalizedValue);

    return isValid(isoDate) ? isoDate : undefined;
}

export function DatePicker({
                               value,
                               onChange,
                               placeholder = 'Выберите дату',
                               disabled,
                               className,
                           }: DatePickerProps) {
    const date = value ? parseDateValue(value) : undefined;

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    disabled={disabled}
                    className={cn(
                        'w-55 justify-between font-normal',
                        !date && 'text-muted-foreground',
                        className,
                    )}
                >
                    {date ? format(date, 'dd.MM.yyyy') : placeholder}
                    <ChevronDown className="h-4 w-4 opacity-50" />
                </Button>
            </PopoverTrigger>

            <PopoverContent
                align="start"
                className="w-auto p-0"
            >
                <Calendar
                    mode="single"
                    selected={date}
                    onSelect={(d) => {
                        if (!d) {
                            onChange(null);
                            return;
                        }

                        onChange(format(d, 'yyyy-MM-dd'));
                    }}
                    captionLayout="dropdown"
                    fromYear={2000}
                    toYear={new Date().getFullYear() + 5}
                />
            </PopoverContent>
        </Popover>
    );
}
