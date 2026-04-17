<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Note;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Support\ProjectStatus;
use App\Support\Role;
use App\Support\TaskPriority;
use App\Support\TaskStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@clienthub.test'],
            [
                'name' => 'Alex Admin',
                'password' => Hash::make('password'),
                'role' => Role::Admin,
                'email_verified_at' => now(),
            ]
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@clienthub.test'],
            [
                'name' => 'Morgan Manager',
                'password' => Hash::make('password'),
                'role' => Role::Manager,
                'email_verified_at' => now(),
            ]
        );

        $users = User::factory(6)->create();

        $team = collect([$admin, $manager])->merge($users);

        Client::factory(8)->create(['owner_id' => $manager->id])->each(function (Client $client) use ($team) {
            Project::factory(rand(1, 3))
                ->state([
                    'client_id' => $client->id,
                    'owner_id' => $team->random()->id,
                ])
                ->create()
                ->each(function (Project $project) use ($team) {
                    Task::factory(rand(3, 8))
                        ->state(fn () => [
                            'project_id' => $project->id,
                            'created_by_id' => $team->random()->id,
                            'assignee_id' => $team->random()->id,
                            'status' => fake()->randomElement(TaskStatus::values()),
                            'priority' => fake()->randomElement(TaskPriority::values()),
                        ])
                        ->create();

                    Note::factory(rand(0, 3))
                        ->state(fn () => [
                            'project_id' => $project->id,
                            'author_id' => $team->random()->id,
                        ])
                        ->create();
                });
        });

        Project::where('status', ProjectStatus::Completed->value)
            ->each(function (Project $p) {
                $p->tasks()->update([
                    'status' => TaskStatus::Done->value,
                    'completed_at' => now()->subDays(rand(1, 30)),
                ]);
            });
    }
}
