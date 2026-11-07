<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['sometimes', Rule::in(['fikr', 'dev'])],
            'title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:255'],
            'tag' => ['sometimes', 'nullable', 'string', 'max:64'],
            'excerpt' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'body' => ['sometimes', 'nullable', 'string', 'max:200000'],
            'published_at' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'cover' => ['sometimes', 'nullable', 'string', 'max:255', 'regex:#^(covers/[A-Za-z0-9._-]+|https?://\S+)$#'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Sarlavha juda uzun.',
            'body.max' => 'Matn juda katta.',
            'published_at.date_format' => 'Sana notoʻgʻri formatda.',
            'cover.regex' => 'Muqova manzili notoʻgʻri.',
        ];
    }
}
