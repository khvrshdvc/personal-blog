<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadCoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Rasm faylini tanlang.',
            'file.image' => 'Rasm faylini tanlang.',
            'file.mimes' => 'Faqat JPG, PNG yoki WebP rasm yuklash mumkin.',
            'file.max' => 'Rasm 5 MB dan katta.',
        ];
    }
}
