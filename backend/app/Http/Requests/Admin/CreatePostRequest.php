<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', Rule::in(['fikr', 'dev'])],
            'title' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:200000'],
        ];
    }
}
