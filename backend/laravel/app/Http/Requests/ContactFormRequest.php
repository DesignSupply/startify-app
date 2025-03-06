<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactFormRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|regex:/^[0-9]{10,11}$/',
            'url' => 'nullable|url',
            'inquiry_type' => 'nullable|array',
            'inquiry_type.*' => 'string',
            'gender' => 'required|string|in:男性,女性',
            'message' => 'required|string',
        ];
    }

    public function attributes()
    {
        return [
            'name' => 'お名前',
            'company' => '会社名',
            'email' => 'メールアドレス',
            'phone' => '電話番号',
            'url' => 'ウェブサイトURL',
            'inquiry_type' => 'お問い合わせ種別',
            'inquiry_type.*' => 'お問い合わせ種別',
            'gender' => '性別',
            'message' => 'お問い合わせ内容',
        ];
    }

    public function messages()
    {
        return [
            'required' => ':attributeは必須項目です。',
            'email' => ':attributeの形式が正しくありません。',
            'max' => ':attributeは:max文字以内で入力してください。',
            'regex' => ':attributeの形式が正しくありません。',
            'url' => ':attributeの形式が正しくありません。',
            'in' => ':attributeの値が不正です。',
        ];
    }
}
