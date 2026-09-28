<?php

namespace App\Repositories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskRepository
{
    /**
     * 指定したユーザーのタスクだけを取得（未完了を先、新しい順）
     */
    public function paginateForUser(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return $user->tasks()
            ->orderBy('is_completed')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * ユーザーに紐づけてタスクを作成
     */
    public function createForUser(User $user, array $data): Task
    {
        return $user->tasks()->create($data);
    }

    /**
     * タスクを更新
     */
    public function update(Task $task, array $data): Task
    {
        $task->update($data);
        return $task;
    }

    /**
     * タスクを削除
     */
    public function delete(Task $task): void
    {
        $task->delete();
    }
}
