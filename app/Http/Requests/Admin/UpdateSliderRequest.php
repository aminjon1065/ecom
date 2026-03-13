<?php

namespace App\Http\Requests\Admin;

use App\Models\Slider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSliderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        /** @var Slider $slider */
        $slider = $this->route('slider');

        return [
            'banner' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:4096'],
            'type' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'starting_price' => ['required', 'string', 'max:255'],
            'btn_url' => ['required', 'string', 'max:255'],
            'serial' => ['required', 'integer', Rule::unique('sliders', 'serial')->ignore($slider)],
            'status' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'banner.image' => 'Баннер должен быть изображением.',
            'serial.unique' => 'Слайдер с таким порядковым номером уже существует.',
        ];
    }
}
