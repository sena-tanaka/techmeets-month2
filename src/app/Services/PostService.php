<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class PostService
{
    public function __construct(
        private PostRepository $postRepository
    ) {
    }

    /**
     * 投稿一覧を取得（ページネーション付き）
     * ページごとに10分間キャッシュする
     */
    public function getPosts(): LengthAwarePaginator
    {
        $page    = request()->integer('page', 1);
        $version = Cache::get('posts.cache_version', 1);
        $key     = "posts.index.v{$version}.page{$page}";

        return Cache::remember($key, now()->addMinutes(10), function () {
            return $this->postRepository->paginateLatest();
        });
    }

    /**
     * API用:投稿一覧を全件取得（投稿者情報つき）
     */
    public function getAllPostsForApi(): Collection
    {
        return $this->postRepository->getAllWithUser();
    }

    /**
     * API用:投稿一覧をページネーション付きで取得（投稿者・いいね情報つき）
     */
    public function getPostsForApi(): LengthAwarePaginator
    {
        return $this->postRepository->paginateForApi();
    }

    /**
     * ログインユーザーの投稿として作成
     */
    public function createPost(User $user, array $data): Post
    {
        $post = $this->postRepository->createForUser($user, $data);
        $this->clearPostsCache();

        return $post;
    }

    /**
     * 投稿を更新
     */
    public function updatePost(Post $post, array $data): Post
    {
        $post = $this->postRepository->update($post, $data);
        $this->clearPostsCache();

        return $post;
    }

    /**
     * 投稿を削除
     */
    public function deletePost(Post $post): void
    {
        $this->postRepository->delete($post);
        $this->clearPostsCache();
    }

    /**
     * いいねを切り替える
     */
    public function toggleLike(Post $post, User $user): void
    {
        $this->postRepository->toggleLike($post, $user);
        $this->clearPostsCache();
    }

    /**
     * 投稿一覧のキャッシュを無効化する
     * バージョン番号を上げることで、全ページの古いキャッシュを一度に使わなくする
     */
    private function clearPostsCache(): void
    {
        Cache::forever('posts.cache_version', Cache::get('posts.cache_version', 1) + 1);
    }
}
