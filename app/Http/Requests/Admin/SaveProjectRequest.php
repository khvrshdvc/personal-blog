<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SaveProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'repo' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:220'],
            'stack' => ['nullable', 'string', 'max:300'],
            'demo_url' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Loyiha nomini yozing',
            'name.max' => 'Loyiha nomi juda uzun.',
            'description.max' => 'Tavsif 220 belgidan oshmasin.',
        ];
    }
}
