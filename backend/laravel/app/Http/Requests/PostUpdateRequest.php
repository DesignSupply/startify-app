<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostUpdateRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'published_at' => 'required|date',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ];
    }

    public function attributes()
    {
        return [
            'title' => 'タイトル',
            'body' => '本文',
            'published_at' => '公開日時',
            'categories' => 'カテゴリ',
            'tags' => 'タグ',
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attributeは必須項目です。',
            'max' => ':attributeは:max文字以内で入力してください。',
            'date' => ':attributeの形式が正しくありません。',
            'exists' => '選択された:attributeは無効です。',
        ];
    }
}
