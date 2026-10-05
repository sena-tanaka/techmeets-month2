<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostServicePaginationTest extends TestCase
{
    use RefreshDatabase;

    private function service(): PostService
    {
        return app(PostService::class); // 本物のRepositoryを使ったService
    }

    public function test_0件なら空のページが返る(): void
    {
        $posts = $this->service()->getPosts();

        $this->assertSame(0, $posts->total());
        $this->assertSame(1, $posts->lastPage());
    }

    public function test_ちょうど10件なら1ページに収まる(): void
    {
        Post::factory()->count(10)->create();

        $posts = $this->service()->getPosts();

        $this->assertCount(10, $posts->items());
        $this->assertSame(1, $posts->lastPage());
    }

    public function test_11件なら2ページに分かれる(): void
    {
        Post::factory()->count(11)->create();

        $posts = $this->service()->getPosts();

        $this->assertCount(10, $posts->items());
        $this->assertSame(2, $posts->lastPage());
    }
}
