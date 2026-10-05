<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiPostTest extends TestCase
{
    use RefreshDatabase;

    // ===== GET /api/posts =====
    public function test_API一覧は誰でも取得できる(): void
    {
        Post::factory()->count(3)->create();

        $this->getJson('/api/posts')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_API一覧はPostResourceの形で返る(): void
    {
        $user = User::factory()->create(['name' => '山田太郎']);
        Post::factory()->for($user)->create([
            'title'   => 'APIテスト',
            'content' => 'API本文',
        ]);

        $this->getJson('/api/posts')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    ['id', 'title', 'content', 'author', 'created_at'],
                ],
            ])
            ->assertJsonPath('data.0.title', 'APIテスト')
            ->assertJsonPath('data.0.author', '山田太郎');
    }

    public function test_API一覧は新しい順に並ぶ(): void
    {
        Post::factory()->create(['title' => '古い投稿', 'created_at' => now()->subDay()]);
        Post::factory()->create(['title' => '新しい投稿', 'created_at' => now()]);

        $this->getJson('/api/posts')
            ->assertJsonPath('data.0.title', '新しい投稿')
            ->assertJsonPath('data.1.title', '古い投稿');
    }

    public function test_投稿が0件なら空の配列が返る(): void
    {
        $this->getJson('/api/posts')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    // ===== POST /api/posts（認証） =====
    public function test_トークンなしではAPIから投稿できず401(): void
    {
        $this->postJson('/api/posts', [
            'title'   => 'x',
            'content' => 'y',
        ])->assertUnauthorized();

        $this->assertDatabaseCount('posts', 0);
    }
}
