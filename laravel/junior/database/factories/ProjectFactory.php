<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $starts = $this->faker->dateTimeBetween('-2 months', '+1 week');

        return [
            'client_id' => Client::factory(),
            'name' => ucfirst($this->faker->words(3, true)),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(Project::STATUSES),
            'starts_on' => $starts,
            'ends_on' => $this->faker->optional()->dateTimeBetween($starts, '+3 months'),
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => ['status' => 'active']);
    }
}
