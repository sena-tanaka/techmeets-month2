<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use Illuminate\Database\Eloquent\Collection;

class PostService
{
    public function __construct(
        private PostRepository $postRepository
    ) {}

    /**
     * 投稿一覧を取得
     */
    public function getPosts(): Collection
    {
        return $this->postRepository->getAllLatest();
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
