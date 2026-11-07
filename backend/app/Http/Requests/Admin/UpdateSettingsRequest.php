<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string', 'max:5000'],
            'open' => ['required', 'boolean'],
            'github' => ['nullable', 'string', 'max:200'],
            'now' => ['required', 'array', 'size:4'],
            'now.*.k' => ['nullable', 'string', 'max:120'],
            'now.*.v' => ['nullable', 'string', 'max:200'],
            'learn' => ['required', 'array', 'size:3'],
            'learn.*.n' => ['nullable', 'string', 'max:80'],
            'learn.*.p' => ['nullable', 'integer', 'min:0', 'max:100'],
            'telegram' => ['required', 'array'],
            'telegram.u' => ['nullable', 'string', 'max:200'],
            'telegram.n' => ['nullable', 'string', 'max:120'],
            'telegram.posts' => ['required', 'array', 'size:2'],
            'telegram.posts.*.t' => ['nullable', 'string', 'max:500'],
            'telegram.posts.*.d' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function messages(): array
    {
        return [
            'bio.max' => 'Bio juda uzun.',
            'learn.*.p.integer' => 'Foiz butun son boʻlishi kerak.',
            'learn.*.p.max' => 'Foiz 100 dan oshmasin.',
            'telegram.posts.*.d.date_format' => 'Sana notoʻgʻri formatda.',
        ];
    }
}
