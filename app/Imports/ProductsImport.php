<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\SubCategory;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class ProductsImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];

    public function collection(Collection $collection): void
    {
        $categoriesByName = Category::query()
            ->get()
            ->keyBy(fn (Category $category): string => $this->normalizeLookupValue($category->name));
        $subCategoriesByKey = SubCategory::query()
            ->get()
            ->keyBy(
                fn (SubCategory $subCategory): string => $subCategory->category_id.'|'.$this->normalizeLookupValue($subCategory->name)
            );
        $childCategoriesByKey = ChildCategory::query()
            ->get()
            ->keyBy(
                fn (ChildCategory $childCategory): string => $childCategory->sub_category_id.'|'.$this->normalizeLookupValue($childCategory->name)
            );
        $brandsByName = Brand::query()
            ->get()
            ->keyBy(fn (Brand $brand): string => $this->normalizeLookupValue($brand->name));
        $vendorIds = Vendor::query()
            ->pluck('id')
            ->flip();

        $fallbackCategory = $categoriesByName->get('разное');

        foreach ($collection as $index => $row) {
            $rowNumber = $index + 2;
            $rowData = $this->sanitizeRow($row);

            // Excel reads numeric SKUs as integers; cast to string before validation
            if (isset($rowData['sku']) && is_numeric($rowData['sku'])) {
                $rowData['sku'] = (string) $rowData['sku'];
            }

            $validator = Validator::make($rowData, [
                'code' => ['required', 'integer', 'min:1'],
                'name' => ['required', 'string', 'max:255'],
                'category' => ['required', 'string'],
                'sub_category' => ['nullable', 'string'],
                'brand' => ['nullable', 'string'],
                'thumb_image' => ['required', 'string'],
                'sku' => ['nullable', 'string', 'max:100'],
                'qty' => ['nullable', 'integer', 'min:0'],
                'price' => ['required', 'numeric', 'min:0'],
                'cost_price' => ['nullable', 'numeric', 'min:0'],
                'offer_price' => ['nullable', 'numeric', 'min:0'],
                'offer_start_date' => ['nullable'],
                'offer_end_date' => ['nullable'],
                'product_type' => ['nullable', 'string', 'max:255'],
                'short_description' => ['nullable', 'string'],
                'long_description' => ['nullable', 'string'],
                'video_link' => ['nullable', 'string', 'max:255'],
                'seo_title' => ['nullable', 'string', 'max:255'],
                'seo_description' => ['nullable', 'string'],
                'first_source_link' => ['nullable', 'string'],
                'second_source_link' => ['nullable', 'string'],
                'child_category' => ['nullable', 'string'],
                'vendor_id' => ['nullable', 'integer', 'min:1'],
                'status' => ['nullable'],
                'is_approved' => ['nullable'],
            ]);

            if ($validator->fails()) {
                $this->addError($rowNumber, $validator->errors()->all());

                continue;
            }

            $code = (int) $rowData['code'];
            $name = (string) $rowData['name'];

            $categoryName = $this->normalizeLookupValue((string) $rowData['category']);
            $category = $categoriesByName->get($categoryName) ?? $fallbackCategory;

            if (! $category) {
                $this->addError($rowNumber, ["Категория '{$rowData['category']}' не найдена и категория 'Разное' отсутствует в базе"]);

                continue;
            }

            $subCategoryValue = $rowData['sub_category'] ?? null;
            $subCategory = null;

            if ($subCategoryValue !== null && $subCategoryValue !== '') {
                $subCategoryName = $this->normalizeLookupValue((string) $subCategoryValue);
                $subCategory = $subCategoriesByKey->get($category->id.'|'.$subCategoryName);
            }

            $childCategory = null;
            $childCategoryValue = $rowData['child_category'] ?? null;

            if ($childCategoryValue !== null && $childCategoryValue !== '' && $subCategory !== null) {
                $childCategoryName = $this->normalizeLookupValue((string) $childCategoryValue);
                $childCategory = $childCategoriesByKey->get($subCategory->id.'|'.$childCategoryName);

                if (! $childCategory) {
                    $this->addError($rowNumber, ["Дочерняя категория '{$rowData['child_category']}' не найдена"]);

                    continue;
                }
            }

            $brand = null;
            $brandValue = $rowData['brand'] ?? null;

            if ($brandValue !== null && $brandValue !== '') {
                $brandName = $this->normalizeLookupValue((string) $brandValue);
                $brand = $brandsByName->get($brandName);

                if (! $brand) {
                    $this->addError($rowNumber, ["Бренд '{$brandValue}' не найден"]);

                    continue;
                }
            }

            $vendorId = null;
            $vendorValue = $rowData['vendor_id'] ?? null;

            if ($vendorValue !== null && $vendorValue !== '') {
                $candidateVendorId = (int) $vendorValue;

                if (! $vendorIds->has($candidateVendorId)) {
                    $this->addError($rowNumber, ["Продавец с ID '{$candidateVendorId}' не найден"]);

                    continue;
                }

                $vendorId = $candidateVendorId;
            }

            $existingProduct = Product::query()
                ->select(['id'])
                ->where('code', $code)
                ->first();

            $productData = [
                'vendor_id' => $vendorId,
                'name' => $name,
                'slug' => $this->generateUniqueSlug($name, $existingProduct?->id),
                'thumb_image' => (string) $rowData['thumb_image'],
                'category_id' => $category->id,
                'sub_category_id' => $subCategory?->id,
                'child_category_id' => $childCategory?->id,
                'sku' => $rowData['sku'] ?? null,
                'qty' => (int) ($rowData['qty'] ?? 0),
                'price' => (float) $rowData['price'],
                'cost_price' => isset($rowData['cost_price']) && $rowData['cost_price'] !== '' ? (float) $rowData['cost_price'] : null,
                'offer_price' => isset($rowData['offer_price']) && $rowData['offer_price'] !== '' ? (float) $rowData['offer_price'] : null,
                'offer_start_date' => $this->parseDate($rowData['offer_start_date'] ?? null),
                'offer_end_date' => $this->parseDate($rowData['offer_end_date'] ?? null),
                'product_type' => isset($rowData['product_type']) && $rowData['product_type'] !== '' ? (string) $rowData['product_type'] : null,
                'short_description' => (string) ($rowData['short_description'] ?? ''),
                'long_description' => $this->normalizeLongDescription($rowData['long_description'] ?? null),
                'video_link' => isset($rowData['video_link']) && $rowData['video_link'] !== '' ? (string) $rowData['video_link'] : null,
                'seo_title' => isset($rowData['seo_title']) && $rowData['seo_title'] !== '' ? (string) $rowData['seo_title'] : null,
                'seo_description' => isset($rowData['seo_description']) && $rowData['seo_description'] !== '' ? (string) $rowData['seo_description'] : null,
                'first_source_link' => isset($rowData['first_source_link']) && $rowData['first_source_link'] !== '' ? (string) $rowData['first_source_link'] : null,
                'second_source_link' => isset($rowData['second_source_link']) && $rowData['second_source_link'] !== '' ? (string) $rowData['second_source_link'] : null,
                'status' => $this->parseBoolean($rowData['status'] ?? null, true),
                'is_approved' => $this->parseBoolean($rowData['is_approved'] ?? null, false),
            ];

            if ($brand !== null) {
                $productData['brand_id'] = $brand->id;
            }

            try {
                Product::updateOrCreate(['code' => $code], $productData);
            } catch (Throwable $exception) {
                $this->addError($rowNumber, ["Ошибка сохранения: {$exception->getMessage()}"]);
            }
        }
    }

    private function sanitizeRow(Collection $row): array
    {
        return $row
            ->reject(
                fn (mixed $value, mixed $key): bool => is_string($key)
                    && str_starts_with($key, 'Unnamed')
            )
            ->map(fn (mixed $value): mixed => is_string($value) ? trim($value) : $value)
            ->toArray();
    }

    private function normalizeLookupValue(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }

    private function parseBoolean(mixed $value, bool $default): bool
    {
        if ($value === null || $value === '') {
            return $default;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($parsed === null) {
            return $default;
        }

        return $parsed;
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Excel serial date stored as a float
        if (is_numeric($value)) {
            try {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (Throwable) {
                return null;
            }
        }

        try {
            return \Carbon\Carbon::parse((string) $value)->format('Y-m-d');
        } catch (Throwable) {
            return null;
        }
    }

    private function generateUniqueSlug(string $name, ?int $ignoreProductId): string
    {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'product';
        }

        $candidateSlug = $baseSlug;
        $suffix = 2;

        while (
            Product::query()
                ->where('slug', $candidateSlug)
                ->when(
                    $ignoreProductId !== null,
                    fn ($query) => $query->where('id', '!=', $ignoreProductId),
                )
                ->exists()
        ) {
            $candidateSlug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $candidateSlug;
    }

    private function normalizeLongDescription(mixed $value): string
    {
        if (is_string($value) && $value !== '') {
            try {
                json_decode($value, true, flags: JSON_THROW_ON_ERROR);

                return $value;
            } catch (Throwable) {
                return $this->buildEditorStateJson($value);
            }
        }

        return $this->buildEditorStateJson('');
    }

    private function buildEditorStateJson(string $text): string
    {
        $payload = [
            'root' => [
                'children' => [
                    [
                        'children' => [
                            [
                                'detail' => 0,
                                'format' => 0,
                                'mode' => 'normal',
                                'style' => '',
                                'text' => $text,
                                'type' => 'text',
                                'version' => 1,
                            ],
                        ],
                        'direction' => 'ltr',
                        'format' => '',
                        'indent' => 0,
                        'type' => 'paragraph',
                        'version' => 1,
                    ],
                ],
                'direction' => 'ltr',
                'format' => '',
                'indent' => 0,
                'type' => 'root',
                'version' => 1,
            ],
        ];

        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    /**
     * @param  array<int, string>  $messages
     */
    private function addError(int $rowNumber, array $messages): void
    {
        $this->errors[] = [
            'row' => $rowNumber,
            'errors' => $messages,
        ];
    }
}
