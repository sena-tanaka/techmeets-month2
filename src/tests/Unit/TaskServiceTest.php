<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Repositories\TaskRepository;
use App\Services\TaskService;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    private MockInterface $repository;
    private TaskService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TaskRepository::class);
        $this->service = new TaskService($this->repository);
    }

    // DBに保存しないタスクを作る
    private function makeTask(bool $isCompleted = false): Task
    {
        $task = new Task();
        $task->is_completed = $isCompleted;
        return $task;
    }

    public function test_一覧取得はユーザーを渡してpaginateForUserを呼ぶ(): void
    {
        $user = new User();
        $paginator = new LengthAwarePaginator([], 0, 10);

        $this->repository->shouldReceive('paginateForUser')
            ->once()
            ->with($user)
            ->andReturn($paginator);

        $this->assertSame($paginator, $this->service->getTasks($user));
    }

    public function test_タスク作成はユーザーとデータをRepositoryに渡す(): void
    {
        $user = new User();
        $data = ['title' => '買い物'];
        $task = new Task();

        $this->repository->shouldReceive('createForUser')
            ->once()
            ->with($user, $data)
            ->andReturn($task);

        $this->assertSame($task, $this->service->createTask($user, $data));
    }

    public function test_タスク更新はタスクとデータをRepositoryに渡す(): void
    {
        $task = $this->makeTask();
        $data = ['title' => '更新後'];

        $this->repository->shouldReceive('update')
            ->once()
            ->with($task, $data)
            ->andReturn($task);

        $this->assertSame($task, $this->service->updateTask($task, $data));
    }

    // ===== toggleCompletion：Service独自のロジック =====
    public function test_未完了のタスクを切り替えると完了になる(): void
    {
        $task = $this->makeTask(false);

        $this->repository->shouldReceive('update')
            ->once()
            ->with($task, ['is_completed' => true])
            ->andReturn($task);

        $this->service->toggleCompletion($task);
    }

    public function test_完了のタスクを切り替えると未完了に戻る(): void
    {
        $task = $this->makeTask(true);

        $this->repository->shouldReceive('update')
            ->once()
            ->with($task, ['is_completed' => false])
            ->andReturn($task);

        $this->service->toggleCompletion($task);
    }

    public function test_タスク削除はRepositoryのdeleteを1回呼ぶ(): void
    {
        $task = $this->makeTask();

        $this->repository->shouldReceive('delete')
            ->once()
            ->with($task);

        $this->service->deleteTask($task);
    }
}
