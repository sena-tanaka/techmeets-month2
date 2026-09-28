<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
{
    /**
     * このリクエストを使ってよいか
     * 「自分の投稿か」の判定はPostPolicyで行うので、ここではtrueにする
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * 投稿の作成・更新で共通のバリデーションルール
     */
    public function rules(): array
    {
        return [
            'title'    => ['required', 'string', 'max:255'],
            'content'  => ['required', 'string', 'max:10000'],
            'category' => ['nullable', 'string', 'max:50'],
        ];
    }
}
