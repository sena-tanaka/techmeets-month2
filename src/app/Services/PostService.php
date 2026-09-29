<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection; // 【追加】戻り値の型「Collection」を使うため

class PostService
{
    public function __construct(
        private PostRepository $postRepository
    ) {}

    /**
     * 投稿一覧を取得（ページネーション付き）
     */
    public function getPosts(): LengthAwarePaginator
    {
        return $this->postRepository->paginateLatest();
    }

    /**
     * 【追加】API用:投稿一覧を全件取得（投稿者情報つき）
     * データの取得そのものはRepositoryに任せる
     */
    public function getAllPostsForApi(): Collection
    {
        return $this->postRepository->getAllWithUser();
    }

    /**
     * ログインユーザーの投稿として作成
     */
    public function createPost(User $user, array $data): Post
    {
        return $this->postRepository->createForUser($user, $data);
    }

    /**
     * 投稿を更新
     */
    public function updatePost(Post $post, array $data): Post
    {
        return $this->postRepository->update($post, $data);
    }

    /**
     * 投稿を削除
     */
    public function deletePost(Post $post): void
    {
        $this->postRepository->delete($post);
    }
}
