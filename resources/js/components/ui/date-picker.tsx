import * as React from 'react';
import { format } from 'date-fns';
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

export function DatePicker({
                               value,
                               onChange,
                               placeholder = 'Выберите дату',
                               disabled,
                               className,
                           }: DatePickerProps) {
    const date = value ? new Date(value) : undefined;

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button
                    variant="outline"
                    disabled={disabled}
                    className={cn(
                        'w-55 justify-between font-normal',
                        !value && 'text-muted-foreground',
                        className,
                    )}
                >
                    {value ? format(date!, 'dd.MM.yyyy') : placeholder}
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

                        // YYYY-MM-DD
                        const formatted = d
                            .toISOString()
                            .split('T')[0];

                        onChange(formatted);
                    }}
                    captionLayout="dropdown"
                    fromYear={2000}
                    toYear={new Date().getFullYear() + 5}
                />
            </PopoverContent>
        </Popover>
    );
}
