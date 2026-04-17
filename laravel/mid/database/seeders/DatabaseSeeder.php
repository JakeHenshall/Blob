<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Client;
use App\Models\Note;
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
        $admin = User::factory()->admin()->create([
            'name' => 'Ada Admin',
            'email' => 'admin@clienthub.test',
        ]);

        $manager = User::factory()->manager()->create([
            'name' => 'Mia Manager',
            'email' => 'manager@clienthub.test',
        ]);

        $staff = User::factory()->count(2)->manager()->create();

        $users = User::factory()->count(4)->user()->create();

        $allAssignable = collect([$admin, $manager])->merge($staff)->merge($users);

        $owners = collect([$admin, $manager])->merge($staff);

        Client::factory()
            ->count(8)
            ->recycle($owners)
            ->create()
            ->each(function (Client $client) use ($allAssignable, $owners) {
                Project::factory()
                    ->count(random_int(1, 3))
                    ->recycle($owners)
                    ->state(['client_id' => $client->id])
                    ->create()
                    ->each(function (Project $project) use ($allAssignable) {
                        Task::factory()
                            ->count(random_int(3, 8))
                            ->state([
                                'project_id' => $project->id,
                                'assigned_to' => $allAssignable->random()->id,
                            ])
                            ->create();

                        Task::factory()
                            ->overdue()
                            ->count(1)
                            ->state([
                                'project_id' => $project->id,
                                'assigned_to' => $allAssignable->random()->id,
                            ])
                            ->create();

                        Note::factory()
                            ->count(random_int(0, 3))
                            ->state([
                                'project_id' => $project->id,
                                'user_id' => $allAssignable->random()->id,
                            ])
                            ->create();
                    });
            });
    }
}
