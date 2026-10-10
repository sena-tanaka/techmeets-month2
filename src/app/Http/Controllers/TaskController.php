<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function __construct(
        private TaskService $taskService
    ) {
    }

    // 一覧（自分のタスクのみ）
    public function index(Request $request)
    {
        $tasks = $this->taskService->getTasks($request->user());
        return view('tasks.index', compact('tasks'));
    }

    // 作成画面
    public function create()
    {
        return view('tasks.create');
    }

    // 保存
    public function store(TaskRequest $request)
    {
        $this->taskService->createTask($request->user(), $request->validated());

        return redirect()->route('tasks.index')->with('success', 'タスクを作成しました');
    }

    // 詳細（自分のタスクのみ）
    public function show(Task $task)
    {
        Gate::authorize('view', $task);

        return view('tasks.show', compact('task'));
    }

    // 編集画面（自分のタスクのみ）
    public function edit(Task $task)
    {
        Gate::authorize('update', $task);

        return view('tasks.edit', compact('task'));
    }

    // 更新（自分のタスクのみ）
    public function update(TaskRequest $request, Task $task)
    {
        Gate::authorize('update', $task);

        $this->taskService->updateTask($task, $request->validated());

        return redirect()->route('tasks.show', $task)->with('success', 'タスクを更新しました');
    }

    // 完了/未完了の切り替え（自分のタスクのみ）
    public function toggle(Task $task)
    {
        Gate::authorize('update', $task);

        $this->taskService->toggleCompletion($task);

        return back()->with('success', '状態を切り替えました');
    }

    // 削除（自分のタスクのみ）
    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);

        $this->taskService->deleteTask($task);

        return redirect()->route('tasks.index')->with('success', 'タスクを削除しました');
    }
}
