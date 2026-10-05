<?php

namespace App\Repositories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

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
     * API用:全投稿を投稿者(user)と一緒に、新しい順で取得（ページネーションなし）
     */
    public function getAllWithUser(): Collection
    {
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

    /**
     * いいねを切り替える（していなければ追加、していれば削除）
     */
    public function toggleLike(Post $post, User $user): void
    {
        $post->likes()->toggle($user->id);
    }
}