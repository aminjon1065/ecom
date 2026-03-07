<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePopularSearchQueryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'query' => trim((string) $this->input('query', '')),
            'is_active' => filter_var($this->input('is_active', true), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'max:120', 'unique:popular_search_queries,query'],
            'priority' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'query.unique' => 'Такой запрос уже существует.',
        ];
    }
}
