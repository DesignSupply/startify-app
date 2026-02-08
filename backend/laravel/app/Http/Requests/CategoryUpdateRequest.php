<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryUpdateRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $categoryId = $this->route('id');
        
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-zA-Z0-9\-]+$/|unique:categories,slug,' . $categoryId,
        ];
    }

    public function attributes()
    {
        return [
            'name' => '名前',
            'slug' => 'スラッグ',
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attributeは必須項目です。',
            'max' => ':attributeは:max文字以内で入力してください。',
            'regex' => ':attributeは英数字とハイフンのみ使用できます。',
            'unique' => 'この:attributeは既に使用されています。',
        ];
    }
}
