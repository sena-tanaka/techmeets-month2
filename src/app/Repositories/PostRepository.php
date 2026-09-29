<?php

namespace App\Repositories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection; // 【追加】戻り値の型「Collection」を使うため

class PostRepository
{
    /**
     * 投稿一覧を新しい順で取得（ページネーション付き）
     */
    public function paginateLatest(int $perPage = 10): LengthAwarePaginator
    {
        return Post::with('user')->latest()->paginate($perPage);
    }

    /**
     * 【追加】API用:全投稿を投稿者(user)と一緒に、新しい順で取得（ページネーションなし）
     */
    public function getAllWithUser(): Collection
    {
        // with('user'):投稿者の情報もまとめて取得する(N+1問題を防ぐ)
        // latest():created_at が新しい順に並べる
        // get():ページ分けせず、全件を取得する
        return Post::with('user')->latest()->get();
    }

    /**
     * ユーザーに紐づけて投稿を作成
     * user_idはリレーションが自動でセットする（$fillableに入れなくてよい）
     */
    public function createForUser(User $user, array $data): Post
    {
        return $user->posts()->create($data);
    }

    /**
     * 投稿を更新
     */
    public function update(Post $post, array $data): Post
    {
        $post->update($data);
        return $post;
    }

    /**
     * 投稿を削除
     */
    public function delete(Post $post): void
    {
        $post->delete();
    }
}
