<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    // 自分のタスクのみ見られる
    public function view(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    // 自分のタスクのみ編集できる
    public function update(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }

    // 自分のタスクのみ削除できる
    public function delete(User $user, Task $task): bool
    {
        return $user->id === $task->user_id;
    }
}
