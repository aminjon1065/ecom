<?php

use Illuminate\Support\Facades\Validator;

it('uses russian validation and system messages', function () {
    app()->setLocale('ru');

    $validator = Validator::make(
        ['name' => ''],
        ['name' => ['required']]
    );

    expect($validator->errors()->first('name'))
        ->toBe('Поле название обязательно для заполнения.');

    expect(__('The given data was invalid.'))
        ->toBe('Введённые данные некорректны.');
});
