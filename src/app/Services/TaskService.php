<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaskService
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {}

    /**
     * ログインユーザーのタスク一覧を取得
     */
    public function getTasks(User $user): LengthAwarePaginator
    {
        return $this->taskRepository->paginateForUser($user);
    }

    /**
     * ログインユーザーのタスクとして作成
     */
    public function createTask(User $user, array $data): Task
    {
        return $this->taskRepository->createForUser($user, $data);
    }

    /**
     * タスクを更新
     */
    public function updateTask(Task $task, array $data): Task
    {
        return $this->taskRepository->update($task, $data);
    }

    /**
     * 完了/未完了を切り替える
     */
    public function toggleCompletion(Task $task): Task
    {
        return $this->taskRepository->update($task, [
            'is_completed' => ! $task->is_completed,
        ]);
    }

    /**
     * タスクを削除
     */
    public function deleteTask(Task $task): void
    {
        $this->taskRepository->delete($task);
    }
}
