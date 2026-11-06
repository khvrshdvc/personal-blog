<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SavePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:16'],
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:64'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string', 'max:200000'],
            'published_at' => ['nullable', 'date_format:Y-m-d'],
            'cover' => ['nullable', 'string', 'max:255', 'regex:#^(covers/[A-Za-z0-9._-]+|https?://\S+)$#'],
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
