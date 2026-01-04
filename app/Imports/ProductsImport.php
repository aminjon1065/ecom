<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\Brand;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];

    public function collection(Collection $collection): void
    {
        // 1. Кеш справочниов (КРИТИЧНО для скорости)
        $categories = Category::all()->keyBy(fn($c) => mb_strtolower(trim($c->name)));
        $brands = Brand::all()->keyBy(fn($b) => mb_strtolower(trim($b->name)));

        foreach ($collection as $index => $row) {

            // чистим мусор
            foreach ($row as $key => $value) {
                if (str_starts_with($key, 'Unnamed')) {
                    unset($row[$key]);
                }
            }

            // 2. Базовая валидация
            $validator = Validator::make($row->toArray(), [
                'name' => 'required|string',
                'category' => 'required|string',
            ]);

            if ($validator->fails()) {
                $this->errors[] = [
                    'row' => $index + 2,
                    'errors' => $validator->errors()->all(),
                ];
                continue;
            }

            /* =========================
             | 3. Поиск категории
             ========================= */

            $categoryName = mb_strtolower(trim($row['category']));
            $category = $categories[$categoryName] ?? null;

            if (!$category) {
                $this->errors[] = [
                    'row' => $index + 2,
                    'errors' => ["Категория '{$row['category']}' не найдена"],
                ];
                continue;
            }

            /* =========================
             | 4. Поиск подкатегории
             ========================= */

            $subCategory = SubCategory::where('name', $row['sub_category'])
                ->where('category_id', $category->id)
                ->first();

            if (!$subCategory) {
                $this->errors[] = [
                    'row' => $index + 2,
                    'errors' => ["Подкатегория '{$row['sub_category']}' не найдена в '{$row['category']}'"],
                ];
                continue;
            }

            /* =========================
             | 5. Поиск дочерней категории (опц.)
             ========================= */

            $childCategory = null;

            if (!empty($row['child_category'])) {
                $childCategory = ChildCategory::where('name', $row['child_category'])
                    ->where('sub_category_id', $subCategory->id)
                    ->first();

                if (!$childCategory) {
                    $this->errors[] = [
                        'row' => $index + 2,
                        'errors' => ["Дочерняя категория '{$row['child_category']}' не найдена"],
                    ];
                    continue;
                }
            }

//            /* =========================
//             | 6. Поиск бренда
//             ========================= */
//
//            $brandName = mb_strtolower(trim($row['brand']));
//            $brand = $brands[$brandName] ?? null;
//
//            if (!$brand) {
//                $this->errors[] = [
//                    'row' => $index + 2,
//                    'errors' => ["Бренд '{$row['brand']}' не найден"],
//                ];
//                continue;
//            }
//
//            /* =========================
//             | 7. Сохранение продукта
//             ========================= */

            Product::updateOrCreate(
                ['code' => (int)$row['code']],
                [
                    'vendor_id' => 1,

                    'name' => $row['name'],
                    'slug' => \Str::slug($row['name']),
                    'thumb_image' => $row['thumb_image'],

                    'category_id' => $category->id,
                    'sub_category_id' => $subCategory->id,
                    'child_category_id' => $childCategory?->id,
                    'brand_id' => 1,
                    'sku' => $row['sku'],
                    'qty' => (int)$row['qty'],
                    'price' => (float)$row['price'],

                    'short_description' => $row['short_description'] ?? '',
                    'long_description' => $row['long_description'] ?? '',

                    'status' => filter_var($row['status'], FILTER_VALIDATE_BOOLEAN),
                    'is_approved' => filter_var($row['is_approved'], FILTER_VALIDATE_BOOLEAN),
                ]
            );
        }
    }
}
