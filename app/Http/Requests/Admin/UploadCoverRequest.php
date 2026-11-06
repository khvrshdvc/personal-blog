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
            'cover' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'cover.required' => 'Rasm faylini tanlang.',
            'cover.image' => 'Rasm faylini tanlang.',
            'cover.mimes' => 'Faqat JPG, PNG yoki WebP rasm yuklash mumkin.',
            'cover.max' => 'Rasm 5 MB dan katta.',
        ];
    }
}
