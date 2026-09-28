<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * 「自分のタスクか」の判定はTaskPolicyで行うので、ここではtrueにする
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * タスクの作成・更新で共通のバリデーションルール
     */
    public function rules(): array
    {
        return [
            'title'        => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string', 'max:2000'],
            'due_date'     => ['nullable', 'date'],
            'is_completed' => ['sometimes', 'boolean'],
        ];
    }
}
