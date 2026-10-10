<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * PostResource:Postモデル1件を「APIで返すJSONの形」に変換するクラス
 *
 * @mixin \App\Models\Post
 */
class PostResource extends JsonResource
{
    // toArray():ここで返した配列が、そのままJSONになる
    public function toArray(Request $request): array
    {
        // $this は「変換しようとしているPost 1件」を指す
        return [
            'id'         => $this->id,
            'title'      => $this->title,
            'content'    => $this->content,

            // whenLoaded('user'):userリレーションを事前に読み込んでいる時だけ、
            // 投稿者名を含める(読み込んでいなければこの項目は出力されない)
            'author'     => $this->whenLoaded('user', fn () => $this->user->name),

            // 日付は画面で扱いやすい文字列に整形して返す
            'created_at' => $this->created_at->format('Y-m-d H:i'),
        ];
    }
}
