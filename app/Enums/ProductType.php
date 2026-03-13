<?php

namespace App\Enums;

enum ProductType: string
{
    case Top = 'top';
    case Recommended = 'recommended';
    case New = 'new';
    case Best = 'best';
    case LegacyTopProduct = 'top_product';
    case LegacyFeaturedProduct = 'featured_product';
    case LegacyBestProduct = 'best_product';
    case LegacyTop = 'Топ';
    case LegacyRecommended = 'Рекомендуемый';
    case LegacyNew = 'Новый';
    case LegacyBest = 'Лучший';
    case LegacyNewArrival = 'new_arrival';

    public static function values(): array
    {
        return [
            self::Top->value,
            self::Recommended->value,
            self::New->value,
            self::Best->value,
        ];
    }

    public static function fromDatabaseValue(null|string|self $value): ?self
    {
        if ($value instanceof self) {
            return $value->canonical();
        }

        if ($value === null || $value === '') {
            return null;
        }

        return match ($value) {
            self::Top->value,
            self::LegacyTopProduct->value,
            self::LegacyTop->value => self::Top,

            self::Recommended->value,
            self::LegacyFeaturedProduct->value,
            self::LegacyRecommended->value => self::Recommended,

            self::New->value,
            self::LegacyNew->value,
            self::LegacyNewArrival->value => self::New,

            self::Best->value,
            self::LegacyBestProduct->value,
            self::LegacyBest->value => self::Best,

            default => null,
        };
    }

    public static function normalizeDatabaseValue(null|string|self $value): ?string
    {
        return self::fromDatabaseValue($value)?->value;
    }

    /**
     * @return array<int, string>
     */
    public static function databaseValuesFor(self|string $value): array
    {
        $canonical = self::fromDatabaseValue($value);

        return match ($canonical) {
            self::Top => [self::Top->value, self::LegacyTopProduct->value, self::LegacyTop->value],
            self::Recommended => [self::Recommended->value, self::LegacyFeaturedProduct->value, self::LegacyRecommended->value],
            self::New => [self::New->value, self::LegacyNewArrival->value, self::LegacyNew->value],
            self::Best => [self::Best->value, self::LegacyBestProduct->value, self::LegacyBest->value],
            default => [$value instanceof self ? $value->value : $value],
        };
    }

    public static function options(): array
    {
        return array_combine(
            array_column(self::cases(), 'value'),
            array_map(fn(self $case) => $case->label(), self::cases()),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Top, self::LegacyTop, self::LegacyTopProduct => 'Топ',
            self::Recommended, self::LegacyRecommended, self::LegacyFeaturedProduct => 'Рекомендуемый',
            self::New, self::LegacyNew, self::LegacyNewArrival => 'Новый',
            self::Best, self::LegacyBest, self::LegacyBestProduct => 'Лучший',
        };
    }

    public function canonical(): self
    {
        return match ($this) {
            self::Top, self::LegacyTop, self::LegacyTopProduct => self::Top,
            self::Recommended, self::LegacyRecommended, self::LegacyFeaturedProduct => self::Recommended,
            self::New, self::LegacyNew, self::LegacyNewArrival => self::New,
            self::Best, self::LegacyBest, self::LegacyBestProduct => self::Best,
        };
    }
}
