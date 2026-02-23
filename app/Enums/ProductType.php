<?php

namespace App\Enums;

enum ProductType: string
{
    case Top = 'Топ';
    case Recommended = 'Рекомендуемый';
    case New = 'Новый';
    case Best = 'Лучший';

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
            array_column(self::cases(), 'value'),
        );
    }
}
