<?php

namespace Tests\Unit;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;
use Tests\TestCase;

class PostPolicyTest extends TestCase
{
    private PostPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new PostPolicy();
    }

    // DBに保存せずにIDだけ持ったユーザーを作る
    private function makeUser(int $id): User
    {
        $user = User::factory()->make();
        $user->id = $id;
        return $user;
    }

    public function test_自分の投稿は編集できる(): void
    {
        $post = Post::factory()->make(['user_id' => 1]);
        $this->assertTrue($this->policy->update($this->makeUser(1), $post));
    }

    public function test_他人の投稿は編集できない(): void
    {
        $post = Post::factory()->make(['user_id' => 1]);
        $this->assertFalse($this->policy->update($this->makeUser(2), $post));
    }

    public function test_自分の投稿は削除できる(): void
    {
        $post = Post::factory()->make(['user_id' => 1]);
        $this->assertTrue($this->policy->delete($this->makeUser(1), $post));
    }

    public function test_他人の投稿は削除できない(): void
    {
        $post = Post::factory()->make(['user_id' => 1]);
        $this->assertFalse($this->policy->delete($this->makeUser(2), $post));
    }
}
