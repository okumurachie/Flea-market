<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'item_id' => 'required|exists:items,id',
            'payment_method' => 'required|in:konbini,card',
            'post_code' => ['required', 'regex:/^\d{3}-\d{4}$/'],
            'destination' => 'required|string',
        ];
    }
    public function messages()
    {
        return [
            'payment_method.required' => '支払い方法を選択してください',
            'post_code.required' => '郵便番号を入力してください',
            'post_code.regex' => '郵便番号は、ハイフンありの8文字で入力してください',
            'destination.required' => '配送先を指定してください',
        ];
    }
}
