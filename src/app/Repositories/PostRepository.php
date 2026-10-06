<?php

namespace App\Repositories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
