<?php

namespace App\Enums;

enum HomePageSectionType: string
{
    case Category = 'category';
    case FlashSale = 'flash_sale';
    case NewProducts = 'new_products';
    case TopProducts = 'top_products';
    case BestProducts = 'best_products';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function options(): array
    {
        return array_map(
            fn (self $case): array => [
                'value' => $case->value,
                'label' => $case->label(),
            ],
            self::cases(),
        );
    }

    public function label(): string
    {
        return match ($this) {
            self::Category => 'Категория',
            self::FlashSale => 'Акции и скидки',
            self::NewProducts => 'Новинки',
            self::TopProducts => 'Топ товары',
            self::BestProducts => 'Лучшие товары',
        };
    }
}
