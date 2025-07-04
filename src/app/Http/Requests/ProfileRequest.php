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
}
