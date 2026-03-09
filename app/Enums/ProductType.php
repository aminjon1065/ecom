<?php

namespace App\Enums;

enum ProductType: string
{
    case Top = 'top';
    case Recommended = 'recommended';
    case New = 'new';
    case Best = 'best';

    /**
     * Return all enum values as a plain array (useful for validation rules).
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Return an associative array of value => label for use in select inputs.
     */
    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn (self $case) => $case->label(), self::cases()),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Top => 'Топ',
            self::Recommended => 'Рекомендуемый',
            self::New => 'Новый',
            self::Best => 'Лучший',
        };
    }
}
