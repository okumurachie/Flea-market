<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatMessageReauest extends FormRequest
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
            'text' => [
                'required',
                'string',
                'max:400',
            ],
            'image' => [
                'nullable',
                'file',
                'mimes:jpeg,png',
            ],
        ];
    }

    public function messages()
    {
        return [
            'text.required' => '本文を入力してください',
            'text.max' => '本文は400文字以内で入力してください',

            'image.mimes' => '「.png」または「.jpeg」形式でアップロードしてください',
        ];
    }
}
