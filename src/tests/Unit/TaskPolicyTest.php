<?php

namespace Tests\Unit;

use App\Models\Task;
use App\Models\User;
use App\Policies\TaskPolicy;
use Tests\TestCase;

class TaskPolicyTest extends TestCase
{
    private TaskPolicy $policy;
    private User $owner;
    private User $other;
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new TaskPolicy();

        $this->owner = User::factory()->make();
        $this->owner->id = 1;

        $this->other = User::factory()->make();
        $this->other->id = 2;

        $this->task = Task::factory()->make(['user_id' => 1]);
    }

    public function test_自分のタスクは見られる(): void
    {
        $this->assertTrue($this->policy->view($this->owner, $this->task));
    }

    public function test_他人のタスクは見られない(): void
    {
        $this->assertFalse($this->policy->view($this->other, $this->task));
    }

    public function test_自分のタスクは編集できる(): void
    {
        $this->assertTrue($this->policy->update($this->owner, $this->task));
    }

    public function test_他人のタスクは編集できない(): void
    {
        $this->assertFalse($this->policy->update($this->other, $this->task));
    }

    public function test_自分のタスクは削除できる(): void
    {
        $this->assertTrue($this->policy->delete($this->owner, $this->task));
    }

    public function test_他人のタスクは削除できない(): void
    {
        $this->assertFalse($this->policy->delete($this->other, $this->task));
    }
}
