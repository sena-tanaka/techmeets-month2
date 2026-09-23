<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    // 自分の投稿のみ編集できる
    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }

    // 自分の投稿のみ削除できる
    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id;
    }
}
