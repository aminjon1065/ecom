<?php

namespace App\Enums;

enum ProductType: string
{
    case Top = 'top';
    case Recommended = 'recommended';
    case New = 'new';
    case Best = 'best';
    case LegacyTop = 'Топ';
    case LegacyRecommended = 'Рекомендуемый';
    case LegacyNew = 'Новый';
    case LegacyBest = 'Лучший';
    case LegacyNewArrival = 'new_arrival';

    /**
     * Return all enum values as a plain array (useful for validation rules).
     */
    public static function values(): array
    {
        return [
            self::Top->value,
            self::Recommended->value,
            self::New->value,
            self::Best->value,
        ];
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
            self::Top, self::LegacyTop => 'Топ',
            self::Recommended, self::LegacyRecommended => 'Рекомендуемый',
            self::New, self::LegacyNew, self::LegacyNewArrival => 'Новый',
            self::Best, self::LegacyBest => 'Лучший',
        };
    }
}
