<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostCrudTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title'    => 'テストタイトル',
            'content'  => 'テスト本文',
            'category' => '日記',
        ], $overrides);
    }

    // ===== Read（誰でも見られる） =====
    public function test_未ログインでも投稿一覧が見られる(): void
    {
        Post::factory()->create(['title' => '一覧に出る投稿']);

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee('一覧に出る投稿');
    }

    public function test_未ログインでも投稿詳細が見られる(): void
    {
        $post = Post::factory()->create();

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee($post->title);
    }

    public function test_存在しない投稿は404(): void
    {
        $this->get(route('posts.show', 99999))->assertNotFound();
    }

    // ===== Create =====
    public function test_ログインユーザーは作成画面を開ける(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('posts.create'))
            ->assertOk();
    }

    public function test_未ログインで作成画面を開くとログイン画面へ(): void
    {
        $this->get(route('posts.create'))->assertRedirect(route('login'));
    }

    public function test_ログインユーザーは投稿できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('posts.store'), $this->validData())
            ->assertRedirect(route('posts.index'))
            ->assertSessionHas('success', '投稿しました');

        $this->assertDatabaseHas('posts', [
            'title'   => 'テストタイトル',
            'user_id' => $user->id,
        ]);
    }

    public function test_未ログインでは投稿できない(): void
    {
        $this->post(route('posts.store'), $this->validData())
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_カテゴリなしでも投稿できる(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('posts.store'), $this->validData(['category' => '']))
            ->assertRedirect(route('posts.index'));

        $this->assertDatabaseCount('posts', 1);
    }

    public function test_フォームからuser_idを送っても他人の投稿にはならない(): void
    {
        $me    = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($me)
            ->post(route('posts.store'), $this->validData(['user_id' => $other->id]));

        $this->assertDatabaseHas('posts', ['user_id' => $me->id]);
        $this->assertDatabaseMissing('posts', ['user_id' => $other->id]);
    }

    // ===== Update =====
    public function test_自分の投稿の編集画面を開ける(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($post->user)
            ->get(route('posts.edit', $post))
            ->assertOk();
    }

    public function test_他人の投稿の編集画面は403(): void
    {
        $post = Post::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('posts.edit', $post))
            ->assertForbidden();
    }

    public function test_自分の投稿を更新できる(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($post->user)
            ->put(route('posts.update', $post), $this->validData(['title' => '更新後']))
            ->assertRedirect(route('posts.show', $post))
            ->assertSessionHas('success', '更新しました');

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => '更新後']);
    }

    public function test_他人の投稿は更新できない(): void
    {
        $post = Post::factory()->create(['title' => '元のタイトル']);

        $this->actingAs(User::factory()->create())
            ->put(route('posts.update', $post), $this->validData(['title' => '乗っ取り']))
            ->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => '元のタイトル']);
    }

    public function test_バリデーションエラーなら更新されない(): void
    {
        $post = Post::factory()->create(['title' => '元のタイトル']);

        $this->actingAs($post->user)
            ->put(route('posts.update', $post), $this->validData(['title' => '']))
            ->assertSessionHasErrors('title');

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => '元のタイトル']);
    }

    // ===== Delete =====
    public function test_自分の投稿を削除できる(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($post->user)
            ->delete(route('posts.destroy', $post))
            ->assertRedirect(route('posts.index'))
            ->assertSessionHas('success', '削除しました');

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_他人の投稿は削除できない(): void
    {
        $post = Post::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('posts.destroy', $post))
            ->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_未ログインでは削除できない(): void
    {
        $post = Post::factory()->create();

        $this->delete(route('posts.destroy', $post))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    // ===== バリデーション（画面経由） =====
    public function test_タイトル未入力だと投稿できない(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('posts.store'), $this->validData(['title' => '']))
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_本文未入力だと投稿できない(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('posts.store'), $this->validData(['content' => '']))
            ->assertSessionHasErrors('content');

        $this->assertDatabaseCount('posts', 0);
    }

    // ===== エッジケース =====
    public function test_scriptタグはエスケープして表示される(): void
    {
        $post = Post::factory()->create(['title' => '<script>alert(1)</script>']);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false); // 生のタグは出ない
    }
}
