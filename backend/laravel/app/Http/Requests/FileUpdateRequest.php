<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function attributes(): array
    {
        return [
            'description' => 'ファイル説明文',
        ];
    }

    public function messages(): array
    {
        return [
            'max' => ':attributeは:max文字以内で入力してください。',
        ];
    }
}
