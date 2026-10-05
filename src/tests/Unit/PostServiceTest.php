<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use App\Repositories\PostRepository;
use App\Services\PostService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    private MockInterface $repository;
    private PostService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // 本物のRepositoryの代わりに「偽物」を用意し、Serviceに渡す
        $this->repository = Mockery::mock(PostRepository::class);
        $this->service = new PostService($this->repository);
    }

    public function test_一覧取得はRepositoryのpaginateLatestの結果を返す(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->repository->shouldReceive('paginateLatest')
            ->once()
            ->andReturn($paginator);

        $this->assertSame($paginator, $this->service->getPosts());
    }

    public function test_API用一覧はRepositoryのgetAllWithUserの結果を返す(): void
    {
        $posts = new Collection([Post::factory()->make(), Post::factory()->make()]);

        $this->repository->shouldReceive('getAllWithUser')
            ->once()
            ->andReturn($posts);

        $result = $this->service->getAllPostsForApi();

        $this->assertSame($posts, $result);
        $this->assertCount(2, $result);
    }

    public function test_投稿作成はユーザーとデータをRepositoryに渡す(): void
    {
        $user = User::factory()->make();
        $data = ['title' => '新規', 'content' => '本文', 'category' => '日記'];
        $post = Post::factory()->make($data);

        $this->repository->shouldReceive('createForUser')
            ->once()
            ->with($user, $data)
            ->andReturn($post);

        $result = $this->service->createPost($user, $data);

        $this->assertSame('新規', $result->title);
    }

    public function test_投稿更新は投稿とデータをRepositoryに渡す(): void
    {
        $post = Post::factory()->make();
        $data = ['title' => '更新後', 'content' => '更新本文', 'category' => '技術'];
        $updated = Post::factory()->make($data);

        $this->repository->shouldReceive('update')
            ->once()
            ->with($post, $data)
            ->andReturn($updated);

        $this->assertSame('更新後', $this->service->updatePost($post, $data)->title);
    }

    public function test_投稿削除はRepositoryのdeleteを1回呼ぶ(): void
    {
        $post = Post::factory()->make();

        // 「deleteが、この投稿を引数に、ちょうど1回呼ばれること」を期待する
        $this->repository->shouldReceive('delete')
            ->once()
            ->with($post);

        $this->service->deletePost($post);
    }
        // ===== 異常系 =====
    public function test_Repositoryで例外が起きたらServiceはそのまま投げる(): void
    {
        $post = Post::factory()->make();

        $this->repository->shouldReceive('delete')
            ->once()
            ->andThrow(new \RuntimeException('DBエラー'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DBエラー');

        $this->service->deletePost($post);
    }
}