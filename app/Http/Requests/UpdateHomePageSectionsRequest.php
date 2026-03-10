<?php

namespace App\Http\Requests;

use App\Enums\HomePageSectionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateHomePageSectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sections' => ['required', 'array', 'min:1', 'max:4'],
            'sections.*.type' => ['required', 'string', Rule::in(HomePageSectionType::values())],
            'sections.*.category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sections.required' => 'Добавьте хотя бы один блок на главную страницу.',
            'sections.array' => 'Блоки главной страницы переданы в неверном формате.',
            'sections.min' => 'Нужно настроить хотя бы один блок.',
            'sections.max' => 'Можно настроить максимум 4 блока.',
            'sections.*.type.required' => 'Выберите тип блока.',
            'sections.*.type.in' => 'Выбран недопустимый тип блока.',
            'sections.*.category_id.exists' => 'Выбранная категория не найдена.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $sections = $this->input('sections', []);

            foreach ($sections as $index => $section) {
                if (($section['type'] ?? null) === HomePageSectionType::Category->value
                    && blank($section['category_id'] ?? null)) {
                    $validator->errors()->add(
                        "sections.{$index}.category_id",
                        'Для блока категории нужно выбрать категорию.',
                    );
                }
            }
        });
    }
}
