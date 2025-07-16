<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'upload_file' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,gif,webp,pdf,csv,doc,docx,xls,xlsx,ppt,pptx,mp4',
            ],
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function attributes(): array
    {
        return [
            'upload_file' => 'アップロードファイル',
            'description' => 'ファイル説明文',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attributeは必須項目です。',
            'file' => ':attributeは有効なファイルをアップロードしてください。',
            'max' => ':attributeのサイズは:max KB以下にしてください。',
            'mimes' => ':attributeは以下の形式のファイルのみアップロード可能です: :values',
            'mimetypes' => ':attributeのファイル形式が不正です。',
        ];
    }
}
