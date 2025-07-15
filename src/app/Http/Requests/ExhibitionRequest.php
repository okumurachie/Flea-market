<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExhibitionRequest extends FormRequest
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
            'item_name' => 'required|string',
            'condition_id' => 'required|exists:conditions,id',
            'brand' => 'nullable|string',
            'description' => 'required|string|max:255',
            'price' => 'required|integer|min:0',
            'item_image' => 'required|mimes:jpg,jpeg,png',
            'category_ids' => 'required|array',
            'category_ids.*' => 'exists:categories,id'
        ];
    }

    public function messages()
    {
        return [
            'item_name.required' => '商品名を入力してください',
            'condition_id.required' => '商品の状態を選択してください',
            'description.required' => '商品説明を入力してください',
            'description.max' => '商品説明は255文字以内で入力してください',
            'price.required' => '価格を入力してください',
            'price.integer' => '価格は数値型で入力してください',
            'price.min' => '価格は0円以上で入力してください',
            'item_image.required' => '画像を選択してください',
            'item_image.mimes' => '画像は、拡張子がjpgもしくはjpeg,またはpngのものを選択してください',
            'category_ids.required' => '商品のカテゴリーを選択してください',
        ];
    }
}
