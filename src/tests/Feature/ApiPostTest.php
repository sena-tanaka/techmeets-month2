<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
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

    // ===== POST /api/posts（トークンあり） =====
    private function apiData(array $overrides = []): array
    {
        return array_merge([
            'title'    => 'API投稿',
            'content'  => 'API本文',
            'category' => '技術',
        ], $overrides);
    }

    public function test_トークンありならAPIから投稿でき201が返る(): void
    {
        $user = User::factory()->create(['name' => '佐藤花子']);
        Sanctum::actingAs($user); // トークンを持ってログインした状態にする

        $this->postJson('/api/posts', $this->apiData())
            ->assertCreated() // 201
            ->assertJsonPath('data.title', 'API投稿')
            ->assertJsonPath('data.author', '佐藤花子');

        $this->assertDatabaseHas('posts', [
            'title'   => 'API投稿',
            'user_id' => $user->id,
        ]);
    }

    public function test_APIでタイトル未入力だと422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/posts', $this->apiData(['title' => '']))
            ->assertUnprocessable() // 422
            ->assertJsonValidationErrors('title');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_APIでは本文が必須(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/posts', $this->apiData(['content' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('content');
    }

    public function test_APIではカテゴリが必須(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/posts', $this->apiData(['category' => '']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('category');
    }

    public function test_APIでタイトル256文字だと422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/posts', $this->apiData(['title' => str_repeat('あ', 256)]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('title');
    }

    public function test_APIでuser_idを送っても他人の投稿にはならない(): void
    {
        $me    = User::factory()->create();
        $other = User::factory()->create();
        Sanctum::actingAs($me);

        $this->postJson('/api/posts', $this->apiData(['user_id' => $other->id]))
            ->assertCreated();

        $this->assertDatabaseHas('posts', ['user_id' => $me->id]);
        $this->assertDatabaseMissing('posts', ['user_id' => $other->id]);
    }

}
