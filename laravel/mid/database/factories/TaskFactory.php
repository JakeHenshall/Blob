<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
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
            'assigned_to' => null,
            'title' => ucfirst(fake()->words(4, true)),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(TaskStatus::cases())->value,
            'priority' => fake()->randomElement(TaskPriority::cases())->value,
            'due_at' => fake()->optional()->dateTimeBetween('-1 week', '+1 month'),
        ];
    }

    public function todo(): static
    {
        return $this->state(fn () => ['status' => TaskStatus::Todo->value, 'completed_at' => null]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Completed->value,
            'completed_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'status' => TaskStatus::Todo->value,
            'due_at' => now()->subDays(3),
            'completed_at' => null,
        ]);
    }
}
