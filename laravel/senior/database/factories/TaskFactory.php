<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\TaskPriority;
use App\Support\TaskStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'assignee_id' => null,
            'created_by_id' => User::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => TaskStatus::Todo,
            'priority' => fake()->randomElement(TaskPriority::values()),
            'due_on' => fake()->optional()->dateTimeBetween('now', '+60 days'),
            'completed_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Done,
            'completed_at' => now(),
        ]);
    }
}
