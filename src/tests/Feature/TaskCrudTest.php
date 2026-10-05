<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title'       => 'テストタスク',
            'description' => '説明文',
            'due_date'    => '2026-12-31',
        ], $overrides);
    }

    // ===== 一覧 =====
    public function test_自分のタスク一覧が見られる(): void
    {
        $task = Task::factory()->create(['title' => '自分のタスク']);

        $this->actingAs($task->user)
            ->get(route('tasks.index'))
            ->assertOk()
            ->assertSee('自分のタスク');
    }

    public function test_一覧に他人のタスクは表示されない(): void
    {
        $me = User::factory()->create();
        Task::factory()->for($me)->create(['title' => '自分のタスク']);
        Task::factory()->create(['title' => '他人のタスク']);

        $this->actingAs($me)
            ->get(route('tasks.index'))
            ->assertSee('自分のタスク')
            ->assertDontSee('他人のタスク');
    }

    public function test_一覧は未完了のタスクが先に並ぶ(): void
    {
        $me = User::factory()->create();
        Task::factory()->for($me)->completed()->create(['title' => '完了タスク']);
        Task::factory()->for($me)->create(['title' => '未完了タスク']);

        $this->actingAs($me)
            ->get(route('tasks.index'))
            ->assertSeeInOrder(['未完了タスク', '完了タスク']);
    }

    public function test_未ログインでは一覧を見られずログイン画面へ(): void
    {
        $this->get(route('tasks.index'))->assertRedirect(route('login'));
    }

    // ===== 詳細 =====
    public function test_自分のタスク詳細が見られる(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($task->user)
            ->get(route('tasks.show', $task))
            ->assertOk()
            ->assertSee($task->title);
    }

    public function test_他人のタスク詳細は403(): void
    {
        $task = Task::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('tasks.show', $task))
            ->assertForbidden();
    }

    // ===== 作成 =====
    public function test_作成画面を開ける(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('tasks.create'))
            ->assertOk();
    }

    public function test_タスクを作成できる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('tasks.store'), $this->validData())
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('success', 'タスクを作成しました');

        $this->assertDatabaseHas('tasks', [
            'title'        => 'テストタスク',
            'user_id'      => $user->id,
            'is_completed' => false,
        ]);
    }

    public function test_タイトルだけでも作成できる(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('tasks.store'), ['title' => 'タイトルのみ'])
            ->assertRedirect(route('tasks.index'));

        $this->assertDatabaseHas('tasks', ['title' => 'タイトルのみ', 'due_date' => null]);
    }

    public function test_フォームからuser_idを送っても他人のタスクにはならない(): void
    {
        $me    = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($me)
            ->post(route('tasks.store'), $this->validData(['user_id' => $other->id]));

        $this->assertDatabaseHas('tasks', ['user_id' => $me->id]);
        $this->assertDatabaseMissing('tasks', ['user_id' => $other->id]);
    }

    // ===== 更新 =====
    public function test_自分のタスクの編集画面を開ける(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($task->user)
            ->get(route('tasks.edit', $task))
            ->assertOk();
    }

    public function test_他人のタスクの編集画面は403(): void
    {
        $task = Task::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get(route('tasks.edit', $task))
            ->assertForbidden();
    }

    public function test_自分のタスクを更新できる(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($task->user)
            ->put(route('tasks.update', $task), $this->validData(['title' => '更新後']))
            ->assertRedirect(route('tasks.show', $task))
            ->assertSessionHas('success', 'タスクを更新しました');

        $task->refresh();
        $this->assertSame('更新後', $task->title);
        $this->assertSame('2026-12-31', $task->due_date->format('Y-m-d'));
    }

    public function test_他人のタスクは更新できない(): void
    {
        $task = Task::factory()->create(['title' => '元のタイトル']);

        $this->actingAs(User::factory()->create())
            ->put(route('tasks.update', $task), $this->validData(['title' => '乗っ取り']))
            ->assertForbidden();

        $this->assertSame('元のタイトル', $task->fresh()->title);
    }

    // ===== 完了切り替え（toggle） =====
    public function test_未完了のタスクを完了にできる(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($task->user)
            ->from(route('tasks.index'))
            ->patch(route('tasks.toggle', $task))
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('success', '状態を切り替えました');

        $this->assertTrue($task->fresh()->is_completed);
    }

    public function test_完了のタスクを未完了に戻せる(): void
    {
        $task = Task::factory()->completed()->create();

        $this->actingAs($task->user)
            ->patch(route('tasks.toggle', $task));

        $this->assertFalse($task->fresh()->is_completed);
    }

    public function test_他人のタスクは切り替えられない(): void
    {
        $task = Task::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('tasks.toggle', $task))
            ->assertForbidden();

        $this->assertFalse($task->fresh()->is_completed);
    }

    // ===== 削除 =====
    public function test_自分のタスクを削除できる(): void
    {
        $task = Task::factory()->create();

        $this->actingAs($task->user)
            ->delete(route('tasks.destroy', $task))
            ->assertRedirect(route('tasks.index'))
            ->assertSessionHas('success', 'タスクを削除しました');

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_他人のタスクは削除できない(): void
    {
        $task = Task::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('tasks.destroy', $task))
            ->assertForbidden();

        $this->assertDatabaseHas('tasks', ['id' => $task->id]);
    }

    // ===== バリデーション =====
    public function test_タイトル未入力だと作成できない(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('tasks.store'), $this->validData(['title' => '']))
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_タイトル256文字だと作成できない(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('tasks.store'), $this->validData(['title' => str_repeat('あ', 256)]))
            ->assertSessionHasErrors('title');
    }

    public function test_説明2001文字だと作成できない(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('tasks.store'), $this->validData(['description' => str_repeat('あ', 2001)]))
            ->assertSessionHasErrors('description');
    }

    public function test_日付でない期限だと作成できない(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('tasks.store'), $this->validData(['due_date' => 'あした']))
            ->assertSessionHasErrors('due_date');
    }

    public function test_真偽値でないis_completedは弾かれる(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('tasks.store'), $this->validData(['is_completed' => 'はい']))
            ->assertSessionHasErrors('is_completed');
    }

    // ===== エッジケース =====
    public function test_存在しないタスクは404(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('tasks.show', 99999))
            ->assertNotFound();
    }
}