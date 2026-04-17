<?php

namespace App\Actions\Projects;

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Support\ProjectStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Create a new project under a client, assign ownership to the actor and
 * record the creation in the activity log — all inside a single transaction.
 */
class CreateProjectAction
{
    public function __construct(private readonly ActivityLogger $activity) {}

    /**
     * Create the project and return the persisted model.
     *
     * @param  array{name:string, description?:?string, status?:?string, budget_pence?:?int, starts_on?:?string, due_on?:?string}  $data
     *
     * @throws \Illuminate\Database\QueryException on persistence failure
     */
    public function execute(User $actor, Client $client, array $data): Project
    {
        return DB::transaction(function () use ($actor, $client, $data) {
            $project = Project::create([
                'client_id' => $client->id,
                'owner_id' => $actor->id,
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($client, $data['name']),
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? ProjectStatus::Active->value,
                'budget_pence' => $data['budget_pence'] ?? null,
                'starts_on' => $data['starts_on'] ?? null,
                'due_on' => $data['due_on'] ?? null,
            ]);

            $this->activity->record($actor, 'project.created', $project, [
                'client_id' => $client->id,
                'name' => $project->name,
            ]);

            return $project;
        });
    }

    private function uniqueSlug(Client $client, string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (Project::where('client_id', $client->id)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
