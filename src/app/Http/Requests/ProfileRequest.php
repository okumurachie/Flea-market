<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'user_name' => 'required|string|max:20',
            'post_code' => ['required', 'regex:/^\d{3}-\d{4}$/'], // ハイフンありの8文字
            'address' => 'required|string',
            'image' => 'nullable|mimes:jpg,jpeg,png',
        ];
    }

    public function messages()
    {
        return [
            'user_name.required' => 'ユーザー名を入力してください',
            'user_name.max' => 'ユーザー名は20文字以内で入力してください',
            'post_code.required' => '郵便番号を入力してください',
            'post_code.regex' => '郵便番号は、ハイフンありの8文字で入力してください',
            'address.required' => '住所を入力してください',
            'image.mimes' => '画像は、拡張子がjpgもしくはjpeg,またはpngのものを選択してください',
        ];
    }
}
