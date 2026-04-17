<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->randomElement(Task::STATUSES);

        return [
            'project_id' => Project::factory(),
            'title' => ucfirst($this->faker->words(4, true)),
            'description' => $this->faker->optional()->paragraph(),
            'status' => $status,
            'due_on' => $this->faker->optional()->dateTimeBetween('-1 week', '+1 month'),
            'completed_at' => $status === 'done' ? now() : null,
        ];
    }

    public function done(): static
    {
        return $this->state(fn () => [
            'status' => 'done',
            'completed_at' => now(),
        ]);
    }
}
