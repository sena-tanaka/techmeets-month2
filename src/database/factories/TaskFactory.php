<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'      => User::factory(),
            'title'        => fake()->sentence(3),
            'description'  => fake()->optional()->paragraph(),
            'due_date'     => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'is_completed' => false,
        ];
    }

    // 完了済みのタスクを作る：Task::factory()->completed()->create()
    public function completed(): static
    {
        return $this->state(fn () => ['is_completed' => true]);
    }
}
