<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostLikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_ログインユーザーは投稿にいいねできる(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)
            ->from(route('posts.show', $post))
            ->post(route('posts.like', $post))
            ->assertRedirect(route('posts.show', $post));

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_もう一度押すといいねが取り消される(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();

        $this->actingAs($user)->post(route('posts.like', $post)); // いいね
        $this->actingAs($user)->post(route('posts.like', $post)); // 取り消し

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'post_id' => $post->id,
        ]);
    }

    public function test_複数のユーザーのいいねが数えられる(): void
    {
        $post = Post::factory()->create();

        foreach (User::factory()->count(3)->create() as $user) {
            $this->actingAs($user)->post(route('posts.like', $post));
        }

        $this->assertSame(3, $post->likes()->count());
    }

    public function test_未ログインではいいねできない(): void
    {
        $post = Post::factory()->create();

        $this->post(route('posts.like', $post))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('likes', 0);
    }

    public function test_存在しない投稿にはいいねできず404(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('posts.like', 99999))
            ->assertNotFound();
    }
}
