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
        $required = $this->isMethod('POST') ? ['required'] : ['sometimes', 'required'];

        return [
            'name' => [...$required, 'string', 'max:120'],
            'repo' => ['sometimes', 'nullable', 'string', 'max:200'],
            'description' => ['sometimes', 'nullable', 'string', 'max:220'],
            'stack' => ['sometimes', 'nullable', 'array', 'max:8'],
            'stack.*' => ['nullable', 'string', 'max:40'],
            'demo_url' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Loyiha nomini yozing',
            'name.max' => 'Loyiha nomi juda uzun.',
            'description.max' => 'Tavsif 220 belgidan oshmasin.',
            'stack.max' => 'Texnologiyalar 8 tadan oshmasin.',
        ];
    }
}
