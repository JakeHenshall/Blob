<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Support\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $name = rtrim(fake()->sentence(3), '.');
        $starts = fake()->dateTimeBetween('-60 days', '+10 days');

        return [
            'client_id' => Client::factory(),
            'owner_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(ProjectStatus::values()),
            'budget_pence' => fake()->numberBetween(50_000, 5_000_000),
            'starts_on' => $starts,
            'due_on' => fake()->dateTimeBetween($starts, '+120 days'),
        ];
    }
}
