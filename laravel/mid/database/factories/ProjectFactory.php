<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $starts = fake()->dateTimeBetween('-2 months', '+1 week');

        return [
            'client_id' => Client::factory(),
            'user_id' => User::factory(),
            'name' => ucwords(fake()->words(3, true)),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(ProjectStatus::cases())->value,
            'starts_at' => $starts,
            'due_at' => fake()->dateTimeBetween($starts, '+3 months'),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => ProjectStatus::Active->value]);
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => ProjectStatus::Completed->value]);
    }
}
