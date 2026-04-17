<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $demo = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@clienthub.test',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        Client::factory()
            ->count(6)
            ->for($demo)
            ->create()
            ->each(function (Client $client) {
                Project::factory()
                    ->count(fake()->numberBetween(1, 3))
                    ->for($client)
                    ->create()
                    ->each(function (Project $project) {
                        Task::factory()
                            ->count(fake()->numberBetween(3, 8))
                            ->for($project)
                            ->create();
                    });
            });
    }
}
